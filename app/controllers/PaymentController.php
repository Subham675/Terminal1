<?php
class PaymentController {
    /** POST /payments/create-order — called after a booking is created, to start the deposit payment. */
    public static function createOrder(): void {
        verifyCsrf();
        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $token     = $_POST['tracking_token'] ?? $_POST['token'] ?? null;
        
        // Strict server-side ownership verification: prevents ordering/paying under another user's identity
        $booking   = verifyBookingOwnership($bookingId, $token);

        if ($booking['payment_status'] === 'paid') {
            echo json_encode(['success' => false, 'message' => 'This booking is already paid.']);
            return;
        }

        $amount = (float) env('DEPOSIT_AMOUNT', 100);
        $order  = Razorpay::createOrder($amount, 'booking_' . $bookingId . '_' . time());

        if (empty($order['id'])) {
            error_log('[Payment] Razorpay order creation failed: ' . json_encode($order));
            echo json_encode(['success' => false, 'message' => 'Could not initiate payment. Please try again.']);
            return;
        }

        Payment::create([
            'booking_id'        => $bookingId,
            'razorpay_order_id' => $order['id'],
            'amount'            => $amount,
        ]);

        echo json_encode([
            'success'   => true,
            'order_id'  => $order['id'],
            'amount'    => (int) round($amount * 100), // paise, for Razorpay Checkout JS
            'currency'  => 'INR',
            'key'       => env('RAZORPAY_KEY_ID'),
            'name'      => env('APP_NAME', 'Terminal 1'),
            'booking_id'=> $bookingId,
        ]);
    }

    /** POST /payments/verify — called from Razorpay Checkout's success handler (client-side). */
    public static function verify(): void {
        verifyCsrf();
        $orderId   = $_POST['razorpay_order_id']   ?? '';
        $paymentId = $_POST['razorpay_payment_id'] ?? '';
        $signature = $_POST['razorpay_signature']  ?? '';
        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $token     = $_POST['tracking_token'] ?? $_POST['token'] ?? null;

        if (!$orderId || !$paymentId || !$signature || !$bookingId) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Missing payment details.']);
            return;
        }

        // Strict server-side ownership verification: prevent verifying/marking paid another user's booking
        $booking = verifyBookingOwnership($bookingId, $token);

        if (!Razorpay::verifySignature($orderId, $paymentId, $signature)) {
            error_log("[Payment] Signature mismatch for order $orderId");
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Payment verification failed.']);
            return;
        }

        Payment::markPaid($orderId, $paymentId, $signature);
        Booking::updatePaymentStatus($bookingId, 'paid');
        $booking = Booking::findById($bookingId);
        if ($booking && !empty($booking['email'])) {
            Mailer::sendBookingConfirmation($booking);
        }

        echo json_encode(['success' => true, 'message' => 'Payment verified! Your table is confirmed.']);
    }

    /** POST /payments/webhook — Razorpay server-to-server event notifications (async, source of truth). */
    public static function webhook(): void {
        $rawBody   = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

        if (!$signature || !Razorpay::verifyWebhookSignature($rawBody, $signature)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid webhook signature']);
            return;
        }

        $payload = json_decode($rawBody, true) ?? [];
        $event   = $payload['event'] ?? '';

        switch ($event) {
            case 'payment.captured':
                $entity = $payload['payload']['payment']['entity'] ?? [];
                $orderId = $entity['order_id'] ?? null;
                if ($orderId) {
                    $payment = Payment::findByOrderId($orderId);
                    if ($payment && $payment['status'] !== 'paid') {
                        Payment::markPaid($orderId, $entity['id'], 'webhook_verified');
                        Booking::updatePaymentStatus($payment['booking_id'], 'paid');
                    }
                }
                break;
            case 'payment.failed':
                $entity = $payload['payload']['payment']['entity'] ?? [];
                if (!empty($entity['order_id'])) Payment::markFailed($entity['order_id']);
                break;
            case 'refund.processed':
                $entity = $payload['payload']['refund']['entity'] ?? [];
                if (!empty($entity['payment_id'])) Payment::markRefunded($entity['payment_id'], $entity['id']);
                break;
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok']);
    }

    /** POST /admin/bookings/refund — admin-triggered refund when cancelling a paid booking. */
    public static function adminRefund(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $booking = $bookingId ? Booking::findById($bookingId) : null;
        if (!$booking || $booking['payment_status'] !== 'paid') {
            flash('bookings', 'No paid payment found for this booking.', 'error');
            redirect('/admin/bookings');
            return;
        }
        $payment = Payment::findByBookingId($bookingId);
        if (!$payment || empty($payment['razorpay_payment_id'])) {
            flash('bookings', 'Payment record missing.', 'error');
            redirect('/admin/bookings');
            return;
        }
        $refund = Razorpay::createRefund($payment['razorpay_payment_id']);
        if (!empty($refund['id'])) {
            Payment::markRefunded($payment['razorpay_payment_id'], $refund['id']);
            Booking::updatePaymentStatus($bookingId, 'refunded');
            Booking::updateStatus($bookingId, 'cancelled');
            auditLog('ADMIN_REFUND', "Refund #{$refund['id']} initiated for Booking #{$bookingId}, Payment #{$payment['razorpay_payment_id']}");
            flash('bookings', 'Refund initiated successfully.', 'success');
        } else {
            auditLog('ADMIN_REFUND_FAILED', "Refund attempt failed for Booking #{$bookingId}, Payment #{$payment['razorpay_payment_id']}");
            flash('bookings', 'Refund failed. Check logs.', 'error');
        }
        redirect('/admin/bookings');
    }
}
