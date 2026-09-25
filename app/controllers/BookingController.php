<?php
class BookingController {
    public static function store(): void {
        verifyCsrf();
        $name  = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        if(!$name || !$phone){
            http_response_code(422);
            echo json_encode(['success'=>false,'message'=>'Name and phone are required.']);
            return;
        }

        $date = $_POST['booking_date'] ?? null;
        $time = $_POST['booking_time'] ?? null;
        $slotCapacity = (int) env('SLOT_CAPACITY', 8);
        if ($date && $time && Booking::countForSlot($date, $time) >= $slotCapacity) {
            http_response_code(409);
            echo json_encode(['success'=>false,'message'=>'That time slot is fully booked. Please choose another time.']);
            return;
        }

        $rawEmail = sanitize($_POST['email'] ?? '');
        $email = '';
        if ($rawEmail !== '') {
            [$isValidEmail, $emailError, $email] = EmailValidator::validate($rawEmail);
            if (!$isValidEmail) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => $emailError]);
                return;
            }
        }

        $depositAmount = (float) env('DEPOSIT_AMOUNT', 100);

        $id = Booking::create([
            'user_id'      => authUser()['id'] ?? null,
            'name'         => $name,
            'phone'        => $phone,
            'email'        => $email,
            'occasion'     => sanitize($_POST['occasion']??''),
            'guests'       => (int)($_POST['guests']??1),
            'booking_date' => $date,
            'booking_time' => $time,
            'message'      => sanitize($_POST['message']??''),
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
            'message'          => 'Reservation received! Please pay the deposit to confirm your table.',
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
}
