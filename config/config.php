<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$appEnv = strtolower((string) ($_ENV['APP_ENV'] ?? 'production'));
$appDebug = in_array($appEnv, ['local', 'dev', 'development', 'test'], true);

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$protocol = $https ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$requestPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$basePath = str_starts_with($requestPath, '/mvc') ? '/mvc/' : '/';

if (!defined('APP_ENV')) {
    define('APP_ENV', $appEnv);
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', $appDebug);
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $protocol . $host . $basePath);
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

return [
    'app' => [
        'env' => APP_ENV,
        'debug' => APP_DEBUG,
        'session_timeout' => (int) ($_ENV['SESSION_TIMEOUT'] ?? 600),
    ],
];

