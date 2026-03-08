<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class InstitutionalDocumentModel
{
    private PDO $db;
    private string $table = 'institutional_documents';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function statusOptions(): array
    {
        return ['draft', 'published', 'archived'];
    }

    public function typeOptions(): array
    {
        return ['estatuto', 'ata', 'oficio', 'formulario', 'politica', 'termo', 'marca', 'outro'];
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
            "INSERT INTO {$this->table} (
                titulo, slug, tipo, resumo, arquivo_url, link_externo, status, publicado_em, ordem
            ) VALUES (
                :titulo, :slug, :tipo, :resumo, :arquivo_url, :link_externo, :status, :publicado_em, :ordem
            )"
        );

        return $stmt->execute($dados);
    }

    public function atualizar(int $id, array $dados): bool
    {
        $dados['id'] = $id;
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET
                titulo = :titulo,
                slug = :slug,
                tipo = :tipo,
                resumo = :resumo,
                arquivo_url = :arquivo_url,
                link_externo = :link_externo,
                status = :status,
                publicado_em = :publicado_em,
                ordem = :ordem,
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
        $select = 'd.*';
        $from = "FROM {$this->table} d";
        $where = [];
        $params = [];
        $orderBy = 'd.ordem ASC, d.publicado_em DESC, d.id DESC';

        if (!empty($filters['q'])) {
            $where[] = '(d.titulo LIKE :q OR d.resumo LIKE :q OR d.slug LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['status'])) {
            $where[] = 'd.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $where[] = 'd.tipo = :tipo';
            $params[':tipo'] = $filters['type'];
        }

        if (($filters['sort'] ?? null) === 'recentes') {
            $orderBy = 'd.publicado_em DESC, d.id DESC, d.ordem ASC';
        }

        return Paginator::paginate(
            $this->db,
            $select,
            $from,
            implode(' AND ', $where),
            $orderBy,
            $params,
            $page,
            $perPage
        );
    }

    public function buscarPorSlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPublicadoPorSlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = :slug AND status = 'published' LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPrimeiroPublicadoPorTipo(string $tipo): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE tipo = :tipo AND status = 'published'
             ORDER BY ordem ASC, publicado_em DESC, id DESC
             LIMIT 1"
        );
        $stmt->execute([':tipo' => $tipo]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function ensureUniqueSlug(string $desired, ?int $ignoreId = null): string
    {
        $slug = $desired;
        $counter = 1;

        while ($existing = $this->buscarPorSlug($slug)) {
            if ($ignoreId !== null && (int) $existing['id'] === $ignoreId) {
                break;
            }

            $slug = $desired . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
