<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
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
            $status !== '' ? $status : null
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
            'title' => 'Gerenciar Usuários',
            'q' => $q,
            'role' => $role,
            'status' => $status,
            'perPageOptions' => PaginationHelper::options($defaultPerPage),
            'perPageSelection' => $perPageSelection,
        ];

        $this->renderTwig('admin/usuarios/index', array_merge($dados, AdminHelper::getUserData('usuarios')));
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
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Role alterada com sucesso!'];
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
            $mail->Body = "Olá {$user['username']}, sua conta foi aprovada. Já pode acessar o portal.";

            $mail->send();
        } catch (Exception $e) {
            error_log('Erro ao enviar email de aprovação: ' . $mail->ErrorInfo);
        }
    }
}
