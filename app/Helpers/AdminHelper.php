<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\LivroOcorrenciaModel;
use App\Models\MembershipApplicationModel;
use App\Models\Post;

class AdminHelper {
    private static ?int $pendingUsersCache = null;
    private static ?int $pendingMembershipApplicationsCache = null;
    private static ?int $occurrencesInProgressCache = null;
    private static ?int $postsPendingReviewCache = null;

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

    private static function pendingMembershipApplicationsCount(): int {
        if (self::$pendingMembershipApplicationsCache === null) {
            try {
                $model = new MembershipApplicationModel();
                self::$pendingMembershipApplicationsCache = $model->contarPorStatus('pendente');
            } catch (\Throwable $th) {
                error_log('AdminHelper membership applications count error: ' . $th->getMessage());
                self::$pendingMembershipApplicationsCache = 0;
            }
        }

        return self::$pendingMembershipApplicationsCache;
    }

    private static function postsPendingReviewCount(): int {
        if (self::$postsPendingReviewCache === null) {
            try {
                $postModel = new Post();
                self::$postsPendingReviewCache = $postModel->contarPorStatus('pending');
            } catch (\Throwable $th) {
                error_log('AdminHelper posts pending count error: ' . $th->getMessage());
                self::$postsPendingReviewCache = 0;
            }
        }

        return self::$postsPendingReviewCache;
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
                'email' => $userEmail ?: (defined('INSTITUTIONAL_EMAIL_PRIMARY') ? INSTITUTIONAL_EMAIL_PRIMARY : 'aorern.comunicacao@gmail.com'),
                'initial' => strtoupper($initial ?: 'U'),
                'avatar' => $userAvatar
            ],
            'subRoute' => $subRoute,
            'notifications' => [
                'pending_users' => self::pendingUsersCount(),
                'pending_membership_applications' => self::pendingMembershipApplicationsCount(),
                'occurrences_in_progress' => self::occurrencesInProgressCount(),
                'posts_pending_review' => self::postsPendingReviewCount(),
            ],
        ];
    }
}
