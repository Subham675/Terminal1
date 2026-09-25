<?php
// Ensures a .env file exists (copied from .env.example) so that including
// app/config/config.php during tests doesn't throw on a missing file.
// Uses placeholder values only — no real DB/API calls happen in these unit tests.
$root = dirname(__DIR__);
if (!file_exists("$root/.env") && file_exists("$root/.env.example")) {
    copy("$root/.env.example", "$root/.env");
}

require_once "$root/app/config/config.php";
require_once "$root/app/config/Razorpay.php";
