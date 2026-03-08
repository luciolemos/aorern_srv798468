<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class BoardMembershipModel
{
    private PDO $db;
    private string $table = 'board_memberships';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    private function selectBase(): string
    {
        return "SELECT bm.*, bt.nome AS term_nome, p.nome AS associado_nome, p.user_id AS associado_user_id,
                p.foto AS associado_foto, u.avatar AS associado_user_avatar,
                f.nome AS funcao_nome
                FROM {$this->table} bm
                LEFT JOIN board_terms bt ON bt.id = bm.term_id
                LEFT JOIN pessoal p ON p.id = bm.pessoal_id
                LEFT JOIN users u ON u.id = p.user_id
                LEFT JOIN funcoes f ON f.id = bm.funcao_id";
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare($this->selectBase() . " WHERE bm.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function salvar(array $dados): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (
                term_id, pessoal_id, funcao_id, cargo, grupo, ordem, is_active, access_role, observacoes
             ) VALUES (
                :term_id, :pessoal_id, :funcao_id, :cargo, :grupo, :ordem, :is_active, :access_role, :observacoes
             )"
        );
        return $stmt->execute($dados);
    }

    public function atualizar(int $id, array $dados): bool
    {
        $dados['id'] = $id;
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET
                term_id = :term_id,
                pessoal_id = :pessoal_id,
                funcao_id = :funcao_id,
                cargo = :cargo,
                grupo = :grupo,
                ordem = :ordem,
                is_active = :is_active,
                access_role = :access_role,
                observacoes = :observacoes,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :id"
        );
        return $stmt->execute($dados);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function paginar(int $page = 1, ?int $perPage = 10, array $filters = []): array
    {
        $select = "bm.*, bt.nome AS term_nome, p.nome AS associado_nome, p.foto AS associado_foto, u.avatar AS associado_user_avatar, f.nome AS funcao_nome";
        $from = "FROM {$this->table} bm
                 LEFT JOIN board_terms bt ON bt.id = bm.term_id
                 LEFT JOIN pessoal p ON p.id = bm.pessoal_id
                 LEFT JOIN users u ON u.id = p.user_id
                 LEFT JOIN funcoes f ON f.id = bm.funcao_id";
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(bm.cargo LIKE :q OR bm.grupo LIKE :q OR p.nome LIKE :q OR f.nome LIKE :q OR bt.nome LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['term_id'])) {
            $where[] = 'bm.term_id = :term_id';
            $params[':term_id'] = (int) $filters['term_id'];
        }

        if ($filters['is_active'] !== null && $filters['is_active'] !== '') {
            $where[] = 'bm.is_active = :is_active';
            $params[':is_active'] = (int) $filters['is_active'];
        }

        return Paginator::paginate(
            $this->db,
            $select,
            $from,
            implode(' AND ', $where),
            'bm.is_active DESC, bm.ordem ASC, bm.id DESC',
            $params,
            $page,
            $perPage
        );
    }

    public function listarPerfisAtivosPorPessoal(int $pessoalId): array
    {
        $stmt = $this->db->prepare(
            "SELECT access_role
             FROM {$this->table}
             WHERE pessoal_id = :pessoal_id
               AND is_active = 1
               AND access_role IS NOT NULL"
        );
        $stmt->execute([':pessoal_id' => $pessoalId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function existeVinculoPorPessoal(int $pessoalId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1
             FROM {$this->table}
             WHERE pessoal_id = :pessoal_id
             LIMIT 1"
        );
        $stmt->execute([':pessoal_id' => $pessoalId]);
        return (bool) $stmt->fetchColumn();
    }

    public function existeFuncaoNoMandato(int $termId, int $funcaoId, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1
                FROM {$this->table}
                WHERE term_id = :term_id
                  AND funcao_id = :funcao_id";
        $params = [
            ':term_id' => $termId,
            ':funcao_id' => $funcaoId,
        ];

        if ($excludeId !== null) {
            $sql .= " AND id <> :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function existeCargoNoMandato(int $termId, string $cargo, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1
                FROM {$this->table}
                WHERE term_id = :term_id
                  AND LOWER(TRIM(cargo)) = LOWER(TRIM(:cargo))";
        $params = [
            ':term_id' => $termId,
            ':cargo' => $cargo,
        ];

        if ($excludeId !== null) {
            $sql .= " AND id <> :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }
}
