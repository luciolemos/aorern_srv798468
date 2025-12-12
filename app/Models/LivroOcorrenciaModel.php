<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use DateTime;
use PDO;

class LivroOcorrenciaModel extends Database
{
    protected string $table = 'livro_ocorrencias';

    public function contarPorStatus(string $status): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE status = :status";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([':status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function buscar(int $id): ?array
    {
        $sql = "SELECT lo.*, u.username AS responsavel_nome " .
            "FROM {$this->table} lo " .
            "LEFT JOIN users u ON u.id = lo.responsavel_id " .
            "WHERE lo.id = :id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorProtocolo(string $protocolo): ?array
    {
        $sql = "SELECT lo.*, u.username AS responsavel_nome " .
            "FROM {$this->table} lo " .
            "LEFT JOIN users u ON u.id = lo.responsavel_id " .
            "WHERE lo.protocolo = :protocolo";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([':protocolo' => $protocolo]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function criar(array $dados): bool
    {
        return $this->insert($this->table, $dados) !== false;
    }

    public function atualizar(int $id, array $dados): bool
    {
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

    public function paginarComFiltros(
        int $page = 1,
        ?int $perPage = 12,
        ?string $busca = null,
        ?string $status = null,
        ?string $tipo = null,
        ?string $subgrupamento = null,
        ?int $municipioCodigo = null,
        ?string $dataInicio = null,
        ?string $dataFim = null,
        ?int $responsavelId = null
    ): array {
        $select = "lo.*, u.username AS responsavel_nome";
        $from = "FROM {$this->table} lo LEFT JOIN users u ON u.id = lo.responsavel_id";
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

        if ($tipo) {
            $conditions[] = "lo.tipo_ocorrencia = :tipo";
            $params[':tipo'] = $tipo;
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
}
