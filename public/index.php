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

$routesFile = __DIR__ . '/../config/routes.php';
if (is_file($routesFile)) {
    require_once $routesFile;
    $request = Request::capture();
    $handled = Router::dispatch($request);
    if ($handled) {
        exit;
    }
}

// Fallback legado por convenção de URL.
$app = new App();

