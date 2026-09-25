<?php
use PHPUnit\Framework\TestCase;

final class RazorpaySignatureTest extends TestCase {
    protected function setUp(): void {
        putenv('RAZORPAY_KEY_SECRET=test_secret_key_12345');
        $_ENV['RAZORPAY_KEY_SECRET'] = 'test_secret_key_12345';
    }

    public function testValidSignaturePasses(): void {
        $orderId   = 'order_ABC123';
        $paymentId = 'pay_XYZ789';
        $expected  = hash_hmac('sha256', "$orderId|$paymentId", 'test_secret_key_12345');

        $this->assertTrue(Razorpay::verifySignature($orderId, $paymentId, $expected));
    }

    public function testTamperedSignatureFails(): void {
        $orderId   = 'order_ABC123';
        $paymentId = 'pay_XYZ789';
        $tampered  = 'not_the_real_signature';

        $this->assertFalse(Razorpay::verifySignature($orderId, $paymentId, $tampered));
    }

    public function testDifferentOrderIdFailsSameSignature(): void {
        $signatureForOrderA = hash_hmac('sha256', 'order_A|pay_XYZ789', 'test_secret_key_12345');
        // Same signature, but presented with a different order_id — must fail.
        $this->assertFalse(Razorpay::verifySignature('order_B', 'pay_XYZ789', $signatureForOrderA));
    }

    public function testWebhookSignatureVerification(): void {
        putenv('RAZORPAY_WEBHOOK_SECRET=webhook_secret_999');
        $_ENV['RAZORPAY_WEBHOOK_SECRET'] = 'webhook_secret_999';

        $body = '{"event":"payment.captured","payload":{}}';
        $validSig = hash_hmac('sha256', $body, 'webhook_secret_999');

        $this->assertTrue(Razorpay::verifyWebhookSignature($body, $validSig));
        $this->assertFalse(Razorpay::verifyWebhookSignature($body, 'garbage'));
        $this->assertFalse(Razorpay::verifyWebhookSignature($body . 'tampered', $validSig));
    }
}
