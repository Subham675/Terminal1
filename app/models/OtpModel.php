<?php
class OtpModel {
    public static function generate(string $email, int $userId=null, string $purpose='google_verify'): string {
        Database::query('UPDATE otps SET used=TRUE WHERE email=? AND purpose=? AND used=FALSE',[$email,$purpose]);
        $otp = str_pad((string)random_int(0,999999),6,'0',STR_PAD_LEFT);
        $minutes = (int)env('OTP_EXPIRY_MINUTES', 10);
        Database::query(
            'INSERT INTO otps (user_id,email,otp_code,purpose,expires_at) VALUES (?,?,?,?, DATE_ADD(NOW(), INTERVAL ? MINUTE))',
            [$userId,$email,password_hash($otp,PASSWORD_DEFAULT),$purpose,$minutes]
        );
        return $otp;
    }
    public static function verify(string $email, string $code, string $purpose='google_verify'): bool {
        $rec = Database::row(
            'SELECT * FROM otps WHERE email=? AND purpose=? AND used=FALSE AND expires_at>NOW() ORDER BY created_at DESC LIMIT 1',
            [$email,$purpose]
        );
        if(!$rec) return false;
        if(!password_verify($code,$rec['otp_code'])) return false;
        Database::query('UPDATE otps SET used=TRUE WHERE id=?',[$rec['id']]);
        return true;
    }
}
