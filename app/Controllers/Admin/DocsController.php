<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;

class DocsController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::requireAdmin();
    }
    
    public function index(): void
    {
        header('Location: ' . BASE_URL . 'docs');
        exit;
    }

    public function doc(string $slug = ''): void
    {
        $target = 'docs';
        if ($slug !== '') {
            $target .= '/doc/' . rawurlencode($slug);
        }
        header('Location: ' . BASE_URL . $target);
        exit;
    }

    public function estrutura(): void
    {
        $this->doc('estrutura-md');
    }

    public function virtualhost(): void
    {
        $this->doc('deploy-md');
    }

    public function composer(): void
    {
        $this->doc('readme-md');
    }

    public function diagrama(): void
    {
        $this->doc('diagrama-arquitetura-md');
    }

    public function caracteristicas(): void
    {
        $this->doc('architecture-md');
    }

    public function fluxomvc(): void
    {
        $this->doc('architecture-md');
    }

    public function fluxopost(): void
    {
        $this->doc('post-workflow-md');
    }

    public function novofluxomvc(): void
    {
        $this->doc('architecture-md');
    }

    public function blog(): void
    {
        $this->doc('post-workflow-md');
    }

    public function elements(): void
    {
        $this->doc('componentes-md');
    }

    public function scripts(): void
    {
        $this->doc('sql-schema-inventory-md');
    }
}
