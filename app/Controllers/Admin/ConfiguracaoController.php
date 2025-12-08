<?php

namespace App\Controllers\Admin;


use App\Helpers\AdminHelper;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;

class ConfiguracaoController extends Controller {

    public function __construct() {
        AuthMiddleware::requireAuth();
    }

    /**
     * Exibe página de configurações
     */
    public function index() {
        PermissionMiddleware::authorize('configuracao:view');
        $user_id = $_SESSION['user_id'] ?? null;

        $data = [
            'title' => 'Configurações',
            'user_id' => $user_id
        ];

        $this->renderTwig('admin/settings/index', array_merge($data, AdminHelper::getUserData('settings')));
    }

    /**
     * Atualiza preferências do usuário
     */
    public function update() {
        $request = Request::capture();
        PermissionMiddleware::authorize('configuracao:edit');

        if (!$request->isPost()) {
            header('Location: ' . BASE_URL . 'admin/configuracoes');
            exit;
        }

        // Aqui você pode processar preferências
        // Por enquanto, apenas mostra um feedback

        $_SESSION['toast'] = [
            'type'    => 'success',
            'message' => 'Configurações salvas com sucesso!'
        ];

        header('Location: ' . BASE_URL . 'admin/configuracoes');
        exit;
    }
}
