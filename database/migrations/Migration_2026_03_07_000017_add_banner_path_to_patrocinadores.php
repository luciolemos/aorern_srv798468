<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_07_000017_add_banner_path_to_patrocinadores extends Migration
{
    private const TABLE = 'patrocinadores';

    public function up(): void
    {
        if (!$this->tableExists(self::TABLE) || $this->columnExists(self::TABLE, 'banner_path')) {
            return;
        }

        $this->execute(
            "ALTER TABLE " . self::TABLE . "
             ADD COLUMN banner_path VARCHAR(255) NULL AFTER logo_path"
        );
    }

    public function down(): void
    {
        if (!$this->tableExists(self::TABLE) || !$this->columnExists(self::TABLE, 'banner_path')) {
            return;
        }

        $this->execute(
            "ALTER TABLE " . self::TABLE . "
             DROP COLUMN banner_path"
        );
    }
}

