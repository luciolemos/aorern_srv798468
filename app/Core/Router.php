<?php

namespace App\Core;

class Router {
    private static array $routes = [];
    private static array $middlewares = [];
    private static ?string $currentGroup = null;
    private static array $groupMiddleware = [];

    /**
     * Faz o parsing da URL vinda via .htaccess
     * Ex: /admin/posts/edit/3 → ['admin', 'posts', 'edit', '3']
     */
    public static function parseUrl(): array {
        $rawUrl = $_GET['url'] ?? '';
        $cleanUrl = self::sanitizeUrl($rawUrl);

        // Explode em segmentos
        $segments = array_filter(explode('/', $cleanUrl), fn($s) => $s !== '');

        return $segments ?: ['home'];
    }

    /**
     * Sanitiza a URL bruta
     */
    private static function sanitizeUrl(string $url): string {
        // Remove espaços, barras redundantes, e ataques básicos
        $url = trim($url);
        $url = preg_replace('/[^\w\-\/]/u', '', $url); // apenas letras, números, -, _
        $url = preg_replace('/\/+/', '/', $url); // remove barras duplicadas
        $url = trim($url, '/'); // remove barra final

        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * Registra rota GET
     * 
     * @param string $path Caminho da rota
     * @param callable|array $handler Controller@method ou callable
     * @param array $middleware Middlewares específicos da rota
     */
    public static function get(string $path, $handler, array $middleware = []): void
    {
        self::addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Registra rota POST
     */
    public static function post(string $path, $handler, array $middleware = []): void
    {
        self::addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Registra rota PUT
     */
    public static function put(string $path, $handler, array $middleware = []): void
    {
        self::addRoute('PUT', $path, $handler, $middleware);
    }

    /**
     * Registra rota DELETE
     */
    public static function delete(string $path, $handler, array $middleware = []): void
    {
        self::addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Registra rota PATCH
     */
    public static function patch(string $path, $handler, array $middleware = []): void
    {
        self::addRoute('PATCH', $path, $handler, $middleware);
    }

    /**
     * Registra rota para qualquer método
     */
    public static function any(string $path, $handler, array $middleware = []): void
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        foreach ($methods as $method) {
            self::addRoute($method, $path, $handler, $middleware);
        }
    }

    /**
     * Adiciona rota ao array de rotas
     */
    private static function addRoute(string $method, string $path, $handler, array $middleware): void
    {
        $path = self::currentGroup . $path;
        $middleware = array_merge(self::$groupMiddleware, $middleware);

        self::$routes[$method][$path] = [
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    /**
     * Cria grupo de rotas com prefixo e/ou middleware
     * 
     * @param string $prefix Prefixo do grupo
     * @param callable $callback Callback com rotas do grupo
     * @param array $middleware Middleware do grupo
     */
    public static function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $previousGroup = self::$currentGroup;
        $previousMiddleware = self::$groupMiddleware;

        self::$currentGroup = $prefix;
        self::$groupMiddleware = array_merge(self::$groupMiddleware, $middleware);

        $callback();

        self::$currentGroup = $previousGroup;
        self::$groupMiddleware = $previousMiddleware;
    }

    /**
     * Registra middleware global
     */
    public static function middleware(string $name, callable $handler): void
    {
        self::$middlewares[$name] = $handler;
    }

    /**
     * Despacha a requisição
     * 
     * @param Request $request
     */
    public static function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = '/' . trim($request->path(), '/');

        // Busca rota exata
        if (isset(self::$routes[$method][$path])) {
            $route = self::$routes[$method][$path];
            self::executeRoute($route, $request, []);
            return;
        }

        // Busca rota com parâmetros
        foreach (self::$routes[$method] ?? [] as $pattern => $route) {
            $params = self::matchRoute($pattern, $path);
            if ($params !== false) {
                self::executeRoute($route, $request, $params);
                return;
            }
        }

        // Rota não encontrada - deixa o App.php atual lidar
    }

    /**
     * Verifica se rota corresponde ao padrão
     */
    private static function matchRoute(string $pattern, string $path)
    {
        // Converte {id} em regex
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches)) {
            array_shift($matches); // Remove match completo
            return $matches;
        }

        return false;
    }

    /**
     * Executa a rota com middlewares
     */
    private static function executeRoute(array $route, Request $request, array $params): void
    {
        // Executa middlewares
        foreach ($route['middleware'] as $middlewareName) {
            if (isset(self::$middlewares[$middlewareName])) {
                $result = self::$middlewares[$middlewareName]($request);
                if ($result === false) {
                    return; // Middleware bloqueou
                }
            }
        }

        // Executa handler
        $handler = $route['handler'];

        if (is_callable($handler)) {
            call_user_func_array($handler, array_merge([$request], $params));
        } elseif (is_string($handler) && str_contains($handler, '@')) {
            [$controller, $method] = explode('@', $handler);
            $controllerInstance = new $controller();
            call_user_func_array([$controllerInstance, $method], array_merge([$request], $params));
        }
    }

    /**
     * Retorna todas as rotas registradas
     */
    public static function routes(): array
    {
        return self::$routes;
    }
}
