<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\User;

class RegisterController extends Controller {
    private const USER_AVATAR_DIR = 'uploads/users/';
    public function index() {
        $errorKey = $_GET['error'] ?? null;
        $error = null;

        $messages = [
            'empty_fields' => 'Preencha todos os campos.',
            'empty_username' => 'Usuário é obrigatório.',
            'passwords_dont_match' => 'As senhas não conferem.',
            'invalid_email' => 'Email inválido.',
            'password_too_short' => 'A senha deve ter pelo menos 6 caracteres.',
            'username_already_exists' => 'Usuário já existe.',
            'email_already_exists' => 'Email já cadastrado.'
        ];

        if ($errorKey && isset($messages[$errorKey])) {
            $error = $messages[$errorKey];
        }
        
        $this->renderTwig('auth/register', ['error' => $error]);
    }

    public function store() {
        $username = trim($_POST['username'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validations
        if (empty($nome) || empty($email) || empty($password) || empty($password_confirm)) {
            header("Location: " . BASE_URL . "register?error=empty_fields");
            exit;
        }
        if (empty($username)) {
            header("Location: " . BASE_URL . "register?error=empty_username");
            exit;
        }

        if ($password !== $password_confirm) {
            header("Location: " . BASE_URL . "register?error=passwords_dont_match");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: " . BASE_URL . "register?error=invalid_email");
            exit;
        }

        if (strlen($password) < 6) {
            header("Location: " . BASE_URL . "register?error=password_too_short");
            exit;
        }

        // Check if email already exists
        $userModel = new User();
        // Verifica se username ou email já existem
        if ($userModel->usernameExiste($username)) {
            header("Location: " . BASE_URL . "register?error=username_already_exists");
            exit;
        }
        if ($userModel->emailExiste($email)) {
            header("Location: " . BASE_URL . "register?error=email_already_exists");
            exit;
        }

        // Avatar opcional
        $avatarPath = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarPath = $this->processAvatar($_FILES['avatar']);
        }

        // Cria usuário
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userModel->criar([
            'username' => $username,
            'email'    => $email,
            'password' => $hashedPassword,
            'avatar'   => $avatarPath,
            'role'     => 'usuario',
            'ativo'    => 0,
            'status'   => 'pendente'
        ]);

        // Redireciona para login admin com mensagem de sucesso
        header("Location: " . BASE_URL . "login/admin?success=account_created");
        exit;
    }

    /**
     * Salva avatar em uploads/users e retorna o caminho relativo
     */
    private function processAvatar(array $file): ?string {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowed, true)) {
            return null;
        }

        $publicRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? (dirname(__DIR__, 3) . '/public'), '/');
        $uploadsDir = $publicRoot . '/' . self::USER_AVATAR_DIR;
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0775, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $filename = uniqid('avatar_', true) . '.' . $ext;
        $destPath = $uploadsDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            // retorna caminho relativo para servir via BASE_URL
            return self::USER_AVATAR_DIR . $filename;
        }

        return null;
    }
}
