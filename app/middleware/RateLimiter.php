<?php
/**
 * RateLimiter — Brute-force & credential stuffing defense
 * Features:
 *  - 100% Parameter-bound PDO queries (no SQL interpolation)
 *  - Escalating cooldown tiers: 15 min -> 1 hour -> 24 hour lockout on repeat abuse
 *  - Lightweight automated bot challenge (CAPTCHA) triggered after 2-3 failures
 */
class RateLimiter {
    /**
     * Checks if the identifier is blocked.
     * Evaluates escalating cooldowns:
     *   - Tier 3: >= 15 failed attempts in 24 hours -> 24-hour lockout
     *   - Tier 2: >= 10 failed attempts in 24 hours -> 1-hour cooldown
     *   - Tier 1: >= $maxAttempts in $windowMinutes -> $windowMinutes cooldown
     */
    public static function isBlocked(string $identifier, string $type, int $maxAttempts = 5, int $windowMinutes = 15): bool {
        // Tier 3 check (24 hour window)
        $cutoff24h = date('Y-m-d H:i:s', time() - 86400);
        $row24 = Database::row(
            'SELECT COUNT(*) as c FROM auth_attempts
             WHERE identifier=? AND attempt_type=? AND success=0 AND created_at >= ?',
            [$identifier, $type, $cutoff24h]
        );
        $count24 = (int) ($row24['c'] ?? 0);

        if ($count24 >= 15) {
            return true; // 24h lockout
        }

        // Tier 2 check (1 hour window if 10+ attempts in 24h)
        if ($count24 >= 10) {
            $cutoff1h = date('Y-m-d H:i:s', time() - 3600);
            $row1h = Database::row(
                'SELECT COUNT(*) as c FROM auth_attempts
                 WHERE identifier=? AND attempt_type=? AND success=0 AND created_at >= ?',
                [$identifier, $type, $cutoff1h]
            );
            if ((int) ($row1h['c'] ?? 0) >= 3) {
                return true;
            }
        }

        // Tier 1 standard window check
        $cutoff = date('Y-m-d H:i:s', time() - ($windowMinutes * 60));
        $row = Database::row(
            'SELECT COUNT(*) as c FROM auth_attempts
             WHERE identifier=? AND attempt_type=? AND success=0 AND created_at >= ?',
            [$identifier, $type, $cutoff]
        );
        return (int) ($row['c'] ?? 0) >= $maxAttempts;
    }

    public static function recordAttempt(string $identifier, string $type, bool $success): void {
        Database::query(
            'INSERT INTO auth_attempts (identifier, attempt_type, success) VALUES (?,?,?)',
            [$identifier, $type, (int) $success]
        );
    }

    /** Clears failed-attempt history after a successful login (fresh start). */
    public static function clearAttempts(string $identifier, string $type): void {
        Database::query('DELETE FROM auth_attempts WHERE identifier=? AND attempt_type=?', [$identifier, $type]);
    }

    /**
     * Calculates remaining cooldown in minutes based on escalating tier.
     */
    public static function remainingCooldownMinutes(string $identifier, string $type, int $windowMinutes = 15): int {
        $cutoff24h = date('Y-m-d H:i:s', time() - 86400);
        $row24 = Database::row(
            'SELECT COUNT(*) as c, MIN(created_at) as oldest, MAX(created_at) as latest FROM auth_attempts
             WHERE identifier=? AND attempt_type=? AND success=0 AND created_at >= ?',
            [$identifier, $type, $cutoff24h]
        );
        $count24 = (int) ($row24['c'] ?? 0);

        if ($count24 >= 15 && !empty($row24['latest'])) {
            $elapsedSinceLatest = time() - strtotime($row24['latest']);
            return max(1, (int) ceil((86400 - $elapsedSinceLatest) / 60));
        }

        if ($count24 >= 10 && !empty($row24['latest'])) {
            $elapsedSinceLatest = time() - strtotime($row24['latest']);
            return max(1, (int) ceil((3600 - $elapsedSinceLatest) / 60));
        }

        $cutoff = date('Y-m-d H:i:s', time() - ($windowMinutes * 60));
        $row = Database::row(
            'SELECT MIN(created_at) as oldest FROM auth_attempts
             WHERE identifier=? AND attempt_type=? AND success=0 AND created_at >= ?',
            [$identifier, $type, $cutoff]
        );
        if (empty($row['oldest'])) return 0;
        $elapsed = time() - strtotime($row['oldest']);
        return max(0, (int) ceil(($windowMinutes * 60 - $elapsed) / 60));
    }

    /**
     * Determines whether CAPTCHA is required (e.g. after 2 failed attempts in recent 15 mins).
     */
    public static function requiresCaptcha(string $identifier, string $type = 'login', int $threshold = 2): bool {
        $cutoff = date('Y-m-d H:i:s', time() - 900); // 15 mins
        $row = Database::row(
            'SELECT COUNT(*) as c FROM auth_attempts
             WHERE identifier=? AND attempt_type=? AND success=0 AND created_at >= ?',
            [$identifier, $type, $cutoff]
        );
        return ((int) ($row['c'] ?? 0)) >= $threshold;
    }

    /**
     * Generates a lightweight anti-bot math puzzle stored securely in session.
     */
    public static function getCaptchaChallenge(): array {
        if (!isset($_SESSION['captcha_num1']) || !isset($_SESSION['captcha_num2'])) {
            $_SESSION['captcha_num1'] = random_int(2, 9);
            $_SESSION['captcha_num2'] = random_int(2, 9);
            $_SESSION['captcha_op']   = '+';
            $_SESSION['captcha_ans']  = $_SESSION['captcha_num1'] + $_SESSION['captcha_num2'];
        }
        return [
            'question' => "{$_SESSION['captcha_num1']} + {$_SESSION['captcha_num2']} = ?",
            'field'    => 'captcha_answer',
        ];
    }

    /**
     * Refreshes the captcha challenge after an attempt.
     */
    public static function refreshCaptcha(): void {
        unset($_SESSION['captcha_num1'], $_SESSION['captcha_num2'], $_SESSION['captcha_op'], $_SESSION['captcha_ans']);
        self::getCaptchaChallenge();
    }

    /**
     * Verifies the submitted CAPTCHA response.
     */
    public static function verifyCaptcha(?string $answer): bool {
        $expected = (string) ($_SESSION['captcha_ans'] ?? '');
        self::refreshCaptcha(); // one-time use per challenge
        if ($expected === '' || $answer === null) return false;
        return hash_equals($expected, trim($answer));
    }
}
