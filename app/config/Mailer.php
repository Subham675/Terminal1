<?php
class Mailer {
    public static function sendOtp(string $toEmail, string $toName, string $otp, string $purpose = 'verification'): bool {
        $subject = match($purpose) {
            'google_verify' => 'Terminal 1 — Verify Your Google Login',
            'register'      => 'Terminal 1 — Confirm Your Registration',
            'admin_login'   => 'Terminal 1 — Admin 2FA Login Code',
            default         => 'Terminal 1 — Your OTP Code',
        };
        $html = self::otpTemplate($toName, $otp, $purpose);
        return self::send($toEmail, $toName, $subject, $html);
    }

    /** Security alert email dispatched whenever an admin successfully logs in. */
    public static function sendAdminLoginAlert(array $admin, string $ip, string $userAgent): bool {
        $subject = 'Terminal 1 Security Alert: Admin Login Detected';
        $time    = date('d M Y, h:i:s A T');
        $html    = "<html><body style='background:#111009;font-family:sans-serif;padding:40px;'>
          <div style='max-width:520px;margin:0 auto;background:#1E1C18;border:1px solid #d29922;border-radius:8px;overflow:hidden;'>
            <div style='background:#d29922;padding:24px;text-align:center;'>
              <h2 style='color:#fff;margin:0;letter-spacing:1px;'>SECURITY ALERT</h2>
              <p style='color:rgba(255,255,255,.8);margin:4px 0 0;font-size:12px;letter-spacing:2px;'>ADMIN ACCESS DETECTED</p>
            </div>
            <div style='padding:32px;'>
              <p style='color:rgba(255,255,255,.8);font-size:14px;line-height:1.6;'>
                Hi <strong>{$admin['name']}</strong>,<br><br>
                A successful administrator login just occurred for your account:
              </p>
              <table style='width:100%;color:rgba(255,255,255,.7);font-size:13px;margin:20px 0;border-collapse:collapse;'>
                <tr><td style='padding:6px 0;color:rgba(255,255,255,.4);'>Time:</td><td>{$time}</td></tr>
                <tr><td style='padding:6px 0;color:rgba(255,255,255,.4);'>IP Address:</td><td>{$ip}</td></tr>
                <tr><td style='padding:6px 0;color:rgba(255,255,255,.4);'>User Agent:</td><td>{$userAgent}</td></tr>
              </table>
              <p style='color:rgba(255,255,255,.5);font-size:12px;'>
                If you did not initiate this login, please immediately change your credentials and check server logs.
              </p>
            </div>
          </div>
        </body></html>";
        return self::send($admin['email'], $admin['name'], $subject, $html);
    }


    /** Booking confirmation email, with PDF invoice attached (once deposit is paid). */
    public static function sendBookingConfirmation(array $booking): bool {
        $subject = 'Terminal 1 — Booking Confirmed #' . $booking['id'];
        $html    = self::bookingTemplate($booking);
        $pdfPath = null;
        try {
            $pdfPath = Invoice::generate($booking);
        } catch (\Throwable $e) {
            error_log('[Mailer] Invoice generation skipped: ' . $e->getMessage());
        }
        return self::send($booking['email'], $booking['name'], $subject, $html, $pdfPath);
    }

