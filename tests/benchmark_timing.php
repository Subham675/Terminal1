<?php
$pages = [
    '/'              => 'Home',
    '/auth/login'    => 'Login',
    '/auth/register' => 'Register',
    '/privacy'       => 'Privacy',
    '/terms'         => 'Terms',
    '/'              => 'Home Again',
    '/auth/login'    => 'Login Again',
];

$cookieJar = tempnam(sys_get_temp_dir(), 't1_cookie_');
echo "=== Benchmarking Page Navigation Latencies (Simulated Session) ===\n\n";

foreach ($pages as $uri => $label) {
    $t0 = microtime(true);
    $ch = curl_init('http://localhost/terminal1/public' . $uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    $content = curl_exec($ch);
    $status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ms      = round((microtime(true) - $t0) * 1000, 2);
    curl_close($ch);
    echo "  [{$status}] {$label} ({$uri}): {$ms} ms (" . strlen($content) . " bytes)\n";
}

@unlink($cookieJar);
echo "\nBenchmark finished.\n";
