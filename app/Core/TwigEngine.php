<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use App\Helpers\Toast;
use App\Helpers\TextHelper;

class TwigEngine {
    private static $instance;
    private $twig;

    private function __construct() {
        $loader = new FilesystemLoader(__DIR__ . '/../Views/templates');
        // Disable Twig cache to avoid permission issues across users (CLI vs web server)
        $cacheConfig = false;

        $this->twig = new Environment($loader, [
            'cache' => $cacheConfig,
            'auto_reload' => true,
            'debug' => $_ENV['APP_ENV'] === 'dev'
        ]);

        // Adicionar funções globais
        $this->twig->addGlobal('BASE_URL', BASE_URL);
        $this->twig->addGlobal('APP_ENV', $_ENV['APP_ENV'] ?? 'prod');
        $this->twig->addGlobal('TINYMCE_API_KEY', TINYMCE_API_KEY ?? 'no-api-key');
        $this->twig->addGlobal('toast_html', Toast::render());
        
        // Adicionar dados da sessão para acesso global nos templates
        $this->twig->addGlobal('session', [
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_name' => $_SESSION['user_name'] ?? null,
            'user_email' => $_SESSION['user_email'] ?? null,
            'user_avatar' => $_SESSION['user_avatar'] ?? null,
            'user_role' => $_SESSION['user_role'] ?? null,
        ]);
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $parsedPath = parse_url($requestUri, PHP_URL_PATH);
        $currentPath = trim((string) ($parsedPath ?? '/'), '/');
        $this->twig->addGlobal('current_path', $currentPath);

        $this->twig->addFilter(new TwigFilter('excerpt', function (?string $content, int $limit = 160) {
            return TextHelper::excerpt($content, $limit);
        }));
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function render($template, $data = []) {
        return $this->twig->render($template . '.twig', $data);
    }

    public function getTwig() {
        return $this->twig;
    }
}
