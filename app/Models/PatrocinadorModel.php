<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class PatrocinadorModel
{
    private PDO $db;
    private string $table = 'patrocinadores';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function paginar(int $page = 1, ?int $perPage = 10, array $filters = []): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['q'])) {
            $conditions[] = '(nome LIKE :q OR telefone LIKE :q OR email LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        if ($filters['ativo'] !== null && $filters['ativo'] !== '') {
            $conditions[] = 'ativo = :ativo';
            $params[':ativo'] = (int) $filters['ativo'];
        }

        return Paginator::paginate(
            $this->db,
            'id, nome, telefone, whatsapp, email, site, instagram, descricao_curta, logo_path, banner_path, exibir_texto_banner, texto_cor_titulo, texto_cor_descricao, icone_cor, ordem, ativo, created_at, updated_at',
            "FROM {$this->table}",
            implode(' AND ', $conditions),
            'ordem ASC, id DESC',
            $params,
            $page,
            $perPage
        );
    }

    public function listarAtivos(int $limit = 20): array
    {
        $sql = "SELECT id, nome, telefone, whatsapp, email, site, instagram, descricao_curta, logo_path, banner_path, exibir_texto_banner, texto_cor_titulo, texto_cor_descricao, icone_cor, ordem
                FROM {$this->table}
                WHERE ativo = 1
                ORDER BY ordem ASC, id DESC
                LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
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
                nome, telefone, whatsapp, email, site, instagram, descricao_curta, logo_path, banner_path, exibir_texto_banner, texto_cor_titulo, texto_cor_descricao, icone_cor, ordem, ativo, created_by
            ) VALUES (
                :nome, :telefone, :whatsapp, :email, :site, :instagram, :descricao_curta, :logo_path, :banner_path, :exibir_texto_banner, :texto_cor_titulo, :texto_cor_descricao, :icone_cor, :ordem, :ativo, :created_by
            )"
        );

        return $stmt->execute($dados);
    }

    public function atualizar(int $id, array $dados): bool
    {
        $dados['id'] = $id;
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET
                nome = :nome,
                telefone = :telefone,
                whatsapp = :whatsapp,
                email = :email,
                site = :site,
                instagram = :instagram,
                descricao_curta = :descricao_curta,
                logo_path = :logo_path,
                banner_path = :banner_path,
                exibir_texto_banner = :exibir_texto_banner,
                texto_cor_titulo = :texto_cor_titulo,
                texto_cor_descricao = :texto_cor_descricao,
                icone_cor = :icone_cor,
                ordem = :ordem,
                ativo = :ativo,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id"
        );

        return $stmt->execute($dados);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
