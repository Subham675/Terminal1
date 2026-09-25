<?php
/**
 * Generates a PDF invoice for a paid booking.
 * Requires: composer require dompdf/dompdf
 * If dompdf isn't installed, generate() throws — caller (Mailer) catches it
 * and simply sends the confirmation email without an attachment.
 */
class Invoice {
    public static function generate(array $booking): string {
        $composerPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (!file_exists($composerPath)) {
            throw new \RuntimeException('dompdf not installed — run: composer require dompdf/dompdf');
        }
        require_once $composerPath;
        if (!class_exists('Dompdf\Dompdf')) {
            throw new \RuntimeException('dompdf not installed — run: composer require dompdf/dompdf');
        }

        $html = self::invoiceHtml($booking);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dir = dirname(__DIR__, 2) . '/storage/invoices';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $path = $dir . '/invoice_' . $booking['id'] . '_' . time() . '.pdf';
        file_put_contents($path, $dompdf->output());
        return $path;
    }

    private static function invoiceHtml(array $b): string {
        $date = date('d M Y');
        $bookingDate = $b['booking_date'] ? date('d M Y', strtotime($b['booking_date'])) : 'TBD';
        return "<html><body style='font-family:sans-serif;color:#222;padding:20px;'>
            <h1 style='color:#C8860A;'>TERMINAL 1 — The Restaurant</h1>
            <p style='color:#777;'>Invoice generated on {$date}</p>
            <hr>
            <h2>Invoice — Booking #{$b['id']}</h2>
            <table style='width:100%;margin-top:20px;border-collapse:collapse;'>
                <tr><td style='padding:8px;border-bottom:1px solid #eee;'>Guest Name</td><td style='padding:8px;border-bottom:1px solid #eee;text-align:right;'>{$b['name']}</td></tr>
                <tr><td style='padding:8px;border-bottom:1px solid #eee;'>Phone</td><td style='padding:8px;border-bottom:1px solid #eee;text-align:right;'>{$b['phone']}</td></tr>
                <tr><td style='padding:8px;border-bottom:1px solid #eee;'>Reservation Date</td><td style='padding:8px;border-bottom:1px solid #eee;text-align:right;'>{$bookingDate}</td></tr>
                <tr><td style='padding:8px;border-bottom:1px solid #eee;'>Guests</td><td style='padding:8px;border-bottom:1px solid #eee;text-align:right;'>{$b['guests']}</td></tr>
                <tr><td style='padding:8px;'><strong>Deposit Paid</strong></td><td style='padding:8px;text-align:right;'><strong>₹{$b['deposit_amount']}.00</strong></td></tr>
            </table>
            <p style='margin-top:40px;color:#999;font-size:12px;'>This deposit will be adjusted against your final bill at the venue. Thank you for choosing Terminal 1.</p>
        </body></html>";
    }
}
