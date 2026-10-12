<?php
// Redirect root /terminal1/ to public/
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$target = '/terminal1/public/' . ($queryString ? '?' . $queryString : '');
header('Location: ' . $target, true, 302);
exit;
