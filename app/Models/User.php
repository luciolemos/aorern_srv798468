<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class User {
    public const USERNAME_REGEX = '/^[a-z]+[0-9]{4}$/';
    private $db;
    private string $table = 'users';

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Cria um novo usuário
     */
    public function criar(array $dados): bool {
        if (isset($dados['username'])) {
            $dados['username'] = self::normalizeUsername((string) $dados['username']);
        }

        $sql = "INSERT INTO {$this->table} (
            username, email, password, avatar, role, ativo, status
        ) VALUES (
            :username, :email, :password, :avatar, :role, :ativo, :status
        )";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username' => $dados['username'],
            ':email'    => $dados['email'],
            ':password' => $dados['password'],  // Deve ser hash
            ':avatar'   => $dados['avatar'] ?? null,
            ':role'     => $dados['role'] ?? 'usuario',
            ':ativo'    => $dados['ativo'] ?? 0,
            ':status'   => $dados['status'] ?? 'pendente'
        ]);
    }

    public function criarERetornarId(array $dados): ?int
    {
        if (!$this->criar($dados)) {
            return null;
        }

        return (int) $this->db->lastInsertId();
    }

    /**
     * Busca usuário por username
     */
    public function buscarPorUsername(string $username): ?array {
        $username = self::normalizeUsername($username);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca usuário por email
     */
    public function buscarPorEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca usuário por ID
     */
    public function buscarPorId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Valida credenciais (username + password)
     */
    public function validarLogin(string $username, string $password): ?array {
        $user = $this->buscarPorUsername($username);
        
        if (!$user) {
            return null;
        }

        // Verifica hash de senha
        if (password_verify($password, $user['password'])) {
            // Remove a senha da resposta por segurança
            unset($user['password']);
            return $user;
        }

        return null;
    }

    /**
     * Atualiza dados do usuário
     */
    public function atualizar(int $id, array $dados): bool {
        if (array_key_exists('username', $dados)) {
            $dados['username'] = self::normalizeUsername((string) $dados['username']);
        }

        $campos = [];
        $params = [':id' => $id];

        // Apenas campos permitidos
        $permitidos = ['username', 'email', 'password', 'avatar', 'role', 'ativo', 'status'];
        
        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $dados)) {
                $campos[] = "{$campo} = :{$campo}";
                $params[":{$campo}"] = $dados[$campo];
            }
        }

        if (empty($campos)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Atualiza apenas a senha
     */
    public function atualizarSenha(int $id, string $senha_hash): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET password = :password WHERE id = :id");
        return $stmt->execute([
            ':password' => $senha_hash,
            ':id'       => $id
        ]);
    }

    /**
     * Registra o último login
     */
    public function registrarUltimoLogin(int $id): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET ultimo_login = NOW() WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Verifica se username já existe
     */
    public function usernameExiste(string $username): bool {
        $username = self::normalizeUsername($username);
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE username = ?");
        $stmt->execute([$username]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    public static function normalizeUsername(string $username): string
    {
        $value = trim($username);
        if ($value === '') {
            return '';
        }

        $value = strtolower($value);
        return preg_replace('/\s+/', '', $value) ?? '';
    }

    public static function isValidUsernameFormat(string $username): bool
    {
        $normalized = self::normalizeUsername($username);
        return (bool) preg_match(self::USERNAME_REGEX, $normalized);
    }

    /**
     * Verifica se email já existe
     */
    public function emailExiste(string $email): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    /**
     * Deleta usuário (soft delete - apenas marca como inativo)
     */
    public function deletar(int $id): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET ativo = 0 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Lista todos os usuários ativos
     */
    public function listar(): array {
        $stmt = $this->db->query("SELECT id, username, email, avatar, role, ativo, status, ultimo_login, created_at, updated_at FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contagem de usuários
     */
    public function contar(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Contagem de usuários por status
     */
    public function contarPorStatus(string $status): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE status = :status");
        $stmt->execute([':status' => $status]);
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Lista usuários com filtros
     */
    public function listarComFiltros(?string $busca = null, ?string $role = null, ?string $status = null): array {
        $result = $this->paginarComFiltros(1, null, $busca, $role, $status);
        return $result['data'];
    }

    public function paginarComFiltros(
        int $page,
        ?int $perPage,
        ?string $busca = null,
        ?string $role = null,
        ?string $status = null,
        bool $administrativeOnly = false
    ): array {
        $conditions = [];
        $params = [];

        if ($busca) {
            $conditions[] = '(u.username LIKE :busca OR u.email LIKE :busca)';
            $params[':busca'] = '%' . $busca . '%';
        }

        if ($role) {
            $conditions[] = 'u.role = :role';
            $params[':role'] = $role;
        }

        if ($status) {
            $conditions[] = 'u.status = :status';
            $params[':status'] = $status;
        }

        if ($administrativeOnly) {
            $conditions[] = "u.role IN ('admin', 'gerente', 'operador')";
        }

        $where = $conditions ? implode(' AND ', $conditions) : '';

        return Paginator::paginate(
            $this->db,
            'u.id, u.username, u.email, u.avatar, u.role, u.ativo, u.status, u.ultimo_login, u.created_at, u.updated_at, p.nome AS associado_nome, d.cargo_funcao_diretoria',
            "FROM {$this->table} u
             LEFT JOIN pessoal p ON p.user_id = u.id
             LEFT JOIN (
                SELECT p.user_id,
                       GROUP_CONCAT(DISTINCT COALESCE(f.nome, bm.cargo) ORDER BY bm.ordem ASC, bm.id ASC SEPARATOR ' | ') AS cargo_funcao_diretoria
                FROM board_memberships bm
                INNER JOIN pessoal p ON p.id = bm.pessoal_id
                LEFT JOIN funcoes f ON f.id = bm.funcao_id
                WHERE p.user_id IS NOT NULL
                  AND bm.is_active = 1
                GROUP BY p.user_id
             ) d ON d.user_id = u.id",
            $where,
            'u.created_at DESC',
            $params,
            $page,
            $perPage
        );
    }
}
