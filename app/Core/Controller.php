<?php

namespace App\Core;

use App\Core\TwigEngine;

class Controller {
    protected function view($view, $data = [], $layout = 'main') {
        $viewPath = "../app/Views/{$view}.php";
        if (file_exists($viewPath)) {
            $GLOBALS['view'] = $view;
            extract($data);

            // Renderiza layout correto
            require_once "../app/Views/layouts/{$layout}.php";
        } else {
            throw new \RuntimeException($this->buildMissingViewMessage((string) $view, $viewPath));
        }
    }

    /**
     * Renderiza template Twig
     */
    protected function renderTwig(string $template, array $data = []): void {
        $twig = TwigEngine::getInstance();
        echo $twig->render($template, $data);
    }

    protected function model($model) {
        $modelClass = "\\App\\Models\\$model";
        return new $modelClass();
    }

    private function buildMissingViewMessage(string $view, string $viewPath): string
    {
        if (defined('APP_DEBUG') && APP_DEBUG === true) {
            return "View '{$view}' não encontrada em {$viewPath}";
        }

        return 'Falha ao renderizar a página solicitada.';
    }
}
