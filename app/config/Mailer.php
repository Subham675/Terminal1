<?php
class Mailer {
    public static function sendOtp(string $toEmail, string $toName, string $otp, string $purpose = 'verification'): bool {
        $subject = match($purpose) {
            'google_verify' => 'Terminal 1 — Verify Your Google Login',
            'register'      => 'Terminal 1 — Confirm Your Registration',
            default         => 'Terminal 1 — Your OTP Code',
        };
        $html = self::otpTemplate($toName, $otp, $purpose);
        return self::send($toEmail, $toName, $subject, $html);
    }

    private static function send(string $to, string $toName, string $subject, string $htmlBody): bool {
        $composerPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (file_exists($composerPath)) {
            require_once $composerPath;
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return self::sendViaPHPMailer($to, $toName, $subject, $htmlBody);
            }
        }
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . env('MAIL_FROM_NAME') . " <" . env('MAIL_FROM_ADDRESS') . ">\r\n";
        $result = mail($to, $subject, $htmlBody, $headers);
        if (!$result) error_log("[Mailer] mail() failed for: $to");
        return $result;
    }

    private static function sendViaPHPMailer(string $to, string $toName, string $subject, string $html): bool {
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
            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log('[Mailer] PHPMailer error: ' . $e->getMessage());
            return false;
        }
    }

    private static function otpTemplate(string $name, string $otp, string $purpose): string {
        $purposeText = match($purpose) {
            'google_verify' => 'verify your Google account login',
            'register'      => 'complete your registration',
            default         => 'verify your identity',
        };
        $expiry = env('OTP_EXPIRY_MINUTES', 10);
        return "<html><body style='background:#111009;font-family:sans-serif;padding:40px;'>
          <div style='max-width:520px;margin:0 auto;background:#1E1C18;border:1px solid #C8860A55;border-radius:8px;overflow:hidden;'>
            <div style='background:#C8860A;padding:28px;text-align:center;'>
              <h1 style='color:#fff;margin:0;letter-spacing:2px;'>TERMINAL 1</h1>
              <p style='color:rgba(255,255,255,.7);margin:4px 0 0;font-size:12px;letter-spacing:3px;'>THE STARTUP CANTEEN</p>
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
              <p style='color:rgba(255,255,255,.2);font-size:12px;margin:0;'>© Terminal 1 — The Startup Canteen, Cooch Behar</p>
            </div>
          </div>
        </body></html>";
    }
}
