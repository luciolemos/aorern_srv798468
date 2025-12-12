<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class DogBreedModel
{
    private PDO $db;
    private string $table = 'dog_breeds';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function paginarComFiltros(
        int $page = 1,
        ?int $perPage = 12,
        ?string $busca = null,
        ?string $size = null,
        ?string $function = null
    ): array {
        $select = 'b.*';
        $from = "FROM {$this->table} b";
        $conditions = [];
        $params = [];

        if ($busca) {
            $conditions[] = 'b.name LIKE :busca';
            $params[':busca'] = '%' . $busca . '%';
        }

        if ($size) {
            $conditions[] = 'b.size = :size';
            $params[':size'] = $size;
        }

        if ($function) {
            $conditions[] = 'b.`function` = :function';
            $params[':function'] = $function;
        }

        $where = implode(' AND ', $conditions);

        return Paginator::paginate(
            $this->db,
            $select,
            $from,
            $where,
            'b.name ASC',
            $params,
            $page,
            $perPage
        );
    }

    public function listarTodas(): array
    {
        $stmt = $this->db->query("SELECT id, name FROM {$this->table} ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorSlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function criar(array $dados): bool
    {
        $sql = "INSERT INTO {$this->table}
            (name, slug, size, `function`, origin, description, image_url)
            VALUES (:name, :slug, :size, :function, :origin, :description, :image_url)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $dados['name'],
            ':slug' => $dados['slug'],
            ':size' => $dados['size'] ?? null,
            ':function' => $dados['function'] ?? null,
            ':origin' => $dados['origin'] ?? null,
            ':description' => $dados['description'] ?? null,
            ':image_url' => $dados['image_url'] ?? null,
        ]);
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE {$this->table}
            SET name = :name,
                slug = :slug,
                size = :size,
                `function` = :function,
                origin = :origin,
                description = :description,
                image_url = :image_url
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $dados['name'],
            ':slug' => $dados['slug'],
            ':size' => $dados['size'] ?? null,
            ':function' => $dados['function'] ?? null,
            ':origin' => $dados['origin'] ?? null,
            ':description' => $dados['description'] ?? null,
            ':image_url' => $dados['image_url'] ?? null,
            ':id' => $id,
        ]);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function slugExiste(string $slug, ?int $ignoreId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
        $params = [':slug' => $slug];

        if ($ignoreId !== null) {
            $sql .= " AND id != :ignore";
            $params[':ignore'] = $ignoreId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function temCachorros(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM dogs WHERE breed_id = :id');
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
