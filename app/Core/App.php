<?php

namespace App\Core;

class App {
    protected $controller;
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = Router::parseUrl();
        $this->debugLog(date('H:i:s') . " URL: " . json_encode($url));

        $this->resolveController($url);
        $this->execute();
    }

    private function resolveController(array $url): void {
        $mainRoute = $url[0] ?? 'home';

        if ($mainRoute === 'admin') {
            array_shift($url); // remove 'admin'

            $controllerPart = array_shift($url) ?? 'dashboard';
            // Normaliza caminhos como /admin/admin -> dashboard
            if ($controllerPart === 'admin' || $controllerPart === '') {
                $controllerPart = 'dashboard';
            }
            $methodRaw = array_shift($url) ?? 'index';
            $this->method = $this->sanitizeMethod($methodRaw);

            // Se alguém acessa caminhos quebrados tipo /admin/admin/auth/... normaliza para AuthController@index
            if ($controllerPart === 'dashboard' && $this->method === 'auth') {
                $controllerPart = 'auth';
                // tenta usar próximo segmento como método, senão index
                $nextRaw = array_shift($url) ?? 'index';
                $allowed = ['index', 'login', 'register', 'logout'];
                $nextSanitized = $this->sanitizeMethod($nextRaw);
                $this->method = in_array($nextSanitized, $allowed, true) ? $nextSanitized : 'index';
                $url = []; // evita passar lixo como params
                $this->debugLog("  Ajuste: redirecionando rota quebrada para auth/{$this->method}");
            }

            // Se o controller for auth e o método for inválido, cai em index
            if ($controllerPart === 'auth') {
                $allowed = ['index', 'login', 'register', 'logout'];
                if (!in_array($this->method, $allowed, true)) {
                    $this->method = 'index';
                    $url = [];
                    $this->debugLog('  Ajuste: método auth inválido -> index');
                }
            }

            $this->params = array_map('htmlspecialchars', $url);

            $this->debugLog("  Controller: $controllerPart | Method raw: $methodRaw | Method final: {$this->method}");

            $controllerClass = $this->buildControllerClass($controllerPart, 'Admin');
        } else {
            $controllerPart = $mainRoute;
            if ($controllerPart === 'blog' && !empty($url[1])) {
                $this->method = 'post';
                $this->params = array_slice($url, 1);
            } else {
                $this->method = $this->sanitizeMethod($url[1] ?? 'index');
                $this->params = array_slice($url, 2);
            }

            $controllerClass = "\\App\\Controllers\\Site\\" . ucfirst($controllerPart) . "Controller";
        }

        $this->debugLog("  Class: $controllerClass | Method: {$this->method}");

        if (!class_exists($controllerClass)) {
            $this->notFound("Controller não encontrado: $controllerClass");
        }

        $this->controller = new $controllerClass();

        if (!method_exists($this->controller, $this->method)) {
            $this->notFound("Método '{$this->method}' não encontrado em $controllerClass");
        }
    }

    private function buildControllerClass(string $path, string $area): string {
        $segments = explode('/', $path);
        $controllerPart = array_pop($segments);
        
        // Converte kebab-case para PascalCase (alterar-senha -> AlterarSenha)
        $controllerName = str_replace('-', ' ', $controllerPart);
        $controllerName = str_replace(' ', '', ucwords($controllerName));
        $controller = $controllerName . 'Controller';
        
        $namespace = implode('\\', array_map('ucfirst', $segments));

        return "\\App\\Controllers\\{$area}" . ($namespace ? "\\{$namespace}" : '') . "\\{$controller}";
    }

    private function sanitizeMethod(string $method): string {
        // Converte kebab-case para camelCase (update-avatar -> updateAvatar)
        $method = preg_replace_callback('/-([a-z])/', function($matches) {
            return strtoupper($matches[1]);
        }, strtolower($method));
        
        // Remove caracteres não permitidos
        return preg_replace('/[^a-zA-Z0-9_]/', '', $method);
    }

    private function execute(): void {
        $this->debugLog(date('H:i:s') . " Executando: " . get_class($this->controller) . "::{$this->method}()");
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function notFound(string $message): void {
        http_response_code(404);

        if (defined('APP_DEBUG') && APP_DEBUG === true) {
            die("404 - {$message}");
        }

        die('Página não encontrada.');
    }

    private function debugLog(string $message): void {
        if (!defined('APP_DEBUG') || APP_DEBUG !== true) {
            return;
        }

        $logPath = __DIR__ . '/../../logs/route-debug.log';
        file_put_contents($logPath, $message . PHP_EOL, FILE_APPEND);
    }
}
