<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$appEnv = strtolower((string) ($_ENV['APP_ENV'] ?? 'production'));
$appDebug = in_array($appEnv, ['local', 'dev', 'development', 'test'], true);
$configuredAppUrl = trim((string) ($_ENV['APP_URL'] ?? ''));
$appTimezone = (string) ($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');

date_default_timezone_set($appTimezone);

$forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $forwardedProto === 'https';
$protocol = $https ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$requestPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$basePath = '/';

foreach (['/aorern', '/cbmrn', '/mvc'] as $prefix) {
    if (str_starts_with($requestPath, $prefix)) {
        $basePath = $prefix . '/';
        break;
    }
}

if (!defined('APP_ENV')) {
    define('APP_ENV', $appEnv);
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', $appDebug);
}

if (!defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE', $appTimezone);
}

if (!defined('BASE_URL')) {
    if ($configuredAppUrl !== '') {
        define('BASE_URL', rtrim($configuredAppUrl, '/') . '/');
    } else {
        define('BASE_URL', $protocol . $host . $basePath);
    }
}

if (!defined('DB_HOST')) {
    define('DB_HOST', (string) ($_ENV['DB_HOST'] ?? '127.0.0.1'));
}

if (!defined('DB_NAME')) {
    define('DB_NAME', (string) ($_ENV['DB_NAME'] ?? ''));
}

if (!defined('DB_USER')) {
    define('DB_USER', (string) ($_ENV['DB_USER'] ?? ''));
}

if (!defined('DB_PASS')) {
    define('DB_PASS', (string) ($_ENV['DB_PASS'] ?? ''));
}

if (!defined('RECAPTCHA_SITE_KEY')) {
    define('RECAPTCHA_SITE_KEY', (string) ($_ENV['RECAPTCHA_SITE_KEY'] ?? ''));
}

if (!defined('RECAPTCHA_SECRET_KEY')) {
    define('RECAPTCHA_SECRET_KEY', (string) ($_ENV['RECAPTCHA_SECRET_KEY'] ?? ''));
}

if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', (string) ($_ENV['SMTP_HOST'] ?? ''));
}

if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', (string) ($_ENV['SMTP_PORT'] ?? ''));
}

if (!defined('SMTP_USER')) {
    define('SMTP_USER', (string) ($_ENV['SMTP_USER'] ?? ''));
}

if (!defined('SMTP_PASS')) {
    define('SMTP_PASS', (string) ($_ENV['SMTP_PASS'] ?? ''));
}

if (!defined('SMTP_FROM')) {
    define('SMTP_FROM', (string) ($_ENV['SMTP_FROM'] ?? ''));
}

if (!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', (string) ($_ENV['SMTP_FROM_NAME'] ?? ''));
}

if (!defined('TINYMCE_API_KEY')) {
    define('TINYMCE_API_KEY', (string) ($_ENV['TINYMCE_API_KEY'] ?? 'no-api-key'));
}

if (!defined('GOOGLE_MAPS_API_KEY')) {
    define('GOOGLE_MAPS_API_KEY', (string) ($_ENV['GOOGLE_MAPS_API_KEY'] ?? ''));
}

if (!defined('EMERGENCY_PHONE_DIAL')) {
    define('EMERGENCY_PHONE_DIAL', preg_replace('/\D+/', '', (string) ($_ENV['EMERGENCY_PHONE_DIAL'] ?? '')));
}

if (!defined('EMERGENCY_PHONE_DISPLAY')) {
    define('EMERGENCY_PHONE_DISPLAY', (string) ($_ENV['EMERGENCY_PHONE_DISPLAY'] ?? ''));
}

if (!defined('INSTITUTIONAL_PHONE_DIAL')) {
    define('INSTITUTIONAL_PHONE_DIAL', preg_replace('/\D+/', '', (string) ($_ENV['INSTITUTIONAL_PHONE_DIAL'] ?? '')));
}

if (!defined('INSTITUTIONAL_PHONE_DISPLAY')) {
    define('INSTITUTIONAL_PHONE_DISPLAY', (string) ($_ENV['INSTITUTIONAL_PHONE_DISPLAY'] ?? ''));
}

if (!defined('INSTITUTIONAL_EMAIL_PRIMARY')) {
    define('INSTITUTIONAL_EMAIL_PRIMARY', (string) ($_ENV['INSTITUTIONAL_EMAIL_PRIMARY'] ?? ''));
}

if (!defined('INSTITUTIONAL_EMAIL_SECONDARY')) {
    define('INSTITUTIONAL_EMAIL_SECONDARY', (string) ($_ENV['INSTITUTIONAL_EMAIL_SECONDARY'] ?? ''));
}

if (!defined('INSTITUTIONAL_ADDRESS_LINE_1')) {
    define('INSTITUTIONAL_ADDRESS_LINE_1', (string) ($_ENV['INSTITUTIONAL_ADDRESS_LINE_1'] ?? ''));
}

if (!defined('INSTITUTIONAL_ADDRESS_LINE_2')) {
    define('INSTITUTIONAL_ADDRESS_LINE_2', (string) ($_ENV['INSTITUTIONAL_ADDRESS_LINE_2'] ?? ''));
}

if (!defined('WHATSAPP_PHONE_E164')) {
    define('WHATSAPP_PHONE_E164', preg_replace('/\D+/', '', (string) ($_ENV['WHATSAPP_PHONE_E164'] ?? '')));
}

if (!defined('WHATSAPP_PHONE_DISPLAY')) {
    define('WHATSAPP_PHONE_DISPLAY', (string) ($_ENV['WHATSAPP_PHONE_DISPLAY'] ?? ''));
}

if (!defined('WHATSAPP_MESSAGES')) {
    define('WHATSAPP_MESSAGES', [
        'general' => (string) ($_ENV['WHATSAPP_MESSAGE_GENERAL'] ?? ''),
        'emergency' => (string) ($_ENV['WHATSAPP_MESSAGE_EMERGENCY'] ?? ''),
        'technical_visit' => (string) ($_ENV['WHATSAPP_MESSAGE_TECHNICAL_VISIT'] ?? ''),
        'press' => (string) ($_ENV['WHATSAPP_MESSAGE_PRESS'] ?? ''),
        'prevention' => (string) ($_ENV['WHATSAPP_MESSAGE_PREVENTION'] ?? ''),
        'k9' => (string) ($_ENV['WHATSAPP_MESSAGE_K9'] ?? ''),
    ]);
}

return [
    'app' => [
        'env' => APP_ENV,
        'debug' => APP_DEBUG,
        'session_timeout' => (int) ($_ENV['SESSION_TIMEOUT'] ?? 600),
    ],
];
