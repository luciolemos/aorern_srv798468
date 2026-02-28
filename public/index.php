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

// Dispara o app
use App\Core\App;
$app = new App();

