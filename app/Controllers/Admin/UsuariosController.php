<?php

namespace App\Controllers\Admin;

use App\Config\Permissions;
use App\Core\Controller;
use App\Helpers\EmailTemplate;
use App\Core\Request;
use App\Helpers\PaginationHelper;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\User;
use App\Helpers\AdminHelper;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UsuariosController extends Controller {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
        AuthMiddleware::requireAuth();
    }

    /**
     * Lista todos os usuários cadastrados
     */
    public function index(): void {
        $request = Request::capture();
        $page = max(1, (int) $request->query('page', 1));
        $q = trim($request->query('q', ''));
        $role = $request->query('role', '');
        if ($role === 'usuario') {
            $role = '';
        }
        $status = $request->query('status', '');

        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);
        $perPageQueryValue = ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null;

        $result = $this->userModel->paginarComFiltros(
            $page,
            $perPage,
            $q !== '' ? $q : null,
            $role !== '' ? $role : null,
            $status !== '' ? $status : null,
            true
        );

        $usuarios = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/usuarios',
            'query' => array_filter([
                'q' => $q,
                'role' => $role,
                'status' => $status,
                'per_page' => $perPageQueryValue,
            ], fn ($value) => $value !== null && $value !== ''),
        ]);

        $dados = [
            'usuarios' => $usuarios,
            'pagination' => $pagination,
            'title' => 'Usuários do Painel',
            'q' => $q,
            'role' => $role,
            'status' => $status,
            'roleLabels' => $this->getRoleLabels(),
            'perPageOptions' => PaginationHelper::options($defaultPerPage),
            'perPageSelection' => $perPageSelection,
        ];

        $this->renderTwig('admin/usuarios/index', array_merge($dados, AdminHelper::getUserData('usuarios')));
    }

    /**
     * Exibe detalhes de um usuário em modo somente leitura
     */
    public function visualizar($id = null): void
    {
        PermissionMiddleware::authorize('users:list');

        $userId = (int) ($id ?? 0);
        if ($userId <= 0) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Usuário inválido.'];
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $usuario = $this->userModel->buscarPorId($userId);
        if (!$usuario) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Usuário não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $this->renderTwig('admin/usuarios/visualizar', array_merge(
            [
                'title' => 'Visualizar Usuário',
                'usuario' => $usuario,
                'roleLabels' => $this->getRoleLabels(),
            ],
            AdminHelper::getUserData('usuarios')
        ));
    }

    /**
     * Exibe formulário de edição de usuário
     */
    public function editar($id = null): void
    {
        PermissionMiddleware::authorize('users:edit');

        $userId = (int) ($id ?? 0);
        if ($userId <= 0) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Usuário inválido.'];
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $usuario = $this->userModel->buscarPorId($userId);
        if (!$usuario) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Usuário não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $this->renderTwig('admin/usuarios/editar', array_merge(
            [
                'title' => 'Editar Usuário',
                'usuario' => $usuario,
                'roleLabels' => $this->getRoleLabels(),
            ],
            AdminHelper::getUserData('usuarios')
        ));
    }

    /**
     * Atualiza dados de um usuário do painel
     */
    public function atualizar(): void
    {
        PermissionMiddleware::authorize('users:edit');
        $request = Request::capture();

        if (!$request->isPost()) {
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $id = (int) $request->post('id', 0);
        $username = trim((string) $request->post('username', ''));
        $email = trim((string) $request->post('email', ''));
        $role = trim((string) $request->post('role', ''));
        $status = trim((string) $request->post('status', ''));

        if ($id <= 0) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Usuário inválido.'];
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $usuarioAtual = $this->userModel->buscarPorId($id);
        if (!$usuarioAtual) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Usuário não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $rolesValidas = ['admin', 'gerente', 'operador', 'usuario'];
        $statusValidos = ['ativo', 'pendente', 'bloqueado'];

        if ($username === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Preencha usuário e e-mail válidos.'];
            header('Location: ' . BASE_URL . 'admin/usuarios/editar/' . $id);
            exit;
        }

        if (!in_array($role, $rolesValidas, true)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Perfil inválido.'];
            header('Location: ' . BASE_URL . 'admin/usuarios/editar/' . $id);
            exit;
        }

        if (!in_array($status, $statusValidos, true)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Status inválido.'];
            header('Location: ' . BASE_URL . 'admin/usuarios/editar/' . $id);
            exit;
        }

        $conflitoUsername = $this->userModel->buscarPorUsername($username);
        if ($conflitoUsername && (int) $conflitoUsername['id'] !== $id) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Nome de usuário já está em uso.'];
            header('Location: ' . BASE_URL . 'admin/usuarios/editar/' . $id);
            exit;
        }

        $conflitoEmail = $this->userModel->buscarPorEmail($email);
        if ($conflitoEmail && (int) $conflitoEmail['id'] !== $id) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'E-mail já está em uso.'];
            header('Location: ' . BASE_URL . 'admin/usuarios/editar/' . $id);
            exit;
        }

        $ativo = $status === 'ativo' ? 1 : 0;
        $ok = $this->userModel->atualizar($id, [
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'status' => $status,
            'ativo' => $ativo,
        ]);

        if (!$ok) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não foi possível atualizar o usuário.'];
            header('Location: ' . BASE_URL . 'admin/usuarios/editar/' . $id);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Usuário atualizado com sucesso.'];
        header('Location: ' . BASE_URL . 'admin/usuarios/visualizar/' . $id);
        exit;
    }

    /**
     * Ativa um usuário (ativo = 1)
     */
    public function ativar(): void {
        PermissionMiddleware::authorize('users:approve');
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'ID inválido.'];
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $user = $this->userModel->buscarPorId((int)$id);

        if (!$user) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Usuário não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $ok = $this->userModel->atualizar((int)$id, ['ativo' => 1, 'status' => 'ativo']);
        if (!$ok) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro ao ativar usuário.'];
        } else {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Usuário ativado com sucesso!'];
            $this->enviarEmailAprovacao($user);
        }

        header('Location: ' . BASE_URL . 'admin/usuarios');
        exit;
    }

    /**
     * Desativa um usuário (ativo = 0)
     */
    public function desativar(): void {
        PermissionMiddleware::authorize('users:block');
        $id = $_POST['id'] ?? null;
        
        if (!$id || !$this->userModel->atualizar((int)$id, ['ativo' => 0, 'status' => 'bloqueado'])) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro ao bloquear usuário.'];
        } else {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Usuário bloqueado.'];
        }

        header('Location: ' . BASE_URL . 'admin/usuarios');
        exit;
    }

    /**
     * Altera role de um usuário
     */
    public function alterarRole(): void {
        PermissionMiddleware::authorize('users:edit');
        $id = $_POST['id'] ?? null;
        $role = $_POST['role'] ?? null;
        
        $rolesValidas = ['admin', 'gerente', 'operador', 'usuario'];
        
        if (!$id || !$role || !in_array($role, $rolesValidas, true)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Dados inválidos.'];
        } elseif (!$this->userModel->atualizar((int)$id, ['role' => $role])) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro ao alterar role.'];
        } else {
            $mensagem = $role === 'usuario'
                ? 'Acesso ao painel removido. O usuário voltou para a área do associado.'
                : 'Acesso ao painel concedido com o perfil institucional selecionado.';
            $_SESSION['toast'] = ['type' => 'success', 'message' => $mensagem];
        }

        header('Location: ' . BASE_URL . 'admin/usuarios');
        exit;
    }

    /**
     * Deleta (soft delete) um usuário
     */
    public function deletar(): void {
        PermissionMiddleware::authorize('users:delete');
        $id = $_POST['id'] ?? null;
        
        if (!$id || !$this->userModel->atualizar((int)$id, ['ativo' => 0, 'status' => 'bloqueado'])) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro ao bloquear usuário.'];
        } else {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Usuário bloqueado.'];
        }

        header('Location: ' . BASE_URL . 'admin/usuarios');
        exit;
    }

    private function enviarEmailAprovacao(array $user): void {
        if (empty($user['email'])) {
            return;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            $mail->Timeout = 3; // evita travar a resposta
            $mail->SMTPKeepAlive = false;

            $mail->CharSet = 'UTF-8';
            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress($user['email'], $user['username'] ?? 'Usuário');

            $mail->Subject = 'Conta aprovada no portal';
            $mail->isHTML(true);
            $mail->Body = EmailTemplate::render(
                'Conta aprovada no portal',
                'Seu acesso ao portal da AORERN foi liberado.',
                sprintf(
                    '<p>Olá %s, sua conta foi aprovada.</p><p>Você já pode acessar o portal administrativo com seu e-mail e sua senha cadastrada.</p>',
                    htmlspecialchars((string) ($user['username'] ?? 'Usuário'), ENT_QUOTES, 'UTF-8')
                )
            );
            $mail->AltBody = "Olá {$user['username']}, sua conta foi aprovada. Já pode acessar o portal.";

            $mail->send();
        } catch (Exception $e) {
            error_log('Erro ao enviar email de aprovação: ' . $mail->ErrorInfo);
        }
    }

    private function getRoleLabels(): array
    {
        return [
            'admin' => Permissions::getRoleLabel('admin'),
            'gerente' => Permissions::getRoleLabel('gerente'),
            'operador' => Permissions::getRoleLabel('operador'),
            'usuario' => Permissions::getRoleLabel('usuario'),
        ];
    }
}
