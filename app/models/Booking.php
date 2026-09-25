<?php
class Booking {
    public static function create(array $d): int {
        $trackingToken = bin2hex(random_bytes(24));
        Database::query(
            'INSERT INTO bookings (user_id,name,phone,email,occasion,guests,booking_date,booking_time,message,tracking_token) VALUES (?,?,?,?,?,?,?,?,?,?)',
            [$d['user_id']??null,$d['name'],$d['phone'],$d['email']??null,$d['occasion']??null,(int)($d['guests']??1),$d['booking_date']??null,$d['booking_time']??null,$d['message']??null,$trackingToken]
        );
        return (int)Database::lastInsertId();
    }
    public static function all(): array {
        return Database::rows('SELECT b.*,u.name as user_name FROM bookings b LEFT JOIN users u ON b.user_id=u.id ORDER BY b.created_at DESC');
    }
    public static function findById(int $id): ?array {
        return Database::row('SELECT * FROM bookings WHERE id=?',[$id]);
    }
    public static function findByIdAndToken(int $id, string $token): ?array {
        return Database::row('SELECT * FROM bookings WHERE id=? AND tracking_token=?',[$id,$token]);
    }
    public static function findByToken(string $token): ?array {
        return Database::row('SELECT * FROM bookings WHERE tracking_token=?',[$token]);
    }
    public static function findByUserId(int $userId): array {
        return Database::rows('SELECT * FROM bookings WHERE user_id=? ORDER BY created_at DESC',[$userId]);
    }
    public static function updateStatus(int $id, string $status): void {
        Database::query('UPDATE bookings SET status=?,updated_at=NOW() WHERE id=?',[$status,$id]);
    }
    public static function updatePaymentStatus(int $id, string $paymentStatus): void {
        Database::query('UPDATE bookings SET payment_status=?,updated_at=NOW() WHERE id=?',[$paymentStatus,$id]);
    }
    public static function setDepositAmount(int $id, float $amount): void {
        Database::query('UPDATE bookings SET deposit_amount=? WHERE id=?',[$amount,$id]);
    }
    /** Counts non-cancelled bookings already reserved for this date+time slot. */
    public static function countForSlot(string $date, string $time): int {
        return (int) Database::row(
            "SELECT COUNT(*) as c FROM bookings WHERE booking_date=? AND booking_time=? AND status != 'cancelled'",
            [$date, $time]
        )['c'];
    }
    /** Revenue-adjacent: bookings per day, useful for admin analytics. */
    public static function bookingsByDay(int $days = 14): array {
        $days = max(1, (int)$days);
        $cutoff = date('Y-m-d 00:00:00', strtotime("-{$days} days"));
        return Database::rows(
            "SELECT DATE(created_at) as day, COUNT(*) as count
             FROM bookings WHERE created_at >= ?
             GROUP BY DATE(created_at) ORDER BY day ASC",
            [$cutoff]
        );
    }
    public static function count(): int { return (int)Database::row('SELECT COUNT(*) as c FROM bookings')['c']; }
    public static function countByStatus(string $status): int {
        return (int)Database::row('SELECT COUNT(*) as c FROM bookings WHERE status=?',[$status])['c'];
    }
    public static function recent(int $limit=10): array {
        $limit = max(1, (int)$limit);
        $stmt = Database::connect()->prepare('SELECT * FROM bookings ORDER BY created_at DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public static function delete(int $id): void { Database::query('DELETE FROM bookings WHERE id=?',[$id]); }

    /** Highest booking ID currently in the table — used by the admin live-update stream
     *  to detect when a brand-new booking has come in. */
    public static function latestId(): int {
        $row = Database::row('SELECT COALESCE(MAX(id),0) as m FROM bookings');
        return (int) ($row['m'] ?? 0);
    }
}
