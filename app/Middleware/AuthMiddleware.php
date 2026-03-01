<?php

namespace App\Middleware;

use App\Core\TwigEngine;

class AuthMiddleware
{
    /**
     * Verifica se o usuário está autenticado
     * 
     * @return bool True se autenticado
     */
    public static function isAuthenticated(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica se tem ID de usuário (campo padrão em toda autenticação)
        $hasId = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
        
        return $hasId;
    }

    /**
     * Verifica se o usuário é admin
     * 
     * @return bool True se for admin
     */
    public static function isAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Protege rota admin - redireciona para login se não autenticado
     */
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? BASE_URL . 'admin/dashboard';
            header('Location: ' . BASE_URL . 'login/admin');
            exit;
        }
    }

    /**
     * Protege rota admin - verifica se é admin
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();

        if (!self::isAdmin()) {
            http_response_code(403);
            self::renderForbiddenPage();
        }
    }

    /**
     * Verifica se a sessão expirou por inatividade
     * 
     * @param int $timeout Tempo em segundos (padrão: 1800 = 30 minutos)
     */
    public static function checkTimeout(int $timeout = 1800): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > $timeout) {
                self::logout();
                $_SESSION['timeout_message'] = 'Sua sessão expirou por inatividade.';
                header('Location: ' . BASE_URL . 'admin/auth');
                exit;
            }
        }

        $_SESSION['last_activity'] = time();
    }

    /**
     * Realiza login do usuário
     * 
     * @param int $userId ID do usuário
     * @param string $role Papel do usuário (admin, user, etc)
     * @param array $userData Dados adicionais do usuário
     */
    public static function login(int $userId, string $role = 'user', array $userData = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenera o ID da sessão para prevenir session fixation (pode causar problemas com cookies)
        // session_regenerate_id(true);

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
        $_SESSION['last_activity'] = time();
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        // Armazena dados adicionais
        foreach ($userData as $key => $value) {
            $_SESSION['user_' . $key] = $value;
        }
    }

    /**
     * Realiza logout do usuário
     */
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Limpa todas as variáveis de sessão
        $_SESSION = [];

        // Destrói o cookie de sessão
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/', '', false, true);
        }

        // Destrói a sessão
        session_destroy();
        
        // Inicia nova sessão vazia para evitar problemas de sessão
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Obtém o ID do usuário logado
     * 
     * @return int|null ID do usuário ou null se não autenticado
     */
    public static function userId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Obtém dados do usuário da sessão
     * 
     * @param string $key Chave do dado (sem prefixo 'user_')
     * @return mixed Valor ou null
     */
    public static function getUserData(string $key)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['user_' . $key] ?? null;
    }

    /**
     * Verifica se a requisição vem do mesmo IP/User-Agent do login
     * Proteção contra session hijacking
     * 
     * @return bool True se válido
     */
    public static function validateSession(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!self::isAuthenticated()) {
            return false;
        }

        $currentIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $sessionIp = $_SESSION['user_ip'] ?? null;
        $sessionAgent = $_SESSION['user_agent'] ?? null;

        // Validação mais flexível para User-Agent (pode variar levemente)
        if ($currentIp !== $sessionIp || $currentAgent !== $sessionAgent) {
            self::logout();
            return false;
        }

        return true;
    }

    private static function renderForbiddenPage(): void
    {
        try {
            echo TwigEngine::getInstance()->render(self::isAdminRequest() ? 'admin/pages/403' : 'site/pages/403');
        } catch (\Throwable $exception) {
            die('Acesso negado.');
        }

        exit;
    }

    private static function isAdminRequest(): bool
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        return preg_match('#/(cbmrn/)?admin(?:/|$)#', (string) $path) === 1;
    }
}
