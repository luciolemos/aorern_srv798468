<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class LivroTipoModel extends Database
{
    protected string $table = 'livro_ocorrencia_tipos';

    public function paginar(
        int $page = 1,
        ?int $perPage = 12,
        ?string $busca = null,
        ?int $ativo = null
    ): array {
        $select = "lt.*, (SELECT COUNT(*) FROM livro_ocorrencias lo WHERE lo.tipo_id = lt.id) AS ocorrencias_total";
        $from = "FROM {$this->table} lt";
        $conditions = [];
        $params = [];

        if ($busca) {
            $conditions[] = "(lt.nome LIKE :busca OR lt.slug LIKE :busca OR lt.descricao LIKE :busca)";
            $params[':busca'] = '%' . $busca . '%';
        }

        if ($ativo !== null) {
            $conditions[] = "lt.ativo = :ativo";
            $params[':ativo'] = $ativo;
        }

        $where = implode(' AND ', $conditions);

        return Paginator::paginate(
            $this->connect(),
            $select,
            $from,
            $where,
            'lt.nome ASC',
            $params,
            $page,
            $perPage
        );
    }

    public function listarAtivos(): array
    {
        $stmt = $this->connect()->prepare("SELECT id, nome, slug, badge_color FROM {$this->table} WHERE ativo = 1 ORDER BY nome ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listarTodos(): array
    {
        $stmt = $this->connect()->prepare("SELECT id, nome, slug, badge_color, ativo FROM {$this->table} ORDER BY nome ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function buscar(int $id): ?array
    {
        $sql = "SELECT lt.*, (SELECT COUNT(*) FROM livro_ocorrencias lo WHERE lo.tipo_id = lt.id) AS ocorrencias_total " .
            "FROM {$this->table} lt WHERE lt.id = :id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorSlug(string $slug): ?array
    {
        return $this->findWhere($this->table, ['slug' => $slug]);
    }

    public function criar(array $dados)
    {
        return $this->insert($this->table, $dados);
    }

    public function atualizar(int $id, array $dados): bool
    {
        return $this->update($this->table, $id, $dados);
    }

    public function deletar(int $id): bool
    {
        return $this->delete($this->table, $id);
    }

    public function possuiOcorrencias(int $tipoId): bool
    {
        $stmt = $this->connect()->prepare("SELECT COUNT(*) FROM livro_ocorrencias WHERE tipo_id = :tipo");
        $stmt->execute([':tipo' => $tipoId]);
        return ((int) $stmt->fetchColumn()) > 0;
    }
}
