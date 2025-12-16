<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2025_12_15_000001_add_closed_at_to_livro_ocorrencias extends Migration
{
    public function up(): void
    {
        if (!$this->columnExists('livro_ocorrencias', 'closed_at')) {
            $this->execute("ALTER TABLE livro_ocorrencias ADD COLUMN closed_at DATETIME NULL AFTER data_ocorrencia");
        }

        if (!$this->indexExists('livro_ocorrencias', 'idx_livro_closed_at')) {
            $this->execute("ALTER TABLE livro_ocorrencias ADD INDEX idx_livro_closed_at (closed_at)");
        }

        // Normalize existing records so status reflects the presence of a closing date
        $this->execute("UPDATE livro_ocorrencias SET closed_at = atualizado_em WHERE status IN ('concluida','arquivada') AND closed_at IS NULL");
        $this->execute("UPDATE livro_ocorrencias SET status = CASE WHEN closed_at IS NOT NULL THEN 'concluida' ELSE 'aberta' END");

        // Restrict the ENUM to the new lifecycle (aberta/concluida)
        $this->execute("ALTER TABLE livro_ocorrencias MODIFY status ENUM('aberta','concluida') DEFAULT 'aberta'");
    }

    public function down(): void
    {
        // Restore wider ENUM definition
        $this->execute("ALTER TABLE livro_ocorrencias MODIFY status ENUM('aberta','em_andamento','concluida','arquivada') DEFAULT 'aberta'");

        if ($this->indexExists('livro_ocorrencias', 'idx_livro_closed_at')) {
            $this->execute("ALTER TABLE livro_ocorrencias DROP INDEX idx_livro_closed_at");
        }

        if ($this->columnExists('livro_ocorrencias', 'closed_at')) {
            $this->execute("ALTER TABLE livro_ocorrencias DROP COLUMN closed_at");
        }
    }
}
