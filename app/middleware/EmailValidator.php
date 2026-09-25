<?php
class EmailValidator {
    private const COMMON_TYPOS = [
        'gmai.com'     => 'gmail.com',
        'gamil.com'    => 'gmail.com',
        'gmial.com'    => 'gmail.com',
        'gmaill.com'   => 'gmail.com',
        'gmal.com'     => 'gmail.com',
        'gmail.co'     => 'gmail.com',
        'gmaul.com'    => 'gmail.com',
        'yaho.com'     => 'yahoo.com',
        'yahooo.com'   => 'yahoo.com',
        'yaho.co.in'   => 'yahoo.co.in',
        'hotmial.com'  => 'hotmail.com',
        'hotmaill.com' => 'hotmail.com',
        'outlok.com'   => 'outlook.com',
        'outloo.com'   => 'outlook.com',
    ];

    private const DISPOSABLE_DOMAINS = [
        'mailinator.com', '10minutemail.com', 'tempmail.com', 'temp-mail.org',
        'guerrillamail.com', 'trashmail.com', 'sharklasers.com', 'dispostable.com',
        'throwawaymail.com', 'fakemailgenerator.com', 'getairmail.com', 'yopmail.com',
        'crazymailing.com', 'mytemp.email', 'tempail.com', 'generator.email',
        'maildrop.cc', 'inboxkitten.com', 'nada.ltd', 'mohmal.com'
    ];

    /**
     * Validates an email address.
     * Returns array [bool $isValid, string $errorMessage, string $cleanedEmail]
     */
    public static function validate(string $email): array {
        $email = trim(strtolower($email));

        // 1. Basic PHP filter validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Please enter a valid email address format (e.g. yourname@example.com).', $email];
        }

        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            return [false, 'Invalid email address structure.', $email];
        }

        [$username, $domain] = $parts;

        // 2. Check for common domain typos
        if (isset(self::COMMON_TYPOS[$domain])) {
            $suggested = self::COMMON_TYPOS[$domain];
            return [false, "Invalid domain '@{$domain}'. Did you mean '@{$suggested}'?", $email];
        }

        // 3. Block known disposable/burner email services
        if (in_array($domain, self::DISPOSABLE_DOMAINS, true)) {
            return [false, 'Temporary or disposable email addresses are not permitted. Please use your permanent email.', $email];
        }

        // 4. Strict validation for Gmail / Googlemail addresses
        if ($domain === 'gmail.com' || $domain === 'googlemail.com') {
            $gmailValidation = self::validateGmailUsername($username);
            if (!$gmailValidation[0]) {
                return [false, $gmailValidation[1], $email];
            }
        }

        // 5. DNS MX record validation (ensures domain can receive mail)
        if (function_exists('checkdnsrr')) {
            $hasMx = @checkdnsrr($domain, 'MX');
            $hasA  = @checkdnsrr($domain, 'A');
            if (!$hasMx && !$hasA) {
                return [false, "The domain '@{$domain}' does not exist or has no active mail servers.", $email];
            }
        }

        return [true, '', $email];
    }

    /**
     * Validates a Gmail username according to Google Account specifications:
     * - Must be 6 to 30 characters long
     * - Only letters, numbers, and periods
     * - Cannot start or end with a period
     * - Cannot contain consecutive periods (..)
     */
    public static function validateGmailUsername(string $username): array {
        if (str_contains($username, '+')) {
            $username = explode('+', $username, 2)[0];
        }

        $rawWithoutDots = str_replace('.', '', $username);

        if (strlen($rawWithoutDots) < 6) {
            return [false, "Gmail username must be at least 6 characters long ('{$username}' has only " . strlen($rawWithoutDots) . " characters)."];
        }

        if (strlen($username) > 30) {
            return [false, 'Gmail username cannot exceed 30 characters.'];
        }

        if (str_starts_with($username, '.') || str_ends_with($username, '.')) {
            return [false, 'Gmail username cannot begin or end with a period.'];
        }

        if (str_contains($username, '..')) {
            return [false, 'Gmail username cannot contain consecutive periods (..).'];
        }

        if (!preg_match('/^[a-z0-9.]+$/', $username)) {
            return [false, 'Gmail username can only contain letters (a-z), numbers (0-9), and periods.'];
        }

        return [true, ''];
    }
}
