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
        return "SELECT p.*, f.nome AS funcao_nome,
                u.username AS user_username, u.email AS user_email, u.avatar AS user_avatar, u.role AS user_role,
                u.ativo AS user_ativo, u.status AS user_status
                FROM {$this->table} p
                LEFT JOIN funcoes f ON f.id = p.funcao_id
                LEFT JOIN users u ON u.id = p.user_id";
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

    public function buscarPorCpf(string $cpf): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . " WHERE p.cpf = ?");
        $stmt->execute([$cpf]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorStaffId(string $staffId): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . " WHERE p.staff_id = ?");
        $stmt->execute([$staffId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function salvar(array $dados): bool {
        $sql = "INSERT INTO {$this->table} (
            staff_id, nome, cpf, nascimento, telefone, foto,
            user_id, funcao_id, obra_id, data_admissao, status, status_associativo, jornada, observacoes
        ) VALUES (
            :staff_id, :nome, :cpf, :nascimento, :telefone, :foto,
            :user_id, :funcao_id, :obra_id, :data_admissao, :status, :status_associativo, :jornada, :observacoes
        )";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':staff_id'      => $dados['staff_id'],
            ':nome'          => $dados['nome'],
            ':cpf'           => $dados['cpf'],
            ':nascimento'    => $dados['nascimento'],
            ':telefone'      => $dados['telefone'],
            ':foto'          => $dados['foto'] ?? null,
            ':user_id'       => $dados['user_id'] ?? null,
            ':funcao_id'     => $dados['funcao_id'],
            ':obra_id'       => $dados['obra_id'],
            ':data_admissao' => $dados['data_admissao'],
            ':status'        => $dados['status'],
            ':status_associativo' => $dados['status_associativo'] ?? 'provisorio',
            ':jornada'       => $dados['jornada'],
            ':observacoes'   => $dados['observacoes']
        ]);
    }

    public function salvarERetornarId(array $dados): ?int
    {
        if (!$this->salvar($dados)) {
            return null;
        }

        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool {
        $sql = "UPDATE {$this->table} SET
            nome = :nome,
            cpf = :cpf,
            nascimento = :nascimento,
            telefone = :telefone,
            foto = :foto,
            user_id = :user_id,
            funcao_id = :funcao_id,
            obra_id = :obra_id,
            data_admissao = :data_admissao,
            status = :status,
            status_associativo = :status_associativo,
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
            ':user_id'       => $dados['user_id'] ?? null,
            ':funcao_id'     => $dados['funcao_id'],
            ':obra_id'       => $dados['obra_id'],
            ':data_admissao' => $dados['data_admissao'],
            ':status'        => $dados['status'],
            ':status_associativo' => $dados['status_associativo'] ?? 'provisorio',
            ':jornada'       => $dados['jornada'],
            ':observacoes'   => $dados['observacoes'],
            ':id'            => $id
        ]);
    }

    public function atualizarStatusAssociativo(int $id, string $statusAssociativo): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status_associativo = :status WHERE id = :id");
        return $stmt->execute([
            ':status' => $statusAssociativo,
            ':id' => $id,
        ]);
    }

    public function atualizarFoto(int $id, ?string $foto): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET foto = :foto WHERE id = :id");
        return $stmt->execute([
            ':foto' => $foto,
            ':id' => $id,
        ]);
    }

    public function vincularUsuario(int $id, ?int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET user_id = :user_id WHERE id = :id");
        return $stmt->execute([
            ':user_id' => $userId,
            ':id' => $id,
        ]);
    }

    public function buscarPorUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . " WHERE p.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
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
        $select = "p.*, f.nome AS funcao_nome,
            u.username AS user_username, u.email AS user_email, u.avatar AS user_avatar, u.role AS user_role,
            u.ativo AS user_ativo, u.status AS user_status";
        $from = "FROM {$this->table} p
            LEFT JOIN funcoes f ON f.id = p.funcao_id
            LEFT JOIN users u ON u.id = p.user_id";
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

        if (!empty($filters['status_associativo'])) {
            $conditions[] = 'p.status_associativo = :status_associativo';
            $params[':status_associativo'] = $filters['status_associativo'];
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

    public function listarParaCarometro(array $filters = []): array
    {
                $sql = "SELECT
                    p.id,
                    p.staff_id,
                    p.nome,
                    p.foto,
                    p.status_associativo,
                    COALESCE(ma_p.posto_graduacao, ma_c.posto_graduacao, '') AS posto_graduacao,
                    COALESCE(ma_p.nome_guerra, ma_c.nome_guerra, '') AS nome_guerra,
                    COALESCE(ma_p.ano_npor, ma_c.ano_npor, '') AS ano_npor,
                    COALESCE(ma_p.numero_militar, ma_c.numero_militar, '') AS numero_militar
                FROM {$this->table} p
                LEFT JOIN (
                    SELECT ma1.*
                    FROM membership_applications ma1
                    INNER JOIN (
                        SELECT pessoal_id, MAX(id) AS max_id
                        FROM membership_applications
                        WHERE pessoal_id IS NOT NULL
                        GROUP BY pessoal_id
                    ) latest_pid ON latest_pid.max_id = ma1.id
                ) ma_p ON ma_p.pessoal_id = p.id
                LEFT JOIN (
                    SELECT ma1.*
                    FROM membership_applications ma1
                    INNER JOIN (
                        SELECT cpf, MAX(id) AS max_id
                        FROM membership_applications
                        GROUP BY cpf
                    ) latest_cpf ON latest_cpf.max_id = ma1.id
                ) ma_c ON ma_c.cpf = p.cpf";

        $conditions = [
            "p.status = 'Ativo'",
            "p.status_associativo = 'efetivo'",
            "EXISTS (
                SELECT 1
                FROM membership_applications ma_ok
                WHERE ma_ok.status = 'aprovada'
                  AND (
                    ma_ok.pessoal_id = p.id
                    OR ma_ok.cpf = p.cpf
                  )
            )",
        ];
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $conditions[] = "(p.nome LIKE :q OR COALESCE(ma_p.nome_guerra, ma_c.nome_guerra, '') LIKE :q OR COALESCE(ma_p.numero_militar, ma_c.numero_militar, '') LIKE :q OR COALESCE(ma_p.ano_npor, ma_c.ano_npor, '') LIKE :q OR COALESCE(ma_p.posto_graduacao, ma_c.posto_graduacao, '') LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $ano = trim((string) ($filters['ano_npor'] ?? ''));
        if ($ano !== '') {
            $conditions[] = "COALESCE(ma_p.ano_npor, ma_c.ano_npor, '') = :ano_npor";
            $params[':ano_npor'] = $ano;
        }

        $where = implode(' AND ', $conditions);
        $sql .= " WHERE {$where}
                  ORDER BY
                    CAST(NULLIF(COALESCE(ma_p.ano_npor, ma_c.ano_npor, ''), '') AS UNSIGNED) DESC,
                    CAST(NULLIF(COALESCE(ma_p.numero_militar, ma_c.numero_militar, ''), '') AS UNSIGNED) ASC,
                    p.nome ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginarParaCarometro(
        int $page = 1,
        int $perPage = 20,
        array $filters = [],
        string $sortBy = 'numero_militar',
        string $sortDir = 'asc'
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $allowedSorts = [
            'nome' => 'p.nome',
            'nome_guerra' => "COALESCE(ma_p.nome_guerra, ma_c.nome_guerra, '')",
            'ano_npor' => "CAST(NULLIF(COALESCE(ma_p.ano_npor, ma_c.ano_npor, ''), '') AS UNSIGNED)",
            'numero_militar' => "CAST(NULLIF(COALESCE(ma_p.numero_militar, ma_c.numero_militar, ''), '') AS UNSIGNED)",
            'posto_graduacao' => "COALESCE(ma_p.posto_graduacao, ma_c.posto_graduacao, '')",
            'data_nascimento' => "COALESCE(p.nascimento, ma_p.data_nascimento, ma_c.data_nascimento)",
            'uf' => "COALESCE(ma_p.uf, ma_c.uf, '')",
            'cidade' => "COALESCE(ma_p.cidade, ma_c.cidade, '')",
        ];

        $sortBy = array_key_exists($sortBy, $allowedSorts) ? $sortBy : 'numero_militar';
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';
        $primaryOrder = $allowedSorts[$sortBy] . ' ' . strtoupper($sortDir);

        $from = "FROM {$this->table} p
                LEFT JOIN (
                    SELECT ma1.*
                    FROM membership_applications ma1
                    INNER JOIN (
                        SELECT pessoal_id, MAX(id) AS max_id
                        FROM membership_applications
                        WHERE pessoal_id IS NOT NULL
                        GROUP BY pessoal_id
                    ) latest_pid ON latest_pid.max_id = ma1.id
                ) ma_p ON ma_p.pessoal_id = p.id
                LEFT JOIN (
                    SELECT ma1.*
                    FROM membership_applications ma1
                    INNER JOIN (
                        SELECT cpf, MAX(id) AS max_id
                        FROM membership_applications
                        GROUP BY cpf
                    ) latest_cpf ON latest_cpf.max_id = ma1.id
                ) ma_c ON ma_c.cpf = p.cpf";

        $conditions = [
            "p.status = 'Ativo'",
            "p.status_associativo = 'efetivo'",
            "EXISTS (
                SELECT 1
                FROM membership_applications ma_ok
                WHERE ma_ok.status = 'aprovada'
                  AND (
                    ma_ok.pessoal_id = p.id
                    OR ma_ok.cpf = p.cpf
                  )
            )",
        ];
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $conditions[] = "(p.nome LIKE :q OR COALESCE(ma_p.nome_guerra, ma_c.nome_guerra, '') LIKE :q OR COALESCE(ma_p.numero_militar, ma_c.numero_militar, '') LIKE :q OR COALESCE(ma_p.ano_npor, ma_c.ano_npor, '') LIKE :q OR COALESCE(ma_p.posto_graduacao, ma_c.posto_graduacao, '') LIKE :q OR COALESCE(ma_p.cidade, ma_c.cidade, '') LIKE :q OR COALESCE(ma_p.uf, ma_c.uf, '') LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $ano = trim((string) ($filters['ano_npor'] ?? ''));
        if ($ano !== '') {
            $conditions[] = "COALESCE(ma_p.ano_npor, ma_c.ano_npor, '') = :ano_npor";
            $params[':ano_npor'] = $ano;
        }

        $where = implode(' AND ', $conditions);
        $orderBy = "{$primaryOrder},
                    CAST(NULLIF(COALESCE(ma_p.ano_npor, ma_c.ano_npor, ''), '') AS UNSIGNED) DESC,
                    p.nome ASC";

        $countStmt = $this->db->prepare("SELECT COUNT(*) {$from} WHERE {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $select = "SELECT
                    p.id,
                    p.staff_id,
                    p.nome,
                    p.foto,
                    p.status_associativo,
                    COALESCE(ma_p.posto_graduacao, ma_c.posto_graduacao, '') AS posto_graduacao,
                    COALESCE(ma_p.arma_quadro, ma_c.arma_quadro, '') AS arma_quadro,
                    COALESCE(ma_p.nome_guerra, ma_c.nome_guerra, '') AS nome_guerra,
                    COALESCE(ma_p.ano_npor, ma_c.ano_npor, '') AS ano_npor,
                    COALESCE(ma_p.numero_militar, ma_c.numero_militar, '') AS numero_militar,
                    COALESCE(ma_p.turma_npor, ma_c.turma_npor, '') AS turma_npor,
                    COALESCE(p.nascimento, ma_p.data_nascimento, ma_c.data_nascimento) AS data_nascimento,
                    COALESCE(ma_p.uf, ma_c.uf, '') AS uf,
                    COALESCE(ma_p.cidade, ma_c.cidade, '') AS cidade";

        $stmt = $this->db->prepare("{$select} {$from} WHERE {$where} ORDER BY {$orderBy} LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $fromRow = $total === 0 ? 0 : ($offset + 1);
        $toRow = min($offset + $perPage, $total);

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
                'from' => $fromRow,
                'to' => $toRow,
            ],
        ];
    }

    public function listarAnosCarometro(): array
    {
        $stmt = $this->db->query(
            "SELECT anos.ano_npor
             FROM (
                SELECT DISTINCT COALESCE(ma_p.ano_npor, ma_c.ano_npor, '') AS ano_npor
                FROM {$this->table} p
                LEFT JOIN (
                    SELECT ma1.*
                    FROM membership_applications ma1
                    INNER JOIN (
                        SELECT pessoal_id, MAX(id) AS max_id
                        FROM membership_applications
                        WHERE pessoal_id IS NOT NULL
                        GROUP BY pessoal_id
                    ) latest_pid ON latest_pid.max_id = ma1.id
                ) ma_p ON ma_p.pessoal_id = p.id
                LEFT JOIN (
                    SELECT ma1.*
                    FROM membership_applications ma1
                    INNER JOIN (
                        SELECT cpf, MAX(id) AS max_id
                        FROM membership_applications
                        GROUP BY cpf
                    ) latest_cpf ON latest_cpf.max_id = ma1.id
                ) ma_c ON ma_c.cpf = p.cpf
                WHERE p.status = 'Ativo'
                  AND p.status_associativo = 'efetivo'
                  AND EXISTS (
                        SELECT 1
                        FROM membership_applications ma_ok
                        WHERE ma_ok.status = 'aprovada'
                          AND (
                            ma_ok.pessoal_id = p.id
                            OR ma_ok.cpf = p.cpf
                          )
                    )
                  AND COALESCE(ma_p.ano_npor, ma_c.ano_npor, '') <> ''
             ) anos
             ORDER BY CAST(anos.ano_npor AS UNSIGNED) DESC"
        );

        return array_map(
            static fn(array $row): string => (string) $row['ano_npor'],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function listarAniversariantesDoDia(?string $monthDay = null, int $limit = 12): array
    {
        $monthDay = $monthDay ?: date('m-d');
        $limit = max(1, min($limit, 50));

                $sql = "SELECT
                    p.id,
                    p.staff_id,
                    p.nome,
                    p.foto,
                    p.nascimento,
                    p.telefone,
                    u.avatar AS user_avatar,
                    u.email AS user_email,
                    COALESCE(ma_p.nome_guerra, ma_c.nome_guerra, '') AS nome_guerra,
                    COALESCE(ma_p.ano_npor, ma_c.ano_npor, '') AS ano_npor,
                    COALESCE(ma_p.numero_militar, ma_c.numero_militar, '') AS numero_militar,
                    COALESCE(ma_p.cidade, ma_c.cidade, '') AS cidade,
                    COALESCE(ma_p.uf, ma_c.uf, '') AS uf
                FROM {$this->table} p
                LEFT JOIN users u ON u.id = p.user_id
                LEFT JOIN (
                    SELECT ma1.*
                    FROM membership_applications ma1
                    INNER JOIN (
                        SELECT pessoal_id, MAX(id) AS max_id
                        FROM membership_applications
                        WHERE pessoal_id IS NOT NULL
                        GROUP BY pessoal_id
                    ) latest_pid ON latest_pid.max_id = ma1.id
                ) ma_p ON ma_p.pessoal_id = p.id
                LEFT JOIN (
                    SELECT ma1.*
                    FROM membership_applications ma1
                    INNER JOIN (
                        SELECT cpf, MAX(id) AS max_id
                        FROM membership_applications
                        GROUP BY cpf
                    ) latest_cpf ON latest_cpf.max_id = ma1.id
                ) ma_c ON ma_c.cpf = p.cpf
                WHERE p.status = 'Ativo'
                  AND p.status_associativo = 'efetivo'
                  AND p.nascimento IS NOT NULL
                  AND DATE_FORMAT(p.nascimento, '%m-%d') = :month_day
                  AND EXISTS (
                        SELECT 1
                        FROM membership_applications ma_ok
                        WHERE ma_ok.status = 'aprovada'
                          AND (
                            ma_ok.pessoal_id = p.id
                            OR ma_ok.cpf = p.cpf
                          )
                    )
                ORDER BY
                    DAY(p.nascimento) ASC,
                    MONTH(p.nascimento) ASC,
                    p.nome ASC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':month_day', $monthDay, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
