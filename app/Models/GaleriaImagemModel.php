<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class GaleriaImagemModel
{
    private PDO $db;
    private string $table = 'gallery_images';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function paginar(
        int $page = 1,
        ?int $perPage = 12,
        ?string $busca = null,
        ?int $categoriaId = null
    ): array {
        $select = 'gi.*, gc.nome AS categoria_nome, gc.slug AS categoria_slug, gc.color AS categoria_color';
        $from = "FROM {$this->table} gi INNER JOIN gallery_categories gc ON gc.id = gi.category_id";
        $params = [];
        $clauses = [];

        if ($busca !== null && $busca !== '') {
            $clauses[] = '(gi.titulo LIKE :busca OR gi.descricao LIKE :busca)';
            $params[':busca'] = '%' . $busca . '%';
        }

        if ($categoriaId !== null) {
            $clauses[] = 'gi.category_id = :categoria_id';
            $params[':categoria_id'] = $categoriaId;
        }

        $where = implode(' AND ', $clauses);

        return Paginator::paginate(
            $this->db,
            $select,
            $from,
            $where,
            'gi.data_upload DESC',
            $params,
            $page,
            $perPage
        );
    }

    public function buscar(int $id): ?array
    {
        $sql = "SELECT gi.*, gc.nome AS categoria_nome, gc.slug AS categoria_slug, gc.color AS categoria_color
                FROM {$this->table} gi
                INNER JOIN gallery_categories gc ON gc.id = gi.category_id
                WHERE gi.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function salvar(array $dados): int
    {
        $sql = "INSERT INTO {$this->table} (category_id, titulo, descricao, url, data_upload)
                VALUES (:category_id, :titulo, :descricao, :url, :data_upload)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':category_id' => $dados['category_id'],
            ':titulo' => $dados['titulo'],
            ':descricao' => $dados['descricao'] ?? null,
            ':url' => $dados['url'],
            ':data_upload' => $dados['data_upload'] ?? date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE {$this->table}
                SET category_id = :category_id,
                    titulo = :titulo,
                    descricao = :descricao,
                    url = :url
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':category_id' => $dados['category_id'],
            ':titulo' => $dados['titulo'],
            ':descricao' => $dados['descricao'] ?? null,
            ':url' => $dados['url'],
            ':id' => $id,
        ]);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function listarRecentes(int $limit = 12): array
    {
        $limit = max(1, $limit);
        $stmt = $this->db->prepare(
            "SELECT gi.*, gc.nome AS categoria_nome, gc.slug AS categoria_slug, gc.color AS categoria_color
             FROM {$this->table} gi
             INNER JOIN gallery_categories gc ON gc.id = gi.category_id
             ORDER BY gi.data_upload DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
