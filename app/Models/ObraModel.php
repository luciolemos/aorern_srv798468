<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class ObraModel {
    private PDO $db;
    private string $table = 'obras';

    public function __construct() {
        $this->db = Database::connect();
    }

    public function listar(): array {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY criado_em DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function all(string $orderBy = 'id DESC', ?int $limit = null): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function salvar(array $dados): bool {
        $sql = "INSERT INTO {$this->table} (
            numero_obra, natureza_obra, descricao, endereco, cep,
            data_inicio, data_termino, status, prioridade,
            valor_estimado, observacoes
        ) VALUES (
            :numero_obra, :natureza_obra, :descricao, :endereco, :cep,
            :data_inicio, :data_termino, :status, :prioridade,
            :valor_estimado, :observacoes
        )";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($dados);
    }

    public function atualizar(int $id, array $dados): bool {
        $sql = "UPDATE {$this->table} SET
            numero_obra = :numero_obra,
            natureza_obra = :natureza_obra,
            descricao = :descricao,
            endereco = :endereco,
            cep = :cep,
            data_inicio = :data_inicio,
            data_termino = :data_termino,
            status = :status,
            prioridade = :prioridade,
            valor_estimado = :valor_estimado,
            observacoes = :observacoes
        WHERE id = :id";

        $dados['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($dados);
    }

    public function deletar(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function listarObrasSimples(): array {
        $stmt = $this->db->query("SELECT id, descricao FROM {$this->table} ORDER BY descricao");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorDescricao(string $termo): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE descricao LIKE :termo ORDER BY criado_em DESC");
        $stmt->execute([':termo' => '%' . $termo . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contar(): int {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM {$this->table}");
        return (int) $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

}
