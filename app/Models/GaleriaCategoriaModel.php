<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class GaleriaCategoriaModel
{
    private PDO $db;
    private string $table = 'gallery_categories';

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
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function buscarPorSlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function salvar(array $dados): int
    {
        $sql = "INSERT INTO {$this->table} (nome, slug, color) VALUES (:nome, :slug, :color)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nome' => $dados['nome'],
            ':slug' => $dados['slug'],
            ':color' => $dados['color'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE {$this->table} SET nome = :nome, slug = :slug, color = :color WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':slug' => $dados['slug'],
            ':color' => $dados['color'] ?? null,
            ':id' => $id,
        ]);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function possuiImagens(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM gallery_images WHERE category_id = :id');
        $stmt->execute([':id' => $id]);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    public function paginar(int $page = 1, ?int $perPage = 12, ?string $busca = null): array
    {
        $select = 'gc.*, (SELECT COUNT(*) FROM gallery_images gi WHERE gi.category_id = gc.id) AS total_imagens';
        $from = "FROM {$this->table} gc";
        $params = [];
        $where = '';

        if ($busca !== null && $busca !== '') {
            $where = '(gc.nome LIKE :busca OR gc.slug LIKE :busca)';
            $params[':busca'] = '%' . $busca . '%';
        }

        return Paginator::paginate(
            $this->db,
            $select,
            $from,
            $where,
            'gc.nome ASC',
            $params,
            $page,
            $perPage
        );
    }
}
