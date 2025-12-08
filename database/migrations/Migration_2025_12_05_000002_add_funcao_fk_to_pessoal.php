<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2025_12_05_000002_add_funcao_fk_to_pessoal extends Migration
{
    public function up(): void
    {
        $hadLegacyColumn = $this->columnExists('pessoal', 'funcao');

        if (!$this->columnExists('pessoal', 'funcao_id')) {
            $after = $hadLegacyColumn ? 'funcao' : 'obra_id';
            $this->execute("ALTER TABLE pessoal ADD COLUMN funcao_id INT NULL AFTER {$after}");
        }

        if ($hadLegacyColumn) {
            // Garante que todas as funções existentes estejam registradas na tabela funcoes
            $stmt = $this->db->query("
                SELECT DISTINCT p.funcao
                FROM pessoal p
                LEFT JOIN funcoes f ON f.nome COLLATE utf8mb4_unicode_ci = p.funcao COLLATE utf8mb4_unicode_ci
                WHERE p.funcao IS NOT NULL AND p.funcao <> '' AND f.id IS NULL
            ");

            $missing = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            if (!empty($missing)) {
                $insert = $this->db->prepare("INSERT INTO funcoes (staff_id, nome) VALUES (:staff_id, :nome)");
                foreach ($missing as $nome) {
                    $staffId = 'FUNC-' . date('YmdHis') . '-' . substr(uniqid(), -4);
                    $insert->execute([
                        ':staff_id' => $staffId,
                        ':nome'     => $nome
                    ]);
                }
            }

            // Preenche funcao_id com base no nome atual
            $this->execute("
                UPDATE pessoal p
                INNER JOIN funcoes f ON f.nome COLLATE utf8mb4_unicode_ci = p.funcao COLLATE utf8mb4_unicode_ci
                SET p.funcao_id = f.id
            ");

            $this->execute("ALTER TABLE pessoal DROP COLUMN funcao");
        }

        $this->execute("ALTER TABLE pessoal MODIFY funcao_id INT NOT NULL");

        if ($this->hasReferencePrivilege()) {
            $this->execute("ALTER TABLE pessoal ADD CONSTRAINT fk_pessoal_funcao FOREIGN KEY (funcao_id) REFERENCES funcoes(id)");
        }
    }

    public function down(): void
    {
        if ($this->hasReferencePrivilege() && $this->fkExists()) {
            $this->execute("ALTER TABLE pessoal DROP FOREIGN KEY fk_pessoal_funcao");
        }

        if (!$this->columnExists('pessoal', 'funcao')) {
            $this->execute("ALTER TABLE pessoal ADD COLUMN funcao VARCHAR(50) NOT NULL AFTER funcao_id");

            $this->execute("
                UPDATE pessoal p
                INNER JOIN funcoes f ON f.id = p.funcao_id
                SET p.funcao = f.nome
            ");
        }

        if ($this->columnExists('pessoal', 'funcao_id')) {
            $this->execute("ALTER TABLE pessoal DROP COLUMN funcao_id");
        }
    }

    private function fkExists(): bool
    {
        $stmt = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_NAME = 'pessoal' AND CONSTRAINT_NAME = 'fk_pessoal_funcao'");
        return (bool)$stmt->fetch();
    }

    private function hasReferencePrivilege(): bool
    {
        try {
            $this->execute("ALTER TABLE pessoal ADD INDEX tmp_funcao_id_idx (funcao_id)");
            $this->execute("ALTER TABLE pessoal DROP INDEX tmp_funcao_id_idx");
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }
}
