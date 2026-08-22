<?php
class Booking {
    public static function create(array $d): int {
        Database::query(
            'INSERT INTO bookings (user_id,name,phone,email,occasion,guests,booking_date,booking_time,message) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9)',
            [$d['user_id']??null,$d['name'],$d['phone'],$d['email']??null,$d['occasion']??null,(int)($d['guests']??1),$d['booking_date']??null,$d['booking_time']??null,$d['message']??null]
        );
        return (int)Database::lastInsertId();
    }
    public static function all(): array {
        return Database::rows('SELECT b.*,u.name as user_name FROM bookings b LEFT JOIN users u ON b.user_id=u.id ORDER BY b.created_at DESC');
    }
    public static function findById(int $id): ?array {
        return Database::row('SELECT * FROM bookings WHERE id=$1',[$id]);
    }
    public static function updateStatus(int $id, string $status): void {
        Database::query('UPDATE bookings SET status=$1,updated_at=NOW() WHERE id=$2',[$status,$id]);
    }
    public static function count(): int { return (int)Database::row('SELECT COUNT(*) as c FROM bookings')['c']; }
    public static function countByStatus(string $status): int {
        return (int)Database::row('SELECT COUNT(*) as c FROM bookings WHERE status=$1',[$status])['c'];
    }
    public static function recent(int $limit=10): array {
        return Database::rows("SELECT * FROM bookings ORDER BY created_at DESC LIMIT $limit");
    }
    public static function delete(int $id): void { Database::query('DELETE FROM bookings WHERE id=$1',[$id]); }
}
