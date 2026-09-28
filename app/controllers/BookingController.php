<?php
class BookingController {
    public static function store(): void {
        verifyCsrf();

        // ── Strictly require customer login before reserving a table ──
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode([
                'success'       => false,
                'require_login' => true,
                'redirect'      => url('/auth/login?redirect=' . urlencode('/#contact')),
                'message'       => 'Please sign in or create an account first to reserve a table.'
            ]);
            return;
        }

        $user  = authUser();
        $name  = sanitize($_POST['name'] ?? '') ?: ($user['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        if(!$name || !$phone){
            http_response_code(422);
            echo json_encode(['success'=>false,'message'=>'Name and phone number are required to reserve your table.']);
            return;
        }

        $date = $_POST['booking_date'] ?? ($_POST['date'] ?? null);
        $time = $_POST['booking_time'] ?? ($_POST['time'] ?? null);
        $slotCapacity = (int) env('SLOT_CAPACITY', 8);
        if ($date && $time && Booking::countForSlot($date, $time) >= $slotCapacity) {
            http_response_code(409);
            echo json_encode(['success'=>false,'message'=>'That time slot is fully booked. Please choose another time.']);
            return;
        }

        // Use the authenticated user's verified account email
        $email = $user['email'] ?? sanitize($_POST['email'] ?? '');

        $depositAmount = (float) env('DEPOSIT_AMOUNT', 0);

        $id = Booking::create([
            'user_id'      => (int)$user['id'],
            'name'         => $name,
            'phone'        => $phone,
            'email'        => $email,
            'occasion'     => sanitize($_POST['occasion']??''),
            'guests'       => (int)($_POST['guests']??2),
            'booking_date' => $date,
            'booking_time' => $time,
            'message'      => sanitize($_POST['message'] ?? ($_POST['special_requests'] ?? '')),
        ]);
        Booking::setDepositAmount($id, $depositAmount);
        $booking = Booking::findById($id);

        // Record ownership in session for this visitor (works for both guests and authenticated users)
        if(!isset($_SESSION['user_bookings'])){
            $_SESSION['user_bookings'] = [];
        }
        $_SESSION['user_bookings'][$id] = $booking['tracking_token'] ?? '';

        echo json_encode([
            'success'          => true,
            'message'          => 'Reservation received! Your table request has been registered.',
            'id'               => $id,
            'tracking_token'   => $booking['tracking_token'] ?? null,
            'requires_payment' => $depositAmount > 0,
            'deposit_amount'   => $depositAmount,
        ]);
    }

    /** GET /my-bookings — view own bookings/orders. Zero URL ID needed; reads strictly from server session! */
    public static function myBookings(): void {
        requireLogin();
        $user = authUser();
        // Server retrieves bookings strictly based on authenticated session user id — NO URL parameter accepted!
        $bookings = Booking::findByUserId($user['id']);
        require APP_ROOT . '/app/views/customer/my_bookings.php';
    }

    /** GET /bookings/view?id=..&token=.. — view single booking. Strictly validates server ownership! */
    public static function viewBooking(): void {
        $id    = (int)($_GET['id'] ?? 0);
        $token = $_GET['token'] ?? null;

        if (!$id && !$token) {
            redirect('/');
            return;
        }

        // If only non-enumerable token is given, safely lookup booking by token
        if (!$id && $token) {
            $found = Booking::findByToken($token);
            if (!$found) {
                http_response_code(404);
                die(renderSecurityError(404, 'Booking Not Found', 'No reservation found matching this tracking token.'));
            }
            $id = (int)$found['id'];
        }

        // Strictly verify server-side ownership. If id was changed/tampered, this dies with 403 Forbidden!
        $booking = verifyBookingOwnership($id, $token);
        require APP_ROOT . '/app/views/customer/view_booking.php';
    }

    /** GET /bookings/slots — returns slot availability for date */
    public static function slots(): void {
        header('Content-Type: application/json');
        $date = sanitize($_GET['date'] ?? date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid date format']);
            return;
        }
        $capacity = (int) env('SLOT_CAPACITY', 8);
        $times = [
            '12:30' => '12:30 PM — Lunch Service',
            '13:30' => '01:30 PM — Afternoon Service',
            '19:00' => '07:00 PM — Evening Service',
            '20:30' => '08:30 PM — Prime Sitting',
            '21:45' => '09:45 PM — Late Supper'
        ];
        $slots = [];
        foreach ($times as $t => $label) {
            $count = Booking::countForSlot($date, $t);
            $isFull = $count >= $capacity;
            $slots[] = [
                'time'      => $t,
                'label'     => $label,
                'booked'    => $count,
                'capacity'  => $capacity,
                'available' => max(0, $capacity - $count),
                'full'      => $isFull
            ];
        }
        echo json_encode(['date' => $date, 'slots' => $slots]);
    }

    /** POST /bookings/cancel — customer cancels their own booking */
    public static function cancelBooking(): void {
        header('Content-Type: application/json');
        csrfCheck();
        $id = (int)($_POST['id'] ?? 0);
        $token = sanitize($_POST['token'] ?? '');
        $booking = verifyBookingOwnership($id, $token ?: null);
        
        if ($booking['status'] === 'cancelled') {
            echo json_encode(['success' => true, 'message' => 'Reservation is already cancelled.']);
            return;
        }

        if ($booking['status'] === 'completed') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Completed dining experiences cannot be cancelled.']);
            return;
        }

        Booking::updateStatus($id, 'cancelled');
        echo json_encode([
            'success' => true,
            'message' => 'Your reservation has been cancelled per our dining policy.'
        ]);
    }
}
