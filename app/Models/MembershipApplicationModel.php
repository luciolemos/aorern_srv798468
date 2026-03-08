<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class MembershipApplicationModel
{
    private PDO $db;
    private string $table = 'membership_applications';
    private bool $hasUfColumn;
    private bool $hasCamColumn;
    private bool $hasRgColumn;
    private bool $hasPostoGraduacaoColumn;
    private bool $hasNumeroMilitarColumn;
    private bool $hasNomeGuerraColumn;
    private bool $hasNomeMaeColumn;
    private bool $hasNomePaiColumn;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->hasUfColumn = $this->detectColumn('uf');
        $this->hasCamColumn = $this->detectColumn('cam');
        $this->hasRgColumn = $this->detectColumn('rg');
        $this->hasPostoGraduacaoColumn = $this->detectColumn('posto_graduacao');
        $this->hasNumeroMilitarColumn = $this->detectColumn('numero_militar');
        $this->hasNomeGuerraColumn = $this->detectColumn('nome_guerra');
        $this->hasNomeMaeColumn = $this->detectColumn('nome_mae');
        $this->hasNomePaiColumn = $this->detectColumn('nome_pai');
    }

    public function salvar(array $dados): bool
    {
        $cidade = $dados['cidade'] ?: null;
        $uf = strtoupper((string) ($dados['uf'] ?? ''));
        if ($uf === '') {
            $uf = null;
        }

        $params = [
            ':nome_completo' => $dados['nome_completo'],
            ':username_desejado' => $dados['username_desejado'],
            ':email' => $dados['email'],
            ':password_hash' => $dados['password_hash'],
            ':cpf' => $dados['cpf'],
            ':data_nascimento' => $dados['data_nascimento'] ?: null,
            ':telefone' => $dados['telefone'] ?: null,
            ':cidade' => $this->hasUfColumn ? $cidade : $this->composeLegacyCity($cidade, $uf),
            ':ano_npor' => $dados['ano_npor'],
            ':turma_npor' => $dados['turma_npor'] ?: null,
            ':arma_quadro' => $dados['arma_quadro'] ?: null,
            ':situacao_militar' => $dados['situacao_militar'] ?? null,
            ':avatar' => $dados['avatar'] ?: null,
            ':documentos_json' => $dados['documentos_json'] ?? null,
            ':observacoes' => $dados['observacoes'] ?: null,
            ':aceite_termo' => (int) ($dados['aceite_termo'] ?? 1),
            ':status' => $dados['status'] ?? 'pendente',
            ':status_associativo' => $dados['status_associativo'] ?? 'provisorio',
            ':observacoes_admin' => $dados['observacoes_admin'] ?: null,
        ];

        $columns = [
            'nome_completo', 'username_desejado', 'email', 'password_hash', 'cpf',
            'data_nascimento', 'telefone', 'cidade',
        ];
        $values = [
            ':nome_completo', ':username_desejado', ':email', ':password_hash', ':cpf',
            ':data_nascimento', ':telefone', ':cidade',
        ];

        if ($this->hasCamColumn) {
            $columns[] = 'cam';
            $values[] = ':cam';
            $params[':cam'] = $dados['cam'] ?: null;
        }

        if ($this->hasRgColumn) {
            $columns[] = 'rg';
            $values[] = ':rg';
            $params[':rg'] = $dados['rg'] ?: null;
        }

        if ($this->hasNomeMaeColumn) {
            $columns[] = 'nome_mae';
            $values[] = ':nome_mae';
            $params[':nome_mae'] = $dados['nome_mae'] ?? null;
        }

        if ($this->hasNomePaiColumn) {
            $columns[] = 'nome_pai';
            $values[] = ':nome_pai';
            $params[':nome_pai'] = $dados['nome_pai'] ?? null;
        }

        if ($this->hasUfColumn) {
            $columns[] = 'uf';
            $values[] = ':uf';
            $params[':uf'] = $uf;
        }

        $columns = array_merge($columns, [
            'ano_npor', 'turma_npor', 'arma_quadro', 'situacao_militar', 'avatar', 'documentos_json', 'observacoes',
            'aceite_termo', 'status', 'status_associativo', 'observacoes_admin',
        ]);
        $values = array_merge($values, [
            ':ano_npor', ':turma_npor', ':arma_quadro', ':situacao_militar', ':avatar', ':documentos_json', ':observacoes',
            ':aceite_termo', ':status', ':status_associativo', ':observacoes_admin',
        ]);

        if ($this->hasPostoGraduacaoColumn) {
            $columns[] = 'posto_graduacao';
            $values[] = ':posto_graduacao';
            $params[':posto_graduacao'] = $dados['posto_graduacao'] ?: null;
        }

        if ($this->hasNumeroMilitarColumn) {
            $columns[] = 'numero_militar';
            $values[] = ':numero_militar';
            $params[':numero_militar'] = $dados['numero_militar'] ?: null;
        }

        if ($this->hasNomeGuerraColumn) {
            $columns[] = 'nome_guerra';
            $values[] = ':nome_guerra';
            $params[':nome_guerra'] = $dados['nome_guerra'] ?: null;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (" . implode(', ', $columns) . ")
             VALUES (" . implode(', ', $values) . ")"
        );

        return $stmt->execute($params);
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function atualizar(int $id, array $dados): bool
    {
        $fields = [];
        $params = [':id' => $id];

        $allowedFields = [
            'nome_completo', 'username_desejado', 'email', 'password_hash', 'cpf', 'data_nascimento',
            'telefone', 'cidade', 'ano_npor', 'turma_npor', 'arma_quadro', 'situacao_militar',
            'avatar', 'documentos_json', 'observacoes', 'aceite_termo', 'status', 'status_associativo', 'user_id', 'pessoal_id',
            'observacoes_admin', 'aprovado_em', 'rejeitado_em',
            'last_notification_type', 'last_notification_status', 'last_notification_at', 'last_notification_error'
        ];
        if ($this->hasCamColumn) {
            $allowedFields[] = 'cam';
        }
        if ($this->hasRgColumn) {
            $allowedFields[] = 'rg';
        }
        if ($this->hasPostoGraduacaoColumn) {
            $allowedFields[] = 'posto_graduacao';
        }
        if ($this->hasNumeroMilitarColumn) {
            $allowedFields[] = 'numero_militar';
        }
        if ($this->hasNomeGuerraColumn) {
            $allowedFields[] = 'nome_guerra';
        }
        if ($this->hasNomeMaeColumn) {
            $allowedFields[] = 'nome_mae';
        }
        if ($this->hasNomePaiColumn) {
            $allowedFields[] = 'nome_pai';
        }
        if ($this->hasUfColumn) {
            $allowedFields[] = 'uf';
        }

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $dados)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $dados[$field];
            }
        }

        if ($fields === []) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", atualizado_em = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function contarPorStatus(string $status): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status = :status");
        $stmt->execute([':status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function paginar(int $page = 1, ?int $perPage = 10, array $filters = []): array
    {
        $select = 'ma.*';
        $from = "FROM {$this->table} ma";
        $conditions = [];
        $params = [];

        if (!empty($filters['q'])) {
            $conditions[] = '(ma.nome_completo LIKE :q OR ma.email LIKE :q OR ma.username_desejado LIKE :q OR ma.cpf LIKE :q OR ma.ano_npor LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'ma.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['status_associativo'])) {
            $conditions[] = 'ma.status_associativo = :status_associativo';
            $params[':status_associativo'] = $filters['status_associativo'];
        }

        if (!empty($filters['cidade'])) {
            if ($this->hasUfColumn) {
                $conditions[] = 'ma.cidade = :cidade';
                $params[':cidade'] = $filters['cidade'];
            } else {
                $conditions[] = '(ma.cidade = :cidade OR ma.cidade LIKE :cidade_legacy)';
                $params[':cidade'] = $filters['cidade'];
                $params[':cidade_legacy'] = $filters['cidade'] . '/%';
            }
        }

        if (!empty($filters['uf'])) {
            $uf = strtoupper((string) $filters['uf']);
            if ($this->hasUfColumn) {
                $conditions[] = 'ma.uf = :uf';
                $params[':uf'] = $uf;
            } else {
                $conditions[] = 'ma.cidade LIKE :uf_legacy';
                $params[':uf_legacy'] = '%/' . $uf;
            }
        }

        return Paginator::paginate(
            $this->db,
            $select,
            $from,
            implode(' AND ', $conditions),
            'ma.criado_em DESC',
            $params,
            $page,
            $perPage
        );
    }

    public function listarCidades(): array
    {
        if ($this->hasUfColumn) {
            $stmt = $this->db->query(
                "SELECT DISTINCT cidade
                 FROM {$this->table}
                 WHERE cidade IS NOT NULL AND cidade <> ''
                 ORDER BY cidade ASC"
            );

            return array_values(array_filter(array_map(
                static fn($cidade) => is_string($cidade) ? trim($cidade) : '',
                $stmt->fetchAll(PDO::FETCH_COLUMN)
            )));
        }

        $stmt = $this->db->query(
            "SELECT DISTINCT
                TRIM(
                    CASE
                        WHEN cidade REGEXP '/[A-Za-z]{2}$' THEN SUBSTRING_INDEX(cidade, '/', 1)
                        ELSE cidade
                    END
                ) AS cidade_normalizada
             FROM {$this->table}
             WHERE cidade IS NOT NULL AND cidade <> ''
             ORDER BY cidade_normalizada ASC"
        );

        return array_values(array_filter(array_map(
            static fn($cidade) => is_string($cidade) ? trim($cidade) : '',
            $stmt->fetchAll(PDO::FETCH_COLUMN)
        )));
    }

    public function listarUfs(): array
    {
        if ($this->hasUfColumn) {
            $stmt = $this->db->query(
                "SELECT DISTINCT UPPER(uf) AS uf
                 FROM {$this->table}
                 WHERE uf IS NOT NULL AND uf <> ''
                 ORDER BY uf ASC"
            );

            return array_values(array_filter(array_map(
                static fn($uf) => is_string($uf) ? trim($uf) : '',
                $stmt->fetchAll(PDO::FETCH_COLUMN)
            )));
        }

        $stmt = $this->db->query(
            "SELECT DISTINCT UPPER(TRIM(SUBSTRING_INDEX(cidade, '/', -1))) AS uf
             FROM {$this->table}
             WHERE cidade IS NOT NULL
               AND cidade REGEXP '/[A-Za-z]{2}$'
             ORDER BY uf ASC"
        );

        return array_values(array_filter(array_map(
            static fn($uf) => is_string($uf) ? trim($uf) : '',
            $stmt->fetchAll(PDO::FETCH_COLUMN)
        )));
    }

    public function existeSolicitacaoAtivaPorIdentidade(string $username, string $email, string $cpf): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table}
             WHERE status IN ('pendente', 'complementacao', 'aprovada')
               AND (username_desejado = :username OR email = :email OR cpf = :cpf)"
        );
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':cpf' => $cpf,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function buscarPorUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarMaisRecentePorCpf(string $cpf): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE cpf = :cpf
             ORDER BY COALESCE(aprovado_em, criado_em) DESC, id DESC
             LIMIT 1"
        );
        $stmt->execute([':cpf' => $cpf]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarMaisRecentePorPessoalId(int $pessoalId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE pessoal_id = :pessoal_id
             ORDER BY COALESCE(aprovado_em, criado_em) DESC, id DESC
             LIMIT 1"
        );
        $stmt->execute([':pessoal_id' => $pessoalId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function detectColumn(string $column): bool
    {
        $stmt = $this->db->prepare("SHOW COLUMNS FROM {$this->table} LIKE :column");
        $stmt->execute([':column' => $column]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function composeLegacyCity(?string $cidade, ?string $uf): ?string
    {
        $cidade = is_string($cidade) ? trim($cidade) : '';
        $uf = is_string($uf) ? strtoupper(trim($uf)) : '';

        if ($cidade === '') {
            return null;
        }

        if ($uf !== '' && !preg_match('#/[A-Z]{2}$#', $cidade)) {
            return $cidade . '/' . $uf;
        }

        return $cidade;
    }
}