    private static function send(string $to, string $toName, string $subject, string $htmlBody, ?string $attachmentPath = null): bool {
        $composerPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (file_exists($composerPath)) {
            require_once $composerPath;
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return self::sendViaPHPMailer($to, $toName, $subject, $htmlBody, $attachmentPath);
            }
        }
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . env('MAIL_FROM_NAME') . " <" . env('MAIL_FROM_ADDRESS') . ">\r\n";
        $result = mail($to, $subject, $htmlBody, $headers);
        if (!$result) error_log("[Mailer] mail() failed for: $to");
        // Note: PHP's built-in mail() cannot attach files — attachment requires PHPMailer (composer).
        return $result;
    }

    private static function sendViaPHPMailer(string $to, string $toName, string $subject, string $html, ?string $attachmentPath = null): bool {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) env('MAIL_PORT', 587);
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($to, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            if ($attachmentPath && file_exists($attachmentPath)) {
                $mail->addAttachment($attachmentPath, 'Terminal1_Invoice.pdf');
            }
            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log('[Mailer] PHPMailer error: ' . $e->getMessage());
            return false;
        }
    }

    private static function bookingTemplate(array $b): string {
        $date = $b['booking_date'] ? date('d M Y', strtotime($b['booking_date'])) : 'TBD';
        $time = $b['booking_time'] ? date('g:i A', strtotime($b['booking_time'])) : 'TBD';
        return "<html><body style='background:#111009;font-family:sans-serif;padding:40px;'>
          <div style='max-width:520px;margin:0 auto;background:#1E1C18;border:1px solid #C8860A55;border-radius:8px;overflow:hidden;'>
            <div style='background:#C8860A;padding:28px;text-align:center;'>
              <h1 style='color:#fff;margin:0;letter-spacing:2px;'>TERMINAL 1</h1>
              <p style='color:rgba(255,255,255,.7);margin:4px 0 0;font-size:12px;letter-spacing:3px;'>BOOKING CONFIRMED</p>
            </div>
            <div style='padding:40px;'>
              <p style='color:rgba(255,255,255,.7);font-size:15px;line-height:1.7;'>Hi <strong style='color:#fff;'>{$b['name']}</strong>,<br><br>Your table reservation is confirmed! Details below:</p>
              <table style='width:100%;color:rgba(255,255,255,.8);font-size:14px;margin-top:20px;'>
                <tr><td style='padding:6px 0;color:rgba(255,255,255,.4);'>Booking ID</td><td style='text-align:right;'>#{$b['id']}</td></tr>
                <tr><td style='padding:6px 0;color:rgba(255,255,255,.4);'>Date</td><td style='text-align:right;'>{$date}</td></tr>
                <tr><td style='padding:6px 0;color:rgba(255,255,255,.4);'>Time</td><td style='text-align:right;'>{$time}</td></tr>
                <tr><td style='padding:6px 0;color:rgba(255,255,255,.4);'>Guests</td><td style='text-align:right;'>{$b['guests']}</td></tr>
                <tr><td style='padding:6px 0;color:rgba(255,255,255,.4);'>Deposit Paid</td><td style='text-align:right;color:#4caf70;'>₹{$b['deposit_amount']}</td></tr>
              </table>
              <p style='color:rgba(255,255,255,.4);font-size:13px;margin-top:24px;'>Your invoice is attached to this email. See you soon!</p>
            </div>
            <div style='padding:20px;border-top:1px solid rgba(255,255,255,.06);text-align:center;'>
              <p style='color:rgba(255,255,255,.2);font-size:12px;margin:0;'>© Terminal 1 — The Restaurant, Cooch Behar</p>
            </div>
          </div>
        </body></html>";
    }

    private static function otpTemplate(string $name, string $otp, string $purpose): string {
        $purposeText = match($purpose) {
            'google_verify' => 'verify your Google account login',
            'register'      => 'complete your registration',
            'admin_login'   => 'securely access the Terminal 1 Admin Dashboard (Two-Factor Authentication)',
            default         => 'verify your identity',
        };
        $expiry = env('OTP_EXPIRY_MINUTES', 10);
        return "<html><body style='background:#111009;font-family:sans-serif;padding:40px;'>
          <div style='max-width:520px;margin:0 auto;background:#1E1C18;border:1px solid #C8860A55;border-radius:8px;overflow:hidden;'>
            <div style='background:#C8860A;padding:28px;text-align:center;'>
              <h1 style='color:#fff;margin:0;letter-spacing:2px;'>TERMINAL 1</h1>
              <p style='color:rgba(255,255,255,.7);margin:4px 0 0;font-size:12px;letter-spacing:3px;'>THE RESTAURANT</p>
            </div>
            <div style='padding:40px;'>
              <p style='color:rgba(255,255,255,.7);font-size:15px;line-height:1.7;'>Hi <strong style='color:#fff;'>{$name}</strong>,<br><br>Use the OTP below to {$purposeText}:</p>
              <div style='text-align:center;margin:32px 0;'>
                <div style='display:inline-block;background:#C8860A18;border:2px solid #C8860A;border-radius:8px;padding:20px 40px;'>
                  <span style='font-size:40px;font-weight:900;letter-spacing:12px;color:#E8A820;'>{$otp}</span>
                </div>
              </div>
              <p style='color:rgba(255,255,255,.4);font-size:13px;text-align:center;'>This OTP expires in <strong style='color:#C8860A;'>{$expiry} minutes</strong>. Do not share this code.</p>
            </div>
            <div style='padding:20px;border-top:1px solid rgba(255,255,255,.06);text-align:center;'>
              <p style='color:rgba(255,255,255,.2);font-size:12px;margin:0;'>© Terminal 1 — The Restaurant, Cooch Behar</p>
            </div>
          </div>
        </body></html>";
    }
}
