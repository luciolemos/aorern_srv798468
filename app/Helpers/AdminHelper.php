<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\LivroOcorrenciaModel;

class AdminHelper {
    private static ?int $pendingUsersCache = null;
    private static ?int $occurrencesInProgressCache = null;

    private static function pendingUsersCount(): int {
        if (self::$pendingUsersCache === null) {
            try {
                $userModel = new User();
                self::$pendingUsersCache = $userModel->contarPorStatus('pendente');
            } catch (\Throwable $th) {
                error_log('AdminHelper pending count error: ' . $th->getMessage());
                self::$pendingUsersCache = 0;
            }
        }

        return self::$pendingUsersCache;
    }

    private static function occurrencesInProgressCount(): int {
        if (self::$occurrencesInProgressCache === null) {
            try {
                $model = new LivroOcorrenciaModel();
                self::$occurrencesInProgressCache = $model->contarPorStatus('em_andamento');
            } catch (\Throwable $th) {
                error_log('AdminHelper occurrences count error: ' . $th->getMessage());
                self::$occurrencesInProgressCache = 0;
            }
        }

        return self::$occurrencesInProgressCache;
    }
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
            'subRoute' => $subRoute,
            'notifications' => [
                'pending_users' => self::pendingUsersCount(),
                'occurrences_in_progress' => self::occurrencesInProgressCount(),
            ],
        ];
    }
}
