<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class PessoalModel {
    private $db;
    private string $table = 'pessoal';

    public function __construct() {
        $this->db = Database::connect();
    }

    private function baseSelect(): string
    {
        return "SELECT p.*, f.nome AS funcao_nome FROM {$this->table} p LEFT JOIN funcoes f ON f.id = p.funcao_id";
    }

    public function listar(): array {
        $stmt = $this->db->query($this->baseSelect() . " ORDER BY p.criado_em DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function all(string $orderBy = 'p.id DESC', ?int $limit = null): array {
        $sql = $this->baseSelect() . " ORDER BY {$orderBy}";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): ?array {
        $stmt = $this->db->prepare($this->baseSelect() . " WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function salvar(array $dados): bool {
        $sql = "INSERT INTO {$this->table} (
            staff_id, nome, cpf, nascimento, telefone, foto,
            funcao_id, obra_id, data_admissao, status, jornada, observacoes
        ) VALUES (
            :staff_id, :nome, :cpf, :nascimento, :telefone, :foto,
            :funcao_id, :obra_id, :data_admissao, :status, :jornada, :observacoes
        )";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':staff_id'      => $dados['staff_id'],
            ':nome'          => $dados['nome'],
            ':cpf'           => $dados['cpf'],
            ':nascimento'    => $dados['nascimento'],
            ':telefone'      => $dados['telefone'],
            ':foto'          => $dados['foto'] ?? null,
            ':funcao_id'     => $dados['funcao_id'],
            ':obra_id'       => $dados['obra_id'],
            ':data_admissao' => $dados['data_admissao'],
            ':status'        => $dados['status'],
            ':jornada'       => $dados['jornada'],
            ':observacoes'   => $dados['observacoes']
        ]);
    }

    public function atualizar(int $id, array $dados): bool {
        $sql = "UPDATE {$this->table} SET
            nome = :nome,
            cpf = :cpf,
            nascimento = :nascimento,
            telefone = :telefone,
            foto = :foto,
            funcao_id = :funcao_id,
            obra_id = :obra_id,
            data_admissao = :data_admissao,
            status = :status,
            jornada = :jornada,
            observacoes = :observacoes
        WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome'          => $dados['nome'],
            ':cpf'           => $dados['cpf'],
            ':nascimento'    => $dados['nascimento'],
            ':telefone'      => $dados['telefone'],
            ':foto'          => $dados['foto'] ?? null,
            ':funcao_id'     => $dados['funcao_id'],
            ':obra_id'       => $dados['obra_id'],
            ':data_admissao' => $dados['data_admissao'],
            ':status'        => $dados['status'],
            ':jornada'       => $dados['jornada'],
            ':observacoes'   => $dados['observacoes'],
            ':id'            => $id
        ]);
    }

    public function deletar(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function buscarPorTermo(string $termo): array {
        $like = "%{$termo}%";
        $stmt = $this->db->prepare(
            $this->baseSelect() . " 
            WHERE p.nome LIKE :term
               OR p.cpf LIKE :term
               OR p.telefone LIKE :term
               OR f.nome LIKE :term
            ORDER BY p.criado_em DESC"
        );
        $stmt->execute([':term' => $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contar(): int {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM {$this->table}");
        return (int) $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    public function paginar(int $page = 1, ?int $perPage = 12, array $filters = []): array
    {
        $select = "p.*, f.nome AS funcao_nome";
        $from = "FROM {$this->table} p LEFT JOIN funcoes f ON f.id = p.funcao_id";
        $conditions = [];
        $params = [];

        $termo = trim($filters['q'] ?? '') ?: null;
        if ($termo) {
            $conditions[] = "(p.nome LIKE :termo
                OR p.cpf LIKE :termo
                OR p.telefone LIKE :termo
                OR f.nome LIKE :termo)";
            $params[':termo'] = '%' . $termo . '%';
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'p.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['funcao_id'])) {
            $conditions[] = 'p.funcao_id = :funcao_id';
            $params[':funcao_id'] = (int) $filters['funcao_id'];
        }

        if (!empty($filters['obra_id'])) {
            $conditions[] = 'p.obra_id = :obra_id';
            $params[':obra_id'] = (int) $filters['obra_id'];
        }

        if (!empty($filters['admissao_inicio'])) {
            $conditions[] = 'p.data_admissao >= :admissao_inicio';
            $params[':admissao_inicio'] = $filters['admissao_inicio'];
        }

        if (!empty($filters['admissao_fim'])) {
            $conditions[] = 'p.data_admissao <= :admissao_fim';
            $params[':admissao_fim'] = $filters['admissao_fim'];
        }

        $where = implode(' AND ', $conditions);

        return Paginator::paginate(
            $this->db,
            $select,
            $from,
            $where,
            'p.criado_em DESC',
            $params,
            $page,
            $perPage
        );
    }
}
