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

        // 6. External Email Verification API (if configured in .env)
        $apiKey = function_exists('env') ? (string)env('EMAIL_VALIDATOR_API_KEY', '') : ($_ENV['EMAIL_VALIDATOR_API_KEY'] ?? (string)getenv('EMAIL_VALIDATOR_API_KEY'));
        if (!empty($apiKey)) {
            $provider = function_exists('env') ? (string)env('EMAIL_VALIDATOR_PROVIDER', 'auto') : ($_ENV['EMAIL_VALIDATOR_PROVIDER'] ?? 'auto');
            $apiResult = self::checkExternalApi($email, $apiKey, $provider);
            if ($apiResult !== null && !$apiResult[0]) {
                return [false, $apiResult[1], $email];
            }
        }

        // 7. Real-time direct SMTP mailbox verification (checks if mailbox exists on target mail server)
        $mailboxCheck = self::verifyMailboxExistence($email, $domain);
        if (!$mailboxCheck[0]) {
            return [false, $mailboxCheck[1], $email];
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

    /**
     * Connects directly to the recipient domain's primary MX server to verify
     * whether the target mailbox actually exists (RCPT TO handshake).
     * If the mail server responds with a 550/551/553 error (e.g. "The email account does not exist"),
     * it immediately rejects the email without sending any message.
     */
    public static function verifyMailboxExistence(string $email, string $domain): array {
        // Collect MX hosts
        $mxHosts = [];
        if (function_exists('getmxrr')) {
            @getmxrr($domain, $mxHosts);
        }
        if (empty($mxHosts)) {
            $mxHosts = [$domain];
        }

        $connected = false;
        $sock = null;
        foreach ($mxHosts as $host) {
            $sock = @fsockopen($host, 25, $errno, $errstr, 3);
            if ($sock) {
                $connected = true;
                break;
            }
        }

        if (!$connected || !$sock) {
            // Port 25 unreachable on network; allow through to avoid false rejections
            return [true, ''];
        }

        stream_set_timeout($sock, 3);
        $banner = @fgets($sock, 512);

        $heloDomain = function_exists('env') ? parse_url((string)env('APP_URL', 'http://terminal1.in'), PHP_URL_HOST) : 'terminal1.in';
        if (!$heloDomain || $heloDomain === 'localhost') $heloDomain = 'terminal1.in';

        @fputs($sock, "HELO {$heloDomain}\r\n");
        $heloRes = @fgets($sock, 512);

        $fromAddress = function_exists('env') ? (string)env('MAIL_FROM_ADDRESS', 'noreply@terminal1.in') : 'noreply@terminal1.in';
        @fputs($sock, "MAIL FROM:<{$fromAddress}>\r\n");
        $fromRes = @fgets($sock, 512);

        @fputs($sock, "RCPT TO:<{$email}>\r\n");
        $rcptRes = @fgets($sock, 512);

        @fputs($sock, "QUIT\r\n");
        @fclose($sock);

        $code = (int)substr(trim((string)$rcptRes), 0, 3);

        // Explicit rejection codes from mail servers (e.g. Google's 550 5.1.1 "The email account that you tried to reach does not exist")
        if (in_array($code, [550, 551, 552, 553, 554], true)) {
            return [false, "The email account '{$email}' does not exist on mail servers. Please enter a valid, active email address."];
        }

        return [true, ''];
    }

    /**
     * Queries an external Email Verification API (AbstractAPI, ZeroBounce, Hunter, Mailboxlayer, etc.)
     * Returns:
     * - [bool $isValid, string $errorMessage] if the API responded with a conclusive result
     * - null if the API key is not recognized or the service is temporarily unreachable (falls back safely)
     */
    public static function checkExternalApi(string $email, string $apiKey, string $provider = 'auto'): ?array {
        $apiKey = trim($apiKey);
        if ($apiKey === '') return null;

        $provider = strtolower(trim($provider));

        if ($provider === 'abstractapi' || $provider === 'abstract') {
            return self::callAbstractApi($email, $apiKey);
        } elseif ($provider === 'zerobounce') {
            return self::callZeroBounce($email, $apiKey);
        } elseif ($provider === 'hunter') {
            return self::callHunter($email, $apiKey);
        } elseif ($provider === 'mailboxlayer') {
            return self::callMailboxlayer($email, $apiKey);
        } elseif ($provider === 'apivoid') {
            return self::callApiVoid($email, $apiKey);
        } elseif ($provider === 'apininjas') {
            return self::callApiNinjas($email, $apiKey);
        }

        // Auto mode: probe supported providers in order of key popularity
        $res = self::callAbstractApi($email, $apiKey);
        if ($res !== null) return $res;

        $res = self::callZeroBounce($email, $apiKey);
        if ($res !== null) return $res;

        $res = self::callMailboxlayer($email, $apiKey);
        if ($res !== null) return $res;

        $res = self::callHunter($email, $apiKey);
        if ($res !== null) return $res;

        $res = self::callApiVoid($email, $apiKey);
        if ($res !== null) return $res;

        $res = self::callApiNinjas($email, $apiKey);
        if ($res !== null) return $res;

        return null;
    }

    private static function httpGet(string $url, array $headers = [], int $timeout = 3): ?string {
        $headerStr = "User-Agent: Terminal1/1.0\r\n";
        foreach ($headers as $k => $v) {
            $headerStr .= "{$k}: {$v}\r\n";
        }
        $ctx = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'header'        => $headerStr,
                'timeout'       => $timeout,
                'ignore_errors' => true,
            ]
        ]);
        $result = @file_get_contents($url, false, $ctx);
        return $result !== false ? $result : null;
    }

    private static function callAbstractApi(string $email, string $apiKey): ?array {
        $url = "https://emailvalidation.abstractapi.com/v1/?api_key=" . urlencode($apiKey) . "&email=" . urlencode($email);
        $raw = self::httpGet($url);
        if (!$raw) return null;

        $json = @json_decode($raw, true);
        if (!is_array($json) || isset($json['error'])) return null;

        if (isset($json['is_disposable_email']['value']) && $json['is_disposable_email']['value'] === true) {
            return [false, 'Temporary or disposable email addresses are not permitted.'];
        }

        if (isset($json['deliverability'])) {
            if ($json['deliverability'] === 'UNDELIVERABLE') {
                return [false, "The email address '{$email}' cannot receive emails or does not exist."];
            }
        }

        if (isset($json['is_valid_format']['value']) && $json['is_valid_format']['value'] === false) {
            return [false, 'Please enter a valid email address format.'];
        }

        return [true, ''];
    }

    private static function callZeroBounce(string $email, string $apiKey): ?array {
        $url = "https://api.zerobounce.net/v2/validate?api_key=" . urlencode($apiKey) . "&email=" . urlencode($email);
        $raw = self::httpGet($url);
        if (!$raw) return null;

        $json = @json_decode($raw, true);
        if (!is_array($json) || isset($json['error'])) return null;

        $status = strtolower($json['status'] ?? '');
        if ($status === 'invalid' || $status === 'spamtrap' || $status === 'abuse') {
            $sub = $json['sub_status'] ?? 'undeliverable';
            return [false, "The email address '{$email}' was flagged as invalid or unreachable ({$sub})."];
        }

        return [true, ''];
    }

    private static function callHunter(string $email, string $apiKey): ?array {
        $url = "https://api.hunter.io/v2/email-verifier?email=" . urlencode($email) . "&api_key=" . urlencode($apiKey);
        $raw = self::httpGet($url);
        if (!$raw) return null;

        $json = @json_decode($raw, true);
        if (!is_array($json) || isset($json['errors'])) return null;

        $result = $json['data']['result'] ?? '';
        if ($result === 'undeliverable') {
            return [false, "The email address '{$email}' could not be verified by mail servers."];
        }
        if (isset($json['data']['disposable']) && $json['data']['disposable'] === true) {
            return [false, 'Disposable email addresses are not allowed.'];
        }

        return [true, ''];
    }

    private static function callMailboxlayer(string $email, string $apiKey): ?array {
        $url = "http://apilayer.net/api/check?access_key=" . urlencode($apiKey) . "&email=" . urlencode($email);
        $raw = self::httpGet($url);
        if (!$raw) return null;

        $json = @json_decode($raw, true);
        if (!is_array($json) || !isset($json['format_valid'])) return null;

        if ($json['format_valid'] === false) {
            return [false, 'Invalid email format.'];
        }
        if (isset($json['disposable']) && $json['disposable'] === true) {
            return [false, 'Disposable email addresses are not permitted.'];
        }
        if (isset($json['mx_found']) && $json['mx_found'] === false) {
            return [false, 'The email domain has no mail servers configured.'];
        }

        return [true, ''];
    }

    private static function callApiVoid(string $email, string $apiKey): ?array {
        $ctx = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/json\r\nX-API-Key: {$apiKey}\r\n",
                'content'       => json_encode(['email' => $email]),
                'timeout'       => 3,
                'ignore_errors' => true,
            ]
        ]);
        $raw = @file_get_contents("https://api.apivoid.com/v2/email-verify", false, $ctx);
        if (!$raw) return null;

        $json = @json_decode($raw, true);
        if (!is_array($json) || isset($json['error'])) return null;

        if (isset($json['data']['is_disposable']) && $json['data']['is_disposable'] === true) {
            return [false, 'Temporary or disposable email addresses are not permitted.'];
        }

        return [true, ''];
    }

    private static function callApiNinjas(string $email, string $apiKey): ?array {
        $url = "https://api.api-ninjas.com/v1/validateemail?email=" . urlencode($email);
        $raw = self::httpGet($url, ['X-Api-Key' => $apiKey]);
        if (!$raw) return null;

        $json = @json_decode($raw, true);
        if (!is_array($json) || isset($json['error'])) return null;

        if (isset($json['is_valid']) && $json['is_valid'] === false) {
            return [false, "The email address '{$email}' is not valid."];
        }
        if (isset($json['is_disposable']) && $json['is_disposable'] === true) {
            return [false, 'Disposable email addresses are not permitted.'];
        }

        return [true, ''];
    }
}
