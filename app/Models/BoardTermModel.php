<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class BoardTermModel
{
    private PDO $db;
    private string $table = 'board_terms';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function statusOptions(): array
    {
        return ['planned', 'active', 'archived'];
    }

    public function listar(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY data_inicio DESC, id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function salvar(array $dados): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (nome, status, data_inicio, data_fim, observacoes)
             VALUES (:nome, :status, :data_inicio, :data_fim, :observacoes)"
        );
        return $stmt->execute($dados);
    }

    public function atualizar(int $id, array $dados): bool
    {
        $dados['id'] = $id;
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET
                nome = :nome,
                status = :status,
                data_inicio = :data_inicio,
                data_fim = :data_fim,
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

    public function possuiMembros(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM board_memberships WHERE term_id = :id");
        $stmt->execute([':id' => $id]);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    public function paginar(int $page = 1, ?int $perPage = 10, array $filters = []): array
    {
        $select = "t.*, (SELECT COUNT(*) FROM board_memberships bm WHERE bm.term_id = t.id) AS total_membros";
        $from = "FROM {$this->table} t";
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(t.nome LIKE :q OR t.observacoes LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['status'])) {
            $where[] = 't.status = :status';
            $params[':status'] = $filters['status'];
        }

        return Paginator::paginate(
            $this->db,
            $select,
            $from,
            implode(' AND ', $where),
            't.data_inicio DESC, t.id DESC',
            $params,
            $page,
            $perPage
        );
    }
}
