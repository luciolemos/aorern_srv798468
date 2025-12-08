<?php

namespace App\Controllers\Admin;


use App\Helpers\AdminHelper;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Models\User;
 use App\Helpers\Validator;
 use App\Helpers\CsrfHelper;

class AlterarSenhaController extends Controller {

    private User $userModel;

    public function __construct() {
        // Middleware de autenticação obrigatória
        if (!AuthMiddleware::isAuthenticated()) {
            header('Location: ' . BASE_URL . 'admin/auth');
            exit;
        }

        $this->userModel = new User();
    }

    /**
     * Exibe página de alterar senha
     */
    public function index() {
        $data = [
            'title' => 'Alterar Senha',
            'csrf_token' => CsrfHelper::generateToken()
        ];

        $this->renderTwig('admin/change-password/index', array_merge($data, AdminHelper::getUserData('change-password')));
    }

    /**
     * Processa alteração de senha
     */
    public function update() {
        \App\Helpers\CsrfHelper::verifyOrDie();
        
        $request = Request::capture();

        if (!$request->isPost()) {
            header('Location: ' . BASE_URL . 'admin/alterar-senha');
            exit;
        }

        $user_id = $_SESSION['user_id'] ?? null;

        if (!$user_id) {
            $_SESSION['toast'] = [
                'type' => 'danger',
                'message' => 'Erro de autenticação. Faça login novamente.'
            ];
            header('Location: ' . BASE_URL . 'admin/auth');
            exit;
        }

        // Valida dados
        $validator = Validator::make($request->post(), [
            'current_password'      => 'required',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            $errors = array_map(fn($e) => $e[0], $validator->errors());
            $_SESSION['toast'] = [
                'type'    => 'danger',
                'message' => 'Erro na validação: ' . implode(', ', $errors)
            ];
            header('Location: ' . BASE_URL . 'admin/alterar-senha');
            exit;
        }

        $current_password = $request->post('current_password');
        $password = $request->post('password');
        $password_confirm = $request->post('password_confirmation');

        // Busca usuário atual
        $user = $this->userModel->buscarPorId($user_id);

        if (!$user) {
            $_SESSION['toast'] = [
                'type'    => 'danger',
                'message' => 'Usuário não encontrado!'
            ];
            header('Location: ' . BASE_URL . 'admin/alterar-senha');
            exit;
        }

        // Valida senha atual
        if (!password_verify($current_password, $user['password'])) {
            $_SESSION['toast'] = [
                'type'    => 'danger',
                'message' => 'Senha atual incorreta!'
            ];
            header('Location: ' . BASE_URL . 'admin/alterar-senha');
            exit;
        }

        // Valida confirmação de nova senha
        if ($password !== $password_confirm) {
            $_SESSION['toast'] = [
                'type'    => 'danger',
                'message' => 'As novas senhas não conferem!'
            ];
            header('Location: ' . BASE_URL . 'admin/alterar-senha');
            exit;
        }

        // Valida se nova senha é diferente da atual
        if (password_verify($password, $user['password'])) {
            $_SESSION['toast'] = [
                'type'    => 'danger',
                'message' => 'A nova senha deve ser diferente da senha atual!'
            ];
            header('Location: ' . BASE_URL . 'admin/alterar-senha');
            exit;
        }

        // Hash da nova senha
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Atualiza senha
        if ($this->userModel->atualizarSenha($user_id, $password_hash)) {
            // Usa cookie temporário para passar mensagem após logout
            setcookie(
                'flash_message',
                json_encode([
                    'type' => 'success',
                    'message' => 'Senha alterada com sucesso! Faça login novamente.'
                ]),
                time() + 10, // Expira em 10 segundos
                '/',
                '',
                false, // não requer HTTPS para desenvolvimento
                true   // httponly
            );
            
            // Logout do usuário
            AuthMiddleware::logout();
            
            header('Location: ' . BASE_URL . 'admin/auth');
            exit;
        }

        $_SESSION['toast'] = [
            'type'    => 'danger',
            'message' => 'Erro ao alterar senha. Tente novamente.'
        ];
        header('Location: ' . BASE_URL . 'admin/alterar-senha');
        exit;
    }
}
