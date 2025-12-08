<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class Post extends Database {
    protected string $table = 'posts';

    public function todos(): array {
        $sql = "SELECT p.*, cp.nome AS categoria_nome, cp.badge_color AS categoria_cor
                FROM {$this->table} p
                LEFT JOIN categorias_posts cp ON cp.id = p.categoria_id
                ORDER BY p.criado_em DESC";
        return $this->connect()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function encontrarPorId(int $id): ?array {
        $stmt = $this->connect()->prepare("SELECT p.*, cp.nome AS categoria_nome, cp.badge_color AS categoria_cor FROM {$this->table} p LEFT JOIN categorias_posts cp ON cp.id = p.categoria_id WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function encontrarPorSlug(string $slug): ?array {
        $stmt = $this->connect()->prepare("SELECT p.*, cp.nome AS categoria_nome, cp.badge_color AS categoria_cor FROM {$this->table} p LEFT JOIN categorias_posts cp ON cp.id = p.categoria_id WHERE p.slug = ? AND p.status = 'published' AND COALESCE(p.is_hidden, 0) = 0");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function criar(array $data): bool {
        return $this->insert($this->table, $data);
    }

    public function atualizar(int $id, array $data): bool {
        return $this->update($this->table, $id, $data);
    }

    public function excluir(int $id): bool {
        return $this->delete($this->table, $id);
    }

    public function encontrarAnterior(string $criadoEm): ?array {
        $sql = "SELECT slug, titulo FROM {$this->table} WHERE criado_em < :criado_em AND status = 'published' AND COALESCE(is_hidden, 0) = 0 ORDER BY criado_em DESC LIMIT 1";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([':criado_em' => $criadoEm]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function encontrarProximo(string $criadoEm): ?array {
        $sql = "SELECT slug, titulo FROM {$this->table} WHERE criado_em > :criado_em AND status = 'published' AND COALESCE(is_hidden, 0) = 0 ORDER BY criado_em ASC LIMIT 1";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([':criado_em' => $criadoEm]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorTitulo(string $termo): array {
        $stmt = $this->connect()->prepare("SELECT * FROM {$this->table} WHERE titulo LIKE :termo ORDER BY criado_em DESC");
        $stmt->execute([':termo' => '%' . $termo . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginar(int $page = 1, int $perPage = 10, ?string $busca = null): array
    {
        $select = "p.*, cp.nome AS categoria_nome, cp.badge_color AS categoria_cor";
        $from = "FROM {$this->table} p LEFT JOIN categorias_posts cp ON cp.id = p.categoria_id";
        $where = '';
        $params = [];

        if ($busca) {
            $where = "(p.titulo LIKE :busca OR p.conteudo LIKE :busca)";
            $params[':busca'] = '%' . $busca . '%';
        }

        return Paginator::paginate(
            $this->connect(),
            $select,
            $from,
            $where,
            'p.criado_em DESC',
            $params,
            $page,
            $perPage
        );
    }

    public function paginarPorAutor(?int $userId, int $page = 1, int $perPage = 10, ?string $busca = null, ?string $status = null, ?string $category = null, ?string $visibility = null): array
    {
        $select = "p.*, cp.nome AS categoria_nome, cp.badge_color AS categoria_cor";
        $from = "FROM {$this->table} p LEFT JOIN categorias_posts cp ON cp.id = p.categoria_id";
        $conditions = [];
        $params = [];

        if ($userId) {
            $conditions[] = "p.user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        if ($busca) {
            $conditions[] = "(p.titulo LIKE :busca OR p.conteudo LIKE :busca)";
            $params[':busca'] = '%' . $busca . '%';
        }

        if ($status) {
            $conditions[] = "p.status = :status";
            $params[':status'] = $status;
        }

        if ($category) {
            $conditions[] = "p.categoria_id = :categoria_id";
            $params[':categoria_id'] = $category;
        }

        if ($visibility) {
            if ($visibility === 'hidden') {
                $conditions[] = "COALESCE(p.is_hidden, 0) = 1";
            } elseif ($visibility === 'visible') {
                $conditions[] = "COALESCE(p.is_hidden, 0) = 0";
            }
        }

        $where = implode(' AND ', $conditions);

        return Paginator::paginate(
            $this->connect(),
            $select,
            $from,
            $where,
            'p.criado_em DESC',
            $params,
            $page,
            $perPage
        );
    }

    public function paginarPorStatus(array $statuses, int $page = 1, int $perPage = 10, ?string $busca = null): array
    {
        $select = "p.*, cp.nome AS categoria_nome, cp.badge_color AS categoria_cor";
        $from = "FROM {$this->table} p LEFT JOIN categorias_posts cp ON cp.id = p.categoria_id";
        $conditions = [];
        $params = [];

        // Filtra por status(es) usando named parameters
        if (!empty($statuses)) {
            $placeholders = [];
            foreach ($statuses as $i => $status) {
                $key = ":status{$i}";
                $placeholders[] = $key;
                $params[$key] = $status;
            }
            $conditions[] = "p.status IN (" . implode(',', $placeholders) . ")";
        }

        if ($busca) {
            $conditions[] = "(p.titulo LIKE :busca OR p.conteudo LIKE :busca)";
            $params[':busca'] = '%' . $busca . '%';
        }

        $where = implode(' AND ', $conditions);

        return Paginator::paginate(
            $this->connect(),
            $select,
            $from,
            $where,
            'p.criado_em DESC',
            $params,
            $page,
            $perPage
        );
    }

    public function listarPublico(?string $busca, ?int $categoriaId, int $page = 1, int $perPage = 7): array
    {
        $select = "p.*, cp.nome AS categoria_nome, cp.badge_color AS categoria_cor";
        $from = "FROM {$this->table} p LEFT JOIN categorias_posts cp ON cp.id = p.categoria_id";

        $conditions = [];
        $params = [];

        // APENAS posts publicados e não ocultos
        $conditions[] = "p.status = :status";
        $params[':status'] = 'published';
        $conditions[] = "COALESCE(p.is_hidden, 0) = 0";

        if ($busca) {
            $conditions[] = "(p.titulo LIKE :busca OR p.conteudo LIKE :busca)";
            $params[':busca'] = '%' . $busca . '%';
        }

        if ($categoriaId) {
            $conditions[] = 'p.categoria_id = :categoria_id';
            $params[':categoria_id'] = $categoriaId;
        }

        $where = implode(' AND ', $conditions);

        return Paginator::paginate(
            $this->connect(),
            $select,
            $from,
            $where,
            'p.criado_em DESC',
            $params,
            $page,
            $perPage
        );
    }

    /**
     * Pagina posts com filtros avançados (para admin/gerente)
     */
    public function paginarComFiltros(
        int $page = 1,
        int $perPage = 10,
        ?string $busca = null,
        ?string $status = null,
        ?string $category = null,
        ?string $author = null,
        ?string $visibility = null
    ): array {
        $select = "p.*, cp.nome AS categoria_nome, cp.badge_color AS categoria_cor, u.name AS autor";
        $from = "FROM {$this->table} p 
                 LEFT JOIN categorias_posts cp ON cp.id = p.categoria_id
                 LEFT JOIN users u ON u.id = p.user_id";
        $conditions = [];
        $params = [];

        // Filtro: Status
        if ($status) {
            $conditions[] = "p.status = :status";
            $params[':status'] = $status;
        } else {
            // Se não especificar status, mostrar apenas pending e published (não rascunhos privados)
            $conditions[] = "p.status IN (:status0, :status1)";
            $params[':status0'] = 'pending';
            $params[':status1'] = 'published';
        }

        // Filtro: Busca em título e conteúdo
        if ($busca) {
            $conditions[] = "(p.titulo LIKE :busca OR p.conteudo LIKE :busca)";
            $params[':busca'] = '%' . $busca . '%';
        }

        // Filtro: Categoria
        if ($category) {
            $conditions[] = "p.categoria_id = :categoria_id";
            $params[':categoria_id'] = $category;
        }

        // Filtro: Autor
        if ($author) {
            $conditions[] = "p.user_id = :user_id";
            $params[':user_id'] = $author;
        }

        // Filtro: Visibilidade (apenas para posts publicados)
        if ($visibility) {
            if ($visibility === 'hidden') {
                $conditions[] = "p.status = 'published' AND COALESCE(p.is_hidden, 0) = 1";
            } elseif ($visibility === 'visible') {
                $conditions[] = "p.status = 'published' AND COALESCE(p.is_hidden, 0) = 0";
            }
        }

        $where = implode(' AND ', $conditions);

        return Paginator::paginate(
            $this->connect(),
            $select,
            $from,
            $where,
            'p.criado_em DESC',
            $params,
            $page,
            $perPage
        );
    }

    /**
     * Lista autores únicos que têm posts
     */
    public function listarAutoresUnicos(): array {
        $sql = "SELECT DISTINCT p.user_id, u.name 
                FROM {$this->table} p
                LEFT JOIN users u ON u.id = p.user_id
                WHERE p.user_id IS NOT NULL
                ORDER BY u.name ASC";
        return $this->connect()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
