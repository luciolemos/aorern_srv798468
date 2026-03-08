<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_07_000018_add_exibir_texto_banner_to_patrocinadores extends Migration
{
    private const TABLE = 'patrocinadores';

    public function up(): void
    {
        if (!$this->tableExists(self::TABLE) || $this->columnExists(self::TABLE, 'exibir_texto_banner')) {
            return;
        }

        $this->execute(
            "ALTER TABLE " . self::TABLE . "
             ADD COLUMN exibir_texto_banner TINYINT(1) NOT NULL DEFAULT 1 AFTER banner_path"
        );
    }

    public function down(): void
    {
        if (!$this->tableExists(self::TABLE) || !$this->columnExists(self::TABLE, 'exibir_texto_banner')) {
            return;
        }

        $this->execute(
            "ALTER TABLE " . self::TABLE . "
             DROP COLUMN exibir_texto_banner"
        );
    }
}

