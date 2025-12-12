<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Paginator;
use PDO;

class DogModel
{
    private PDO $db;
    private string $table = 'dogs';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function paginarComFiltros(
        int $page = 1,
        ?int $perPage = 12,
        ?string $busca = null,
        ?int $breedId = null,
        ?string $status = null,
        ?string $function = null,
        ?string $sex = null
    ): array {
        $select = 'd.*, b.name AS breed_name, b.slug AS breed_slug';
        $from = "FROM {$this->table} d LEFT JOIN dog_breeds b ON b.id = d.breed_id";
        $conditions = [];
        $params = [];

        if ($busca) {
            $conditions[] = '(d.name LIKE :busca OR d.slug LIKE :busca)';
            $params[':busca'] = '%' . $busca . '%';
        }

        if ($breedId) {
            $conditions[] = 'd.breed_id = :breed_id';
            $params[':breed_id'] = $breedId;
        }

        if ($status) {
            $conditions[] = 'd.status = :status';
            $params[':status'] = $status;
        }

        if ($function) {
            $conditions[] = 'd.operational_function = :function';
            $params[':function'] = $function;
        }

        if ($sex) {
            $conditions[] = 'd.sex = :sex';
            $params[':sex'] = $sex;
        }

        $where = implode(' AND ', $conditions);

        return Paginator::paginate(
            $this->db,
            $select,
            $from,
            $where,
            'd.name ASC',
            $params,
            $page,
            $perPage
        );
    }

    public function listarTodos(): array
    {
        $stmt = $this->db->query("SELECT d.*, b.name AS breed_name FROM {$this->table} d LEFT JOIN dog_breeds b ON b.id = d.breed_id ORDER BY d.name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function slugExiste(string $slug, ?int $ignoreId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
        $params = [':slug' => $slug];

        if ($ignoreId !== null) {
            $sql .= " AND id != :ignore";
            $params[':ignore'] = $ignoreId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function criar(array $dados): bool
    {
        $sql = "INSERT INTO {$this->table}
            (name, slug, breed_id, birth_date, birth_city, birth_state, weight_kg, sex,
             operational_function, training_phase, avatar, status, notes, identifying_marks)
            VALUES
            (:name, :slug, :breed_id, :birth_date, :birth_city, :birth_state, :weight_kg, :sex,
             :operational_function, :training_phase, :avatar, :status, :notes, :identifying_marks)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $dados['name'],
            ':slug' => $dados['slug'],
            ':breed_id' => $dados['breed_id'],
            ':birth_date' => $dados['birth_date'],
            ':birth_city' => $dados['birth_city'],
            ':birth_state' => $dados['birth_state'],
            ':weight_kg' => $dados['weight_kg'],
            ':sex' => $dados['sex'],
            ':operational_function' => $dados['operational_function'],
            ':training_phase' => $dados['training_phase'],
            ':avatar' => $dados['avatar'],
            ':status' => $dados['status'],
            ':notes' => $dados['notes'],
            ':identifying_marks' => $dados['identifying_marks'],
        ]);
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE {$this->table}
            SET name = :name,
                slug = :slug,
                breed_id = :breed_id,
                birth_date = :birth_date,
                birth_city = :birth_city,
                birth_state = :birth_state,
                weight_kg = :weight_kg,
                sex = :sex,
                operational_function = :operational_function,
                training_phase = :training_phase,
                avatar = :avatar,
                status = :status,
                notes = :notes,
                identifying_marks = :identifying_marks
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $dados['name'],
            ':slug' => $dados['slug'],
            ':breed_id' => $dados['breed_id'],
            ':birth_date' => $dados['birth_date'],
            ':birth_city' => $dados['birth_city'],
            ':birth_state' => $dados['birth_state'],
            ':weight_kg' => $dados['weight_kg'],
            ':sex' => $dados['sex'],
            ':operational_function' => $dados['operational_function'],
            ':training_phase' => $dados['training_phase'],
            ':avatar' => $dados['avatar'],
            ':status' => $dados['status'],
            ':notes' => $dados['notes'],
            ':identifying_marks' => $dados['identifying_marks'],
            ':id' => $id,
        ]);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
