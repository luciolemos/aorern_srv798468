<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use DateTime;
use PDO;

class LivroOcorrenciaModel extends Database
{
    protected string $table = 'livro_ocorrencias';

    public function contarPorStatus(string $status, ?string $subgrupamento = null): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE status = :status";
        $params = [':status' => $status];

        if ($subgrupamento) {
            $sql .= " AND subgrupamento = :subgrupamento";
            $params[':subgrupamento'] = $subgrupamento;
        }

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function contarTodos(?string $subgrupamento = null): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];

        if ($subgrupamento) {
            $sql .= " WHERE subgrupamento = :subgrupamento";
            $params[':subgrupamento'] = $subgrupamento;
        }

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function buscar(int $id): ?array
    {
        $sql = "SELECT lo.*, lt.nome AS tipo_nome, lt.badge_color AS tipo_badge_color, u.username AS responsavel_nome " .
            "FROM {$this->table} lo " .
            "LEFT JOIN livro_ocorrencia_tipos lt ON lt.id = lo.tipo_id " .
            "LEFT JOIN users u ON u.id = lo.responsavel_id " .
            "WHERE lo.id = :id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorProtocolo(string $protocolo): ?array
    {
        $sql = "SELECT lo.*, lt.nome AS tipo_nome, lt.badge_color AS tipo_badge_color, u.username AS responsavel_nome " .
            "FROM {$this->table} lo " .
            "LEFT JOIN livro_ocorrencia_tipos lt ON lt.id = lo.tipo_id " .
            "LEFT JOIN users u ON u.id = lo.responsavel_id " .
            "WHERE lo.protocolo = :protocolo";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([':protocolo' => $protocolo]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function criar(array $dados): bool
    {
        $dados = $this->syncStatusWithClosedAt($dados);
        return $this->insert($this->table, $dados) !== false;
    }

    public function atualizar(int $id, array $dados): bool
    {
        $dados = $this->syncStatusWithClosedAt($dados);
        return $this->update($this->table, $id, $dados);
    }

    public function deletar(int $id): bool
    {
        return $this->delete($this->table, $id);
    }

    public function gerarProtocolo(?string $dataOcorrencia = null): string
    {
        $data = $dataOcorrencia ? new DateTime($dataOcorrencia) : new DateTime();
        $dia = $data->format('Ymd');
        $stmt = $this->connect()->prepare("SELECT COUNT(*) FROM {$this->table} WHERE DATE(data_ocorrencia) = :data");
        $stmt->execute([':data' => $data->format('Y-m-d')]);
        $sequencial = ((int) $stmt->fetchColumn()) + 1;
        return sprintf('OCR-%s-%04d', $dia, $sequencial);
    }

    public function contarPorTipo(int $limit = 4, ?string $status = null): array
    {
        $sql = "SELECT lt.id AS tipo_id, lt.nome AS tipo, lt.badge_color, COUNT(*) AS total " .
            "FROM {$this->table} lo " .
            "INNER JOIN livro_ocorrencia_tipos lt ON lt.id = lo.tipo_id " .
            "WHERE 1 = 1";

        $params = [];

        if ($status) {
            $sql .= " AND lo.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " GROUP BY lo.tipo_id, lt.nome, lt.badge_color ORDER BY total DESC";

        if ($limit > 0) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->connect()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if ($limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function contarPorTipoPorFechamento(
        int $limit = 4,
        ?string $fechamentoInicial = null,
        ?string $fechamentoFinal = null,
        ?string $subgrupamento = null
    ): array {
        $sql = "SELECT lt.id AS tipo_id, lt.nome AS tipo, lt.badge_color, COUNT(*) AS total " .
            "FROM {$this->table} lo " .
            "INNER JOIN livro_ocorrencia_tipos lt ON lt.id = lo.tipo_id " .
            "WHERE lo.closed_at IS NOT NULL";

        $params = [];

        if ($fechamentoInicial) {
            $sql .= " AND lo.closed_at >= :fechamento_inicial";
            $params[':fechamento_inicial'] = $fechamentoInicial;
        }

        if ($fechamentoFinal) {
            $sql .= " AND lo.closed_at <= :fechamento_final";
            $params[':fechamento_final'] = $fechamentoFinal;
        }

        if ($subgrupamento) {
            $sql .= " AND lo.subgrupamento = :subgrupamento";
            $params[':subgrupamento'] = $subgrupamento;
        }

        $sql .= " GROUP BY lo.tipo_id, lt.nome, lt.badge_color ORDER BY total DESC";

        if ($limit > 0) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->connect()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if ($limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function contarTotaisPorMunicipio(
        ?string $dataInicio = null,
        ?string $dataFim = null,
        ?int $tipoId = null,
        ?string $status = null,
        ?string $subgrupamento = null
    ): array
    {
        $sql = "SELECT lo.municipio_codigo, lo.municipio_nome, COUNT(*) AS total " .
            "FROM {$this->table} lo WHERE 1 = 1";
        $params = [];

        if ($dataInicio) {
            $sql .= " AND lo.data_ocorrencia >= :data_inicio";
            $params[':data_inicio'] = $dataInicio;
        }

        if ($dataFim) {
            $sql .= " AND lo.data_ocorrencia <= :data_fim";
            $params[':data_fim'] = $dataFim;
        }

        if ($tipoId) {
            $sql .= " AND lo.tipo_id = :tipo_id";
            $params[':tipo_id'] = $tipoId;
        }

        if ($status) {
            $sql .= " AND lo.status = :status";
            $params[':status'] = $status;
        }

        if ($subgrupamento) {
            $sql .= " AND lo.subgrupamento = :subgrupamento";
            $params[':subgrupamento'] = $subgrupamento;
        }

        $sql .= " GROUP BY lo.municipio_codigo, lo.municipio_nome ORDER BY total DESC, lo.municipio_nome ASC";

        $stmt = $this->connect()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function paginarComFiltros(
        int $page = 1,
        ?int $perPage = 12,
        ?string $busca = null,
        ?string $status = null,
        ?int $tipoId = null,
        ?string $subgrupamento = null,
        ?int $municipioCodigo = null,
        ?string $dataInicio = null,
        ?string $dataFim = null,
        ?int $responsavelId = null
    ): array {
        $select = "lo.*, lt.nome AS tipo_nome, lt.badge_color AS tipo_badge_color, u.username AS responsavel_nome";
        $from = "FROM {$this->table} lo " .
            "LEFT JOIN livro_ocorrencia_tipos lt ON lt.id = lo.tipo_id " .
            "LEFT JOIN users u ON u.id = lo.responsavel_id";
        $conditions = [];
        $params = [];

        if ($busca) {
            $conditions[] = "(lo.protocolo LIKE :busca OR lo.descricao LIKE :busca OR lo.relatorio_conclusao LIKE :busca OR lo.municipio_nome LIKE :busca)";
            $params[':busca'] = '%' . $busca . '%';
        }

        if ($status) {
            $conditions[] = "lo.status = :status";
            $params[':status'] = $status;
        }

        if ($tipoId) {
            $conditions[] = "lo.tipo_id = :tipo";
            $params[':tipo'] = $tipoId;
        }

        if ($subgrupamento) {
            $conditions[] = "lo.subgrupamento = :subgrupamento";
            $params[':subgrupamento'] = $subgrupamento;
        }

        if ($municipioCodigo) {
            $conditions[] = "lo.municipio_codigo = :municipio";
            $params[':municipio'] = $municipioCodigo;
        }

        if ($responsavelId) {
            $conditions[] = "lo.responsavel_id = :responsavel";
            $params[':responsavel'] = $responsavelId;
        }

        if ($dataInicio) {
            $conditions[] = "lo.data_ocorrencia >= :data_inicio";
            $params[':data_inicio'] = $dataInicio;
        }

        if ($dataFim) {
            $conditions[] = "lo.data_ocorrencia <= :data_fim";
            $params[':data_fim'] = $dataFim;
        }

        $where = implode(' AND ', $conditions);

        return Paginator::paginate(
            $this->connect(),
            $select,
            $from,
            $where,
            'lo.data_ocorrencia DESC, lo.id DESC',
            $params,
            $page,
            $perPage
        );
    }

    private function syncStatusWithClosedAt(array $dados): array
    {
        if (!array_key_exists('closed_at', $dados)) {
            if (!isset($dados['status'])) {
                $dados['status'] = 'aberta';
            }
            return $dados;
        }

        $closedAt = $dados['closed_at'];
        $dados['status'] = $closedAt ? 'concluida' : 'aberta';
        return $dados;
    }
}
