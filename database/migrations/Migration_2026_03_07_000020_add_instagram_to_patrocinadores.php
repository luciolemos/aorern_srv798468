<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_07_000020_add_instagram_to_patrocinadores extends Migration
{
    private const TABLE = 'patrocinadores';

    public function up(): void
    {
        if (!$this->tableExists(self::TABLE) || $this->columnExists(self::TABLE, 'instagram')) {
            return;
        }

        $this->execute(
            "ALTER TABLE " . self::TABLE . "
             ADD COLUMN instagram VARCHAR(160) NULL AFTER site"
        );
    }

    public function down(): void
    {
        if (!$this->tableExists(self::TABLE) || !$this->columnExists(self::TABLE, 'instagram')) {
            return;
        }

        $this->execute(
            "ALTER TABLE " . self::TABLE . "
             DROP COLUMN instagram"
        );
    }
}

