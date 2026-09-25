<?php
class Payment {
    public static function create(array $d): int {
        Database::query(
            'INSERT INTO payments (booking_id,razorpay_order_id,amount,currency,status) VALUES (?,?,?,?,?)',
            [$d['booking_id'], $d['razorpay_order_id'], $d['amount'], $d['currency'] ?? 'INR', $d['status'] ?? 'created']
        );
        return (int) Database::lastInsertId();
    }

    public static function findByOrderId(string $orderId): ?array {
        return Database::row('SELECT * FROM payments WHERE razorpay_order_id=?', [$orderId]);
    }

    public static function findByBookingId(int $bookingId): ?array {
        return Database::row('SELECT * FROM payments WHERE booking_id=? ORDER BY id DESC LIMIT 1', [$bookingId]);
    }

    public static function markPaid(string $orderId, string $paymentId, string $signature): void {
        Database::query(
            'UPDATE payments SET razorpay_payment_id=?, razorpay_signature=?, status=?, updated_at=NOW() WHERE razorpay_order_id=?',
            [$paymentId, $signature, 'paid', $orderId]
        );
    }

    public static function markFailed(string $orderId): void {
        Database::query('UPDATE payments SET status=?, updated_at=NOW() WHERE razorpay_order_id=?', ['failed', $orderId]);
    }

    public static function markRefunded(string $paymentId, string $refundId): void {
        Database::query(
            'UPDATE payments SET status=?, razorpay_refund_id=?, updated_at=NOW() WHERE razorpay_payment_id=?',
            ['refunded', $refundId, $paymentId]
        );
    }

    /** Revenue grouped by day for the last N days — used by admin analytics. */
    public static function revenueByDay(int $days = 14): array {
        $days = max(1, (int)$days);
        $cutoff = date('Y-m-d 00:00:00', strtotime("-{$days} days"));
        return Database::rows(
            "SELECT DATE(created_at) as day, SUM(amount) as total, COUNT(*) as count
             FROM payments
             WHERE status='paid' AND created_at >= ?
             GROUP BY DATE(created_at) ORDER BY day ASC",
            [$cutoff]
        );
    }

    public static function totalRevenue(): float {
        $row = Database::row("SELECT COALESCE(SUM(amount),0) as total FROM payments WHERE status='paid'");
        return (float) ($row['total'] ?? 0);
    }

    public static function countByStatus(string $status): int {
        return (int) Database::row('SELECT COUNT(*) as c FROM payments WHERE status=?', [$status])['c'];
    }
}
