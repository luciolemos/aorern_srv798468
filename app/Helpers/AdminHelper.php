<?php

namespace App\Helpers;

class AdminHelper {
    /**
     * Retorna dados do usuário para templates admin
     */
    public static function getUserData(string $subRoute = ''): array {
        $userName = $_SESSION['user_name'] ?? 'Usuário';
        $userEmail = $_SESSION['user_email'] ?? '';
        $userAvatar = $_SESSION['user_avatar'] ?? '';
        $initial = function_exists('mb_substr') ? mb_substr($userName, 0, 1, 'UTF-8') : substr($userName, 0, 1);
        
        return [
            'user' => [
                'name' => $userName,
                'email' => $userEmail ?: 'admin@cbmrn.gov.br',
                'initial' => strtoupper($initial ?: 'U'),
                'avatar' => $userAvatar
            ],
            'subRoute' => $subRoute
        ];
    }
}
