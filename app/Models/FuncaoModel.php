<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class FuncaoModel {
    private PDO $db;
    private string $table = 'funcoes';

    public function __construct() {
        $this->db = Database::connect();
    }

    public function listar(): array {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function salvar(array $dados): bool {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (staff_id, nome) VALUES (:staff_id, :nome)");
        return $stmt->execute([
            ':staff_id' => $dados['staff_id'],
            ':nome'     => $dados['nome']
        ]);
    }

    public function atualizar(int $id, array $dados): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET nome = :nome WHERE id = :id");
        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':id'   => $id
        ]);
    }

    public function deletar(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function possuiBombeiros(int $id): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM pessoal WHERE funcao_id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ((int) ($row['total'] ?? 0)) > 0;
    }

    public function buscarPorNome(string $termo): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE nome LIKE :termo ORDER BY nome ASC");
        $stmt->execute([':termo' => "%{$termo}%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarNomePorId(int $id): ?string {
        $stmt = $this->db->prepare("SELECT nome FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['nome'] ?? null;
    }

    public function contar(): int {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM {$this->table}");
        return (int) $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    public function paginar(int $page = 1, ?int $perPage = 12, ?string $busca = null): array
    {
        $select = "f.*";
        $from = "FROM {$this->table} f";
        $where = '';
        $params = [];

        if ($busca) {
            $where = "f.nome LIKE :busca";
            $params[':busca'] = '%' . $busca . '%';
        }

        return Paginator::paginate(
            $this->db,
            $select,
            $from,
            $where,
            'f.nome ASC',
            $params,
            $page,
            $perPage
        );
    }
}
