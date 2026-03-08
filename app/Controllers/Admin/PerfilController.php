<?php

namespace App\Controllers\Admin;


use App\Helpers\AdminHelper;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Models\User;

class PerfilController extends Controller {

    private User $userModel;
    private const USER_AVATAR_DIR = 'uploads/users/';

    public function __construct() {
        $this->userModel = new User();
        AuthMiddleware::requireAuth();
    }

    /**
     * Exibe formulário do perfil do usuário logado
     */
    public function index(): void {
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$user_id) {
            header('Location: ' . BASE_URL . 'admin/auth');
            exit;
        }

        $user = $this->userModel->buscarPorId($user_id);
        $csrf_token = \App\Helpers\CsrfHelper::generateToken();
        $title = 'Meu Perfil';

        $this->renderTwig('admin/profile/index', array_merge(
            AdminHelper::getUserData('profile'),
            compact('user', 'csrf_token', 'title')
        ));
    }

    /**
     * Atualiza dados do perfil (username, email e avatar)
     */
    public function update(): void {
        \App\Helpers\CsrfHelper::verifyOrDie();
        $request = Request::capture();

        if (!$request->isPost()) {
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        $user_id = $_SESSION['user_id'] ?? null;

        if (!$user_id) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro de autenticação.'];
            header('Location: ' . BASE_URL . 'admin/auth');
            exit;
        }

        $username = trim($request->post('username', ''));
        $email = trim($request->post('email', ''));

        if (empty($username)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Usuário não pode estar vazio.'];
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Email inválido.'];
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        // Prepara dados para atualização
        $dados = [
            'username' => $username,
            'email' => $email
        ];

        // Processa avatar se enviado
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatar = $this->processarAvatar($_FILES['avatar']);
            if ($avatar) {
                // Remove avatar antigo se existir
                $user = $this->userModel->buscarPorId($user_id);
                if ($user && !empty($user['avatar'])) {
                    $old_avatar = $this->resolvePublicRoot() . '/' . ltrim((string) $user['avatar'], '/');
                    if (file_exists($old_avatar)) {
                        @unlink($old_avatar);
                    }
                }
                $dados['avatar'] = $avatar;
                $_SESSION['user_avatar'] = $avatar;
            }
        }

        if ($this->userModel->atualizar($user_id, $dados)) {
            $_SESSION['user_name'] = $username;
            $_SESSION['user_email'] = $email;

            // Regenera token CSRF para próxima requisição
            \App\Helpers\CsrfHelper::generateToken();

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Perfil atualizado com sucesso!'];
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro ao atualizar perfil.'];
        header('Location: ' . BASE_URL . 'admin/perfil');
        exit;
    }

    /**
     * Atualiza avatar do usuário logado
     */
    public function updateAvatar(): void {
        \App\Helpers\CsrfHelper::verifyOrDie();
        $request = Request::capture();

        if (!$request->isPost()) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Método inválido.'];
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro de autenticação.'];
            header('Location: ' . BASE_URL . 'admin/auth');
            exit;
        }

        // Valida arquivo enviado
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Selecione um arquivo de imagem!'];
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        // Processa avatar
        $avatar = $this->processarAvatar($_FILES['avatar']);

        if (!$avatar) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Arquivo inválido! Use JPG, PNG ou WebP com tamanho máximo de 50MB.'];
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        // Remove avatar antigo se existir
        $user = $this->userModel->buscarPorId($user_id);
        if ($user && !empty($user['avatar'])) {
            $old_avatar = $this->resolvePublicRoot() . '/' . ltrim((string) $user['avatar'], '/');
            if (file_exists($old_avatar)) {
                @unlink($old_avatar);
            }
        }

        // Atualiza no banco
        if ($this->userModel->atualizar($user_id, ['avatar' => $avatar])) {
            // Atualiza sessão
            $_SESSION['user_avatar'] = $avatar;

            // Regenera token CSRF para próxima requisição
            \App\Helpers\CsrfHelper::generateToken();

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Avatar atualizado com sucesso!'];
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro ao atualizar avatar. Tente novamente.'];
        header('Location: ' . BASE_URL . 'admin/perfil');
        exit;
    }

    /**
     * Muda senha do usuário logado
     */
    public function changePassword(): void {
        \App\Helpers\CsrfHelper::verifyOrDie();
        $request = Request::capture();

        if (!$request->isPost()) {
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        $user_id = $_SESSION['user_id'] ?? null;

        if (!$user_id) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro de autenticação.'];
            header('Location: ' . BASE_URL . 'admin/auth');
            exit;
        }

        $current_password = $request->post('current_password', '');
        $new_password = $request->post('new_password', '');
        $confirm_password = $request->post('confirm_password', '');

        // Valida campos
        if (empty($current_password)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Senha atual é obrigatória.'];
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        if (empty($new_password) || strlen($new_password) < 6) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Nova senha deve ter no mínimo 6 caracteres.'];
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'As senhas não coincidem.'];
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        // Busca usuário e valida senha atual
        $user = $this->userModel->buscarPorId($user_id);
        if (!$user || !password_verify($current_password, $user['password'])) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Senha atual incorreta.'];
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        // Atualiza senha
        if ($this->userModel->atualizar($user_id, [
            'password' => password_hash($new_password, PASSWORD_BCRYPT)
        ])) {
            // Regenera token CSRF para próxima requisição
            \App\Helpers\CsrfHelper::generateToken();

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Senha alterada com sucesso!'];
            header('Location: ' . BASE_URL . 'admin/perfil');
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro ao alterar senha.'];
        header('Location: ' . BASE_URL . 'admin/perfil');
        exit;
    }

    /**
     * Processa upload de avatar - IDÊNTICO ao PessoalController
     */
    private function processarAvatar(array $file): ?string
    {
        // Validações básicas
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Tamanho máximo: 50MB
        $max_size = 50 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            return null;
        }

        // Tipos permitidos
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $tipos_permitidos)) {
            return null;
        }

        // Verifica se arquivo temporário existe
        if (!file_exists($file['tmp_name'])) {
            return null;
        }

        // Caminho absoluto
        $publicRoot = $this->resolvePublicRoot();
        $upload_dir = $publicRoot . '/' . self::USER_AVATAR_DIR;

        // Garante que o diretório existe
        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0755, true)) {
                return null;
            }
        }

        // Verifica permissões de escrita
        if (!is_writable($upload_dir)) {
            @chmod($upload_dir, 0777);
        }

        // Extensão do arquivo
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Valida extensão
        $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $ext_permitidas)) {
            return null;
        }

        // Nome único do arquivo
        $filename = 'avatar_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $filepath = $upload_dir . $filename;

        // Move o arquivo
        if (!@move_uploaded_file($file['tmp_name'], $filepath)) {
            return null;
        }

        // Ajusta permissões do arquivo
        @chmod($filepath, 0644);

        return self::USER_AVATAR_DIR . $filename;
    }

    private function resolvePublicRoot(): string
    {
        $projectPublic = rtrim(dirname(__DIR__, 3) . '/public', '/');
        if (is_dir($projectPublic)) {
            return $projectPublic;
        }

        return rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? $projectPublic), '/');
    }
}
