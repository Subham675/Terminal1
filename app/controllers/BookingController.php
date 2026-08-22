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
        $id = Booking::create([
            'user_id'      => authUser()['id'] ?? null,
            'name'         => $name,
            'phone'        => $phone,
            'email'        => sanitize($_POST['email']??''),
            'occasion'     => sanitize($_POST['occasion']??''),
            'guests'       => (int)($_POST['guests']??1),
            'booking_date' => $_POST['booking_date']??null,
            'booking_time' => $_POST['booking_time']??null,
            'message'      => sanitize($_POST['message']??''),
        ]);
        echo json_encode(['success'=>true,'message'=>'Reservation received! We will confirm shortly.','id'=>$id]);
    }
}
