<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private $db;
    private string $table = 'users';

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Cria um novo usuário
     */
    public function criar(array $dados): bool {
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

    /**
     * Busca usuário por username
     */
    public function buscarPorUsername(string $username): ?array {
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
        $campos = [];
        $params = [':id' => $id];

        // Apenas campos permitidos
        $permitidos = ['username', 'email', 'avatar', 'role', 'ativo', 'status'];
        
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
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE username = ?");
        $stmt->execute([$username]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
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
     * Lista usuários com filtros
     */
    public function listarComFiltros(?string $busca = null, ?string $role = null, ?string $status = null): array {
        $sql = "SELECT id, username, email, avatar, role, ativo, status, ultimo_login, created_at, updated_at 
                FROM {$this->table} 
                WHERE 1=1";
        $params = [];

        if ($busca) {
            $sql .= " AND (username LIKE :busca OR email LIKE :busca)";
            $params[':busca'] = '%' . $busca . '%';
        }

        if ($role) {
            $sql .= " AND role = :role";
            $params[':role'] = $role;
        }

        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
