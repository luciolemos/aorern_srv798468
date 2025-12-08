<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class PostCategoryModel
{
    private PDO $db;
    private string $table = 'categorias_posts';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function listar(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function salvar(array $dados): bool
    {
        $sql = "INSERT INTO {$this->table} (staff_id, nome, badge_color, descricao) VALUES (:staff_id, :nome, :badge_color, :descricao)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':staff_id' => $dados['staff_id'],
            ':nome' => $dados['nome'],
            ':badge_color' => $dados['badge_color'],
            ':descricao' => $dados['descricao']
        ]);
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE {$this->table} SET nome = :nome, badge_color = :badge_color, descricao = :descricao WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':badge_color' => $dados['badge_color'],
            ':descricao' => $dados['descricao'],
            ':id' => $id
        ]);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function buscarPorNome(string $termo): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE nome LIKE :termo ORDER BY nome ASC");
        $stmt->execute([':termo' => "%{$termo}%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function possuiPosts(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM posts WHERE categoria_id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ((int)($row['total'] ?? 0)) > 0;
    }

    public function paginar(int $page = 1, int $perPage = 10, ?string $busca = null): array
    {
        $select = "c.*";
        $from = "FROM {$this->table} c";
        $where = '';
        $params = [];

        if ($busca) {
            $where = "c.nome LIKE :busca";
            $params[':busca'] = '%' . $busca . '%';
        }

        return Paginator::paginate(
            $this->db,
            $select,
            $from,
            $where,
            'c.nome ASC',
            $params,
            $page,
            $perPage
        );
    }
}
