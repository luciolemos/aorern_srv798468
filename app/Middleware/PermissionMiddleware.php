<?php

namespace App\Middleware;

use App\Config\Permissions;

/**
 * PermissionMiddleware - Valida permissões do usuário
 * 
 * Uso em controllers:
 * PermissionMiddleware::authorize('posts:create');
 */
class PermissionMiddleware
{
    /**
     * Verifica se o usuário tem permissão, senão redireciona
     * 
     * @param string $permission Permissão no formato 'recurso:acao'
     * @param string|null $redirectTo URL para redirecionar se negar (default: /login/admin)
     * @throws \Exception Se não autenticado
     */
    public static function authorize(string $permission, string $redirectTo = null): void
    {
        // Primeiro verifica autenticação
        if (!AuthMiddleware::isAuthenticated()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? BASE_URL;
            header('Location: ' . BASE_URL . 'login/admin');
            exit;
        }

        // Verifica permissão
        $userRole = $_SESSION['user_role'] ?? 'usuario';
        if (!Permissions::has($userRole, $permission)) {
            // Log do acesso negado
            error_log(sprintf(
                "[PERMISSION DENIED] User: %s (role: %s) tried to access: %s at %s",
                $_SESSION['user_name'] ?? 'Unknown',
                $userRole,
                $permission,
                $_SERVER['REQUEST_URI']
            ));

            // Redireciona ou mostra erro
            $redirectTo = $redirectTo ?? BASE_URL . 'admin/dashboard';
            http_response_code(403);
            header('X-Permission-Denied: ' . $permission);
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    /**
     * Verifica se tem permissão SEM sair da execução
     * Útil para lógica condicional
     * 
     * @param string $permission
     * @return bool
     */
    public static function can(string $permission): bool
    {
        if (!AuthMiddleware::isAuthenticated()) {
            return false;
        }

        $userRole = $_SESSION['user_role'] ?? 'usuario';
        return Permissions::has($userRole, $permission);
    }

    /**
     * Retorna todas as permissões do usuário atual
     * 
     * @return array
     */
    public static function getMyPermissions(): array
    {
        if (!AuthMiddleware::isAuthenticated()) {
            return [];
        }

        $userRole = $_SESSION['user_role'] ?? 'usuario';
        return Permissions::getByRole($userRole);
    }

    /**
     * Verifica se o usuário é dono de um recurso
     * Útil para permitir que usuários comuns editem apenas seus próprios posts
     * 
     * @param int $resourceUserId ID do usuário que criou o recurso
     * @return bool
     */
    public static function isOwner(int $resourceUserId): bool
    {
        if (!AuthMiddleware::isAuthenticated()) {
            return false;
        }

        $currentUserId = $_SESSION['user_id'] ?? null;
        return $currentUserId === $resourceUserId;
    }

    /**
     * Checa permissão OU propriedade
     * Útil para editar: admin pode editar tudo, usuários comuns só seus próprios
     * 
     * @param string $permission Ex: 'posts:edit'
     * @param int $resourceUserId ID do criador do recurso
     * @return bool
     */
    public static function canOrOwns(string $permission, int $resourceUserId): bool
    {
        return self::can($permission) || self::isOwner($resourceUserId);
    }
}
