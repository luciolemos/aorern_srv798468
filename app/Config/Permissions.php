<?php

namespace App\Config;

/**
 * Definição de Permissões por Role (ACL - Access Control List)
 * 
 * Estrutura:
 * 'recurso:acao' => ['role1', 'role2', ...]
 */
class Permissions
{
    public const PERMISSIONS = [
        // ====== POSTS ======
        'posts:list' => ['admin', 'gerente', 'operador', 'usuario'],
        'posts:create' => ['admin', 'gerente'],
        'posts:edit' => ['admin', 'gerente'], // pode editar próprios posts ou qualquer um se admin
        'posts:delete' => ['admin'],
        'posts:publish' => ['admin', 'gerente'],
        'posts:unpublish' => ['admin', 'gerente'],

        // ====== CATEGORIAS DE POSTS ======
        'post_categories:list' => ['admin', 'gerente', 'operador', 'usuario'],
        'post_categories:create' => ['admin', 'gerente'],
        'post_categories:edit' => ['admin'],
        'post_categories:delete' => ['admin'],

        // ====== USUÁRIOS ======
        'users:list' => ['admin', 'gerente'],
        'users:create' => ['admin'],
        'users:edit' => ['admin'],
        'users:delete' => ['admin'],
        'users:approve' => ['admin'],
        'users:block' => ['admin'],

        // ====== DASHBOARD ======
        'dashboard:view' => ['admin', 'gerente', 'operador', 'usuario'],
        'dashboard:stats' => ['admin', 'gerente'],

        // ====== EQUIPAMENTOS ======
        'equipamentos:list' => ['admin', 'gerente', 'operador'],
        'equipamentos:create' => ['admin', 'gerente'],
        'equipamentos:edit' => ['admin', 'gerente'],
        'equipamentos:delete' => ['admin'],

        // ====== PESSOAL ======
        'pessoal:list' => ['admin', 'gerente', 'operador'],
        'pessoal:create' => ['admin', 'gerente'],
        'pessoal:edit' => ['admin', 'gerente'],
        'pessoal:delete' => ['admin'],

        // ====== FUNÇÕES ======
        'funcoes:list' => ['admin', 'gerente', 'operador'],
        'funcoes:create' => ['admin'],
        'funcoes:edit' => ['admin'],
        'funcoes:delete' => ['admin'],

        // ====== OBRAS ======
        'obras:list' => ['admin', 'gerente', 'operador'],
        'obras:create' => ['admin', 'gerente'],
        'obras:edit' => ['admin', 'gerente'],
        'obras:delete' => ['admin'],

        // ====== CATEGORIAS EQUIPAMENTOS ======
        'categorias:list' => ['admin', 'gerente', 'operador'],
        'categorias:create' => ['admin'],
        'categorias:edit' => ['admin'],
        'categorias:delete' => ['admin'],

        // ====== CONFIGURAÇÕES ======
        'configuracao:view' => ['admin'],
        'configuracao:edit' => ['admin'],

        // ====== DOCS ======
        'docs:view' => ['admin', 'gerente', 'operador'],

        // ====== STATUS ======
        'status:view' => ['admin', 'gerente'],
    ];

    /**
     * Verifica se um role tem permissão para uma ação
     * 
     * @param string $role O role do usuário (admin, gerente, operador, usuario)
     * @param string $permission A permissão no formato 'recurso:acao'
     * @return bool True se tem permissão
     */
    public static function has(string $role, string $permission): bool
    {
        if (!isset(self::PERMISSIONS[$permission])) {
            return false;
        }

        return in_array($role, self::PERMISSIONS[$permission], true);
    }

    /**
     * Retorna todas as permissões de um role
     * 
     * @param string $role
     * @return array
     */
    public static function getByRole(string $role): array
    {
        $permissions = [];
        foreach (self::PERMISSIONS as $permission => $roles) {
            if (in_array($role, $roles, true)) {
                $permissions[] = $permission;
            }
        }
        return $permissions;
    }

    /**
     * Retorna descrição amigável de um role
     * 
     * @param string $role
     * @return string
     */
    public static function getRoleLabel(string $role): string
    {
        $labels = [
            'admin' => 'Administrador',
            'gerente' => 'Gerente',
            'operador' => 'Operador',
            'usuario' => 'Usuário',
        ];
        return $labels[$role] ?? ucfirst($role);
    }
}
