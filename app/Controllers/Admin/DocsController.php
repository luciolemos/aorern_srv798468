<?php

namespace App\Controllers\Admin;


use App\Helpers\AdminHelper;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;

class DocsController extends Controller
{
    public function __construct()
    {
        // Protege todas as rotas de documentação
        AuthMiddleware::requireAuth();
        PermissionMiddleware::authorize('docs:view');
    }
    
    public function estrutura() {
        $this->renderTwig('admin/documents/estrutura', AdminHelper::getUserData('docs'));
    }

    public function virtualhost() {
        $this->renderTwig('admin/documents/virtualhost', AdminHelper::getUserData('docs'));
    }

    public function composer() {
        $this->renderTwig('admin/documents/composer', AdminHelper::getUserData('docs'));
    }

    public function diagrama() {
        $this->renderTwig('admin/documents/diagrama', AdminHelper::getUserData('docs'));
    }

    public function caracteristicas() {
        $this->renderTwig('admin/documents/caracteristicas', AdminHelper::getUserData('docs'));
    }

    public function fluxomvc() {
        $this->renderTwig('admin/documents/fluxomvc', AdminHelper::getUserData('docs'));
    }

    public function fluxopost() {
        $this->renderTwig('admin/documents/fluxopost', AdminHelper::getUserData('docs'));
    }

    public function novofluxomvc() {
        $this->renderTwig('admin/documents/novofluxomvc', AdminHelper::getUserData('docs'));
    }

    public function blog() {
        $this->renderTwig('admin/documents/blog', AdminHelper::getUserData('docs'));
    }

    public function elements() {
        $this->renderTwig('admin/documents/elements', AdminHelper::getUserData('docs'));
    }

    public function scripts() {
        $this->renderTwig('admin/documents/scriptssql', AdminHelper::getUserData('docs'));
    }

    public function index() {
        // Redireciona para uma das páginas documentadas, ou mostra uma visão geral
        $this->renderTwig('admin/documents/index', AdminHelper::getUserData('docs')); // Crie essa view
    }

}
