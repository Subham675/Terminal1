<?php
/**
 * Minimal Razorpay REST client — no composer dependency.
 * Docs: https://razorpay.com/docs/api/orders/  https://razorpay.com/docs/api/payments/
 */
class Razorpay {
    private static function auth(): string {
        return base64_encode(env('RAZORPAY_KEY_ID').':'.env('RAZORPAY_KEY_SECRET'));
    }

    private static function request(string $method, string $path, array $body = []): array {
        $ch = curl_init("https://api.razorpay.com/v1{$path}");
        $headers = [
            'Authorization: Basic '.self::auth(),
            'Content-Type: application/json',
        ];
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 15,
        ]);
        if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $res  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($err) { error_log("[Razorpay] cURL error: $err"); return ['error' => $err]; }
        $decoded = json_decode($res, true) ?? [];
        if ($code >= 400) error_log('[Razorpay] API error ('.$code.'): '.$res);
        return $decoded;
    }

    /** Amount in RUPEES (converted to paise internally, as Razorpay requires). */
    public static function createOrder(float $amountRupees, string $receipt): array {
        return self::request('POST', '/orders', [
            'amount'   => (int) round($amountRupees * 100),
            'currency' => 'INR',
            'receipt'  => $receipt,
        ]);
    }

    public static function verifySignature(string $orderId, string $paymentId, string $signature): bool {
        $expected = hash_hmac('sha256', "$orderId|$paymentId", env('RAZORPAY_KEY_SECRET'));
        return hash_equals($expected, $signature);
    }

    /** Verifies an incoming webhook's signature header against the raw request body. */
    public static function verifyWebhookSignature(string $rawBody, string $signatureHeader): bool {
        $expected = hash_hmac('sha256', $rawBody, env('RAZORPAY_WEBHOOK_SECRET'));
        return hash_equals($expected, $signatureHeader);
    }

    public static function fetchPayment(string $paymentId): array {
        return self::request('GET', "/payments/{$paymentId}");
    }

    public static function createRefund(string $paymentId, ?float $amountRupees = null): array {
        $body = $amountRupees !== null ? ['amount' => (int) round($amountRupees * 100)] : [];
        return self::request('POST', "/payments/{$paymentId}/refund", $body);
    }
}
