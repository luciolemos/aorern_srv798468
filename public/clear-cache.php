<?php
require_once __DIR__ . '/../config/config.php';

$allowedIps = ['127.0.0.1', '::1'];
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

if (!in_array($remoteAddr, $allowedIps, true)) {
    http_response_code(404);
    exit;
}

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OPcache cleared!';
} else {
    echo 'OPcache not enabled';
}
