<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\User;

/**
 * LoginController - Gerencia login de administradores
 * 
 * Fluxo:
 * 1. Usuários públicos acessam conteúdo sem login
 * 2. Para acessar funcionalidades, precisam se registrar (criar conta)
 * 3. Após aprovação pelo admin, fazem login com username + senha
 * 4. Baseado no role, veem conteúdos específicos do admin
 */
class LoginController extends Controller {
    
    /**
     * Exibe formulário de login para administradores/usuários registrados
     * GET /login/admin
     */
    public function admin() {
        // Redireciona se já logado
        if ($_SESSION['user_id'] ?? null) {
            $adminRoles = ['admin', 'gerente', 'operador'];
            $redirect = in_array($_SESSION['user_role'] ?? null, $adminRoles, true)
                ? BASE_URL . "admin/dashboard"
                : BASE_URL;
            header("Location: " . $redirect);
            exit;
        }

        $errorKey = $_GET['error'] ?? null;
        $successKey = $_GET['success'] ?? null;
        $error = null;
        $success = null;

        $errorMessages = [
            'admin_empty_fields' => 'Preencha usuário e senha.',
            'admin_invalid_credentials' => 'Credenciais inválidas. Confira seu usuário e senha.',
            'admin_not_authorized' => 'Sua conta ainda não foi aprovada pelo administrador.',
            'admin_inactive' => 'Sua conta foi desativada. Contate o administrador.',
        ];

        $successMessages = [
            'account_created' => 'Conta criada com sucesso! Agora você pode fazer login com suas credenciais abaixo.'
        ];

        if ($errorKey && isset($errorMessages[$errorKey])) {
            $error = $errorMessages[$errorKey];
        }

        if ($successKey && isset($successMessages[$successKey])) {
            $success = $successMessages[$successKey];
        }

        $this->renderTwig('auth/admin-login', [
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Processa login de usuários registrados
     * POST /login/authenticate-admin
     */
    public function authenticateAdmin() {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            header("Location: " . BASE_URL . "login/admin?error=admin_empty_fields");
            exit;
        }

        $userModel = new User();
        $user = $userModel->buscarPorUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            header("Location: " . BASE_URL . "login/admin?error=admin_invalid_credentials");
            exit;
        }

        // Verifica se a conta está ativa e aprovada
        if (($user['ativo'] ?? 0) != 1) {
            $errorCode = ($user['status'] ?? 'pendente') === 'pendente' 
                ? 'admin_not_authorized' 
                : 'admin_inactive';
            header("Location: " . BASE_URL . "login/admin?error=$errorCode");
            exit;
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_avatar'] = $user['avatar'] ?? '';
        $_SESSION['user_role'] = $user['role'];

        // Registra último login
        $userModel->registrarUltimoLogin($user['id']);

        // Redireciona conforme role
        $adminRoles = ['admin', 'gerente', 'operador'];
        $redirect = in_array($user['role'] ?? null, $adminRoles, true)
            ? BASE_URL . "admin/dashboard"
            : BASE_URL . "admin/perfil"; // usuário comum vê seu perfil na área admin

        header("Location: " . $redirect);
        exit;
    }

    /**
     * Faz logout do usuário
     * GET /login/logout
     */
    public function logout() {
        $_SESSION = [];
        session_destroy();
        header("Location: " . BASE_URL);
        exit;
    }
}
