<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
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
        $q = $_GET['q'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $usuarios = $this->userModel->listarComFiltros($q, $role, $status);
        
        $dados = [
            'usuarios' => $usuarios,
            'total' => count($usuarios),
            'title' => 'Gerenciar Usuários',
            'q' => $q,
            'role' => $role,
            'status' => $status
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
