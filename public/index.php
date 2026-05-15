<?php

// Autoload Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Carrega configurações e variáveis de ambiente
$config = require_once __DIR__ . '/../config/config.php';

// Só exibe erros em ambiente de desenvolvimento.
ini_set('display_errors', !empty($config['app']['debug']) ? '1' : '0');
error_reporting(E_ALL);

// Registra Exception Handler
use App\Core\ExceptionHandler;
ExceptionHandler::register();

// Inicia sessão
session_start();

// Middleware de timeout opcional
if (isset($_SESSION['last_activity'])) {
    $timeout = $config['app']['session_timeout'] ?? 600;
    if (time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();
    }
}
$_SESSION['last_activity'] = time();

// Tenta primeiro rotas declarativas (migração gradual); se não casar, usa fallback legado.
use App\Core\Router;
use App\Core\Request;
use App\Core\App;

/**
 * Registra quando uma requisição cai no fallback legado (App.php).
 */
function logRouterFallback(Request $request): void
{
    $projectRoot = dirname(__DIR__);
    $logsDir = $projectRoot . '/logs';
    $logFile = $logsDir . '/router-fallback.log';

    if (!is_dir($logsDir)) {
        @mkdir($logsDir, 0755, true);
    }

    $line = json_encode([
        'timestamp' => date('c'),
        'method' => $request->method(),
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'path' => $request->path(),
        'base_url' => defined('BASE_URL') ? BASE_URL : null,
        'session_user_id' => $_SESSION['user_id'] ?? null,
        'session_user_role' => $_SESSION['user_role'] ?? null,
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (is_string($line)) {
        @file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND);
    }
}

$routesFile = __DIR__ . '/../config/routes.php';
if (is_file($routesFile)) {
    require_once $routesFile;
    $request = Request::capture();
    $handled = Router::dispatch($request);
    if ($handled) {
        exit;
    }
    logRouterFallback($request);
}

// Fallback legado por convenção de URL.
$app = new App();

