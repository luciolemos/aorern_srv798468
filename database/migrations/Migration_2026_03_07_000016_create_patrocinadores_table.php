<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_07_000016_create_patrocinadores_table extends Migration
{
    private const TABLE = 'patrocinadores';

    public function up(): void
    {
        if ($this->tableExists(self::TABLE)) {
            return;
        }

        $this->execute(
            "CREATE TABLE " . self::TABLE . " (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(160) NOT NULL,
                telefone VARCHAR(32) NULL,
                whatsapp VARCHAR(24) NULL,
                email VARCHAR(160) NULL,
                site VARCHAR(255) NULL,
                descricao_curta VARCHAR(500) NULL,
                logo_path VARCHAR(255) NOT NULL,
                ordem INT UNSIGNED NOT NULL DEFAULT 0,
                ativo TINYINT(1) NOT NULL DEFAULT 1,
                created_by INT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_patrocinadores_ativo_ordem (ativo, ordem),
                INDEX idx_patrocinadores_nome (nome),
                CONSTRAINT fk_patrocinadores_created_by
                    FOREIGN KEY (created_by) REFERENCES users(id)
                    ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function down(): void
    {
        if (!$this->tableExists(self::TABLE)) {
            return;
        }

        $this->execute("DROP TABLE " . self::TABLE);
    }
}

