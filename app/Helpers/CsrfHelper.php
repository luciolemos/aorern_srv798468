<?php

namespace App\Helpers;

class CsrfHelper
{
    /**
     * Gera um token CSRF e o armazena na sessão
     * 
     * @return string O token CSRF gerado
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();

        return $token;
    }

    /**
     * Valida o token CSRF enviado
     * 
     * @param string|null $token Token a ser validado
     * @param int $timeout Tempo de expiração em segundos (padrão: 3600 = 1 hora)
     * @return bool True se válido, False caso contrário
     */
    public static function validateToken(?string $token, int $timeout = 3600): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica se o token existe na sessão
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }

        // Verifica se o token expirou
        if (time() - $_SESSION['csrf_token_time'] > $timeout) {
            self::destroyToken();
            return false;
        }

        // Compara os tokens de forma segura
        if (!hash_equals($_SESSION['csrf_token'], $token ?? '')) {
            return false;
        }

        return true;
    }

    /**
     * Gera o campo HTML hidden com o token CSRF
     * 
     * @return string HTML do campo hidden
     */
    public static function inputField(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Gera meta tag para uso em AJAX
     * 
     * @return string HTML da meta tag
     */
    public static function metaTag(): string
    {
        $token = self::generateToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Destrói o token CSRF da sessão
     */
    public static function destroyToken(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
    }

    /**
     * Regenera o token CSRF (útil após operações de segurança)
     * 
     * @return string O novo token CSRF
     */
    public static function regenerateToken(): string
    {
        self::destroyToken();
        return self::generateToken();
    }

    /**
     * Verifica o token do POST e retorna erro 403 se inválido
     */
    public static function verifyOrDie(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!self::validateToken($token)) {
            http_response_code(403);
            die('❌ Erro 403: Token CSRF inválido ou expirado.');
        }
    }
}
