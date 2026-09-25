<?php
/**
 * Real-time updates via Server-Sent Events (SSE) — a one-way, push-based
 * connection from server to browser over plain HTTP. No extra server process,
 * no WebSocket library — works with plain PHP-FPM + Nginx.
 *
 * Trade-off to be upfront about: each open SSE connection holds one PHP-FPM
 * worker for its duration. Fine for a small canteen app with a handful of
 * concurrent viewers; would need a dedicated WebSocket server (or a hosted
 * service like Pusher) at real scale. Each stream below auto-closes after
 * ~55 seconds and the browser's EventSource reconnects automatically —
 * this keeps any single request short instead of tying up a worker forever.
 */
class TrackingController {
    private static function startStream(): void {
        set_time_limit(0);
        ignore_user_abort(true);
        // Defense-in-depth for XAMPP/Apache setups where php.ini may have compression
        // or buffering enabled by default — both would break real-time streaming.
        @ini_set('zlib.output_compression', '0');
        @ini_set('output_buffering', 'off');
        @ini_set('implicit_flush', '1');
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // hint for reverse proxies; nginx also needs fastcgi_buffering off (see nginx.conf)
        while (ob_get_level() > 0) ob_end_flush();
    }

    private static function sendEvent(string $event, array $data): void {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data) . "\n\n";
        @flush();
    }

    /** GET /track/stream?token=.. (or ?id=..&token=..) — customer watching their OWN booking live. */
    public static function customerStream(): void {
        $id    = (int) ($_GET['id'] ?? 0);
        $token = $_GET['token'] ?? '';

        if (!$id && !$token) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid or missing tracking parameters.']);
            return;
        }

        // If only non-enumerable token is provided, safely lookup booking by token
        if (!$id && $token) {
            $found = Booking::findByToken($token);
            if ($found) {
                $id = (int)$found['id'];
            }
        }

        // Strict server-side ownership check: if ID was modified or tampered with, server blocks immediately
        $booking = verifyBookingOwnership($id, $token);

        self::startStream();
        $lastSnapshot = null;
        $start = time();

        while (time() - $start < 55) {
            $booking = Booking::findByIdAndToken($id, $token);
            if (!$booking) { self::sendEvent('error', ['message' => 'Booking not found.']); break; }

            $snapshot = $booking['status'] . '|' . $booking['payment_status'];
            if ($snapshot !== $lastSnapshot) {
                self::sendEvent('status_update', [
                    'status'         => $booking['status'],
                    'payment_status' => $booking['payment_status'],
                    'updated_at'     => $booking['updated_at'],
                ]);
                $lastSnapshot = $snapshot;
            } else {
                echo ": keep-alive\n\n"; // SSE comment line — keeps proxies/browsers from timing out the connection
                @flush();
            }
            if (connection_aborted()) break;
            sleep(3);
        }
    }

    /** GET /admin/track/stream — logged-in admins get a live "new booking" ping without polling. */
    public static function adminStream(): void {
        requireLogin(); requireAdmin();
        session_write_close(); // release the session lock so other admin-panel tabs aren't blocked while this stream is open

        self::startStream();
        $lastSeenId = Booking::latestId();
        $start = time();

        while (time() - $start < 55) {
            $currentMaxId = Booking::latestId();
            if ($currentMaxId > $lastSeenId) {
                self::sendEvent('new_booking', [
                    'latest_id'     => $currentMaxId,
                    'pending_count' => Booking::countByStatus('pending'),
                ]);
                $lastSeenId = $currentMaxId;
            } else {
                echo ": keep-alive\n\n";
                @flush();
            }
            if (connection_aborted()) break;
            sleep(3);
        }
    }
}
