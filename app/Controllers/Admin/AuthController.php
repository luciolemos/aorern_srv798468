<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Helpers\CsrfHelper;
use App\Helpers\Validator;
use App\Models\User;

class AuthController extends Controller {

    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    private function renderAuth(string $template, array $data = []): void {
        $token = CsrfHelper::generateToken();
        $this->renderTwig($template, array_merge(['csrf_token' => $token], $data));
    }

    /**
     * Exibe formulário de login
     */
    public function index() {
        if (AuthMiddleware::isAuthenticated()) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit;
        }
        
        $this->renderAuth('admin/login');
    }

    /**
     * Processa login
     */
    public function login() {
        $request = Request::capture();
        
        // Se já está autenticado, redireciona
        if (AuthMiddleware::isAuthenticated()) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit;
        }
        
        if (!$request->isPost()) {
            $this->renderAuth('admin/login');
            return;
        }
        
        // Valida CSRF
        CsrfHelper::verifyOrDie();
        
        // Valida campos
        $validator = Validator::make($request->post(), [
            'username' => 'required',
            'password' => 'required'
        ]);
        
        if ($validator->fails()) {
            $errors = array_map(fn($e) => $e[0], $validator->errors());
            $this->renderAuth('admin/login', ['error' => implode(', ', $errors)]);
            return;
        }

        $username = $request->post('username');
        $password = $request->post('password');

        // Valida credenciais contra banco de dados
        $user = $this->userModel->validarLogin($username, $password);

        if ($user && $user['ativo']) {
            // Atualiza último login
            $this->userModel->registrarUltimoLogin($user['id']);
            
            // Cria sessão autenticada
            AuthMiddleware::login($user['id'], $user['role'], [
                'name'   => $user['username'],
                'email'  => $user['email'],
                'avatar' => $user['avatar']
            ]);
            
            // Dados para navbar e painel (consistente com Site/LoginController)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_avatar'] = $user['avatar'];
            
            $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . 'admin/dashboard';
            unset($_SESSION['redirect_after_login']);
            
            header('Location: ' . $redirect);
            exit;
        }
        
        $error = 'Usuário ou senha inválidos.';
        error_log("[AUTH] Login falhou para username: $username");
        $this->renderAuth('admin/login', compact('error'));
    }

    /**
     * Exibe formulário de registro
     */
    public function register() {
        if (AuthMiddleware::isAuthenticated()) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit;
        }

        $this->renderTwig('admin/register');
    }

    /**
     * Processa registro de novo usuário
     */
    public function store() {
        $request = Request::capture();

        // Verificar se já autenticado
        if (AuthMiddleware::isAuthenticated()) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit;
        }

        if (!$request->isPost()) {
            header('Location: ' . BASE_URL . 'admin/auth/register');
            exit;
        }

        // Valida CSRF
        CsrfHelper::verifyOrDie();

        // Valida dados
        $validator = Validator::make($request->post(), [
            'username'              => 'required|min:3|max:50',
            'email'                 => 'required|min:5|max:100',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            $errors = array_map(fn($e) => $e[0], $validator->errors());
            $_SESSION['toast'] = [
                'type'    => 'danger',
                'message' => 'Erro na validação: ' . implode(', ', $errors)
            ];
            $_SESSION['old_input'] = $request->post();
            header('Location: ' . BASE_URL . 'admin/auth/register');
            exit;
        }

        $username = $request->post('username');
        $email    = $request->post('email');
        $password = $request->post('password');
        $password_confirm = $request->post('password_confirmation');

        // Validações customizadas
        if ($password !== $password_confirm) {
            $_SESSION['toast'] = [
                'type'    => 'danger',
                'message' => 'As senhas não conferem!'
            ];
            $_SESSION['old_input'] = $request->post();
            header('Location: ' . BASE_URL . 'admin/auth/register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['toast'] = [
                'type'    => 'danger',
                'message' => 'Email inválido!'
            ];
            $_SESSION['old_input'] = $request->post();
            header('Location: ' . BASE_URL . 'admin/auth/register');
            exit;
        }

        if ($this->userModel->usernameExiste($username)) {
            $_SESSION['toast'] = [
                'type'    => 'danger',
                'message' => 'Usuário já existente!'
            ];
            $_SESSION['old_input'] = $request->post();
            header('Location: ' . BASE_URL . 'admin/auth/register');
            exit;
        }

        if ($this->userModel->emailExiste($email)) {
            $_SESSION['toast'] = [
                'type'    => 'danger',
                'message' => 'Email já cadastrado!'
            ];
            $_SESSION['old_input'] = $request->post();
            header('Location: ' . BASE_URL . 'admin/auth/register');
            exit;
        }

        // Hash da senha
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Processa avatar se enviado
        $avatar = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatar = $this->processarAvatar($_FILES['avatar']);
            // Se falhar no upload, continua sem avatar (não é obrigatório)
        }

        // Cria usuário
        $dados = [
            'username' => $username,
            'email'    => $email,
            'password' => $password_hash,
            'avatar'   => $avatar,
            'role'     => 'usuario'
        ];

        if ($this->userModel->criar($dados)) {
            $_SESSION['toast'] = [
                'type'    => 'success',
                'message' => 'Cadastro realizado com sucesso! Faça login para continuar.'
            ];
            header('Location: ' . BASE_URL . 'admin/auth');
            exit;
        }

        $_SESSION['toast'] = [
            'type'    => 'danger',
            'message' => 'Erro ao criar usuário. Tente novamente.'
        ];
        header('Location: ' . BASE_URL . 'admin/auth/register');
        exit;
    }

    /**
     * Processa upload de avatar
     */
    private function processarAvatar(array $file): ?string {
        try {
            // Validações básicas
            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                error_log('[AVATAR-REGISTER] tmp_name vazio');
                return null;
            }

            // Verifica erro de upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                error_log('[AVATAR-REGISTER] Erro de upload: ' . $file['error']);
                return null;
            }

            // Tamanho máximo: 50MB
            $max_size = 50 * 1024 * 1024;
            if ($file['size'] > $max_size) {
                error_log('[AVATAR-REGISTER] Arquivo muito grande: ' . ($file['size'] / 1024 / 1024) . 'MB');
                return null;
            }

            // Tipos permitidos (verificação mais robusta com finfo)
            $tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime, $tipos_permitidos)) {
                error_log('[AVATAR-REGISTER] MIME inválido: ' . $mime);
                return null;
            }

            // Verifica se arquivo temporário existe
            if (!file_exists($file['tmp_name'])) {
                error_log('[AVATAR-REGISTER] Arquivo temporário não encontrado: ' . $file['tmp_name']);
                return null;
            }

            // Caminho absoluto do diretório
            $base_path = dirname(dirname(__DIR__));
            $upload_dir = $base_path . '/public/assets/avatars/';

            // Garante que o diretório existe
            if (!is_dir($upload_dir)) {
                if (!@mkdir($upload_dir, 0755, true)) {
                    error_log('[AVATAR-REGISTER] Falha ao criar diretório: ' . $upload_dir);
                    return null;
                }
            }

            // Verifica permissões de escrita
            if (!is_writable($upload_dir)) {
                @chmod($upload_dir, 0755);
                if (!is_writable($upload_dir)) {
                    error_log('[AVATAR-REGISTER] Diretório não gravável');
                    return null;
                }
            }

            // Extensão do arquivo
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Valida extensão
            $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $ext_permitidas)) {
                error_log('[AVATAR-REGISTER] Extensão não permitida: ' . $ext);
                return null;
            }

            // Nome único do arquivo
            $filename = 'avatar_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $filepath = $upload_dir . $filename;

            // Move arquivo
            if (!@move_uploaded_file($file['tmp_name'], $filepath)) {
                error_log('[AVATAR-REGISTER] Falha ao mover arquivo');
                return null;
            }

            // Ajusta permissões do arquivo
            @chmod($filepath, 0644);

            error_log('[AVATAR-REGISTER] Upload bem-sucedido: ' . $filename);
            return 'assets/avatars/' . $filename;

        } catch (\Exception $e) {
            error_log('[AVATAR-REGISTER] Exceção: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Logout
     */
    public function logout() {
        AuthMiddleware::logout();
        header('Location: ' . BASE_URL);
        exit;
    }
}
