<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_05_000010_add_posto_graduacao_to_membership_applications extends Migration
{
    public function up(): void
    {
        if (!$this->tableExists('membership_applications')) {
            return;
        }

        if (!$this->columnExists('membership_applications', 'posto_graduacao')) {
            $this->execute("ALTER TABLE membership_applications ADD COLUMN posto_graduacao VARCHAR(60) NULL AFTER ano_npor");
        }
    }

    public function down(): void
    {
        if (!$this->tableExists('membership_applications')) {
            return;
        }

        if ($this->columnExists('membership_applications', 'posto_graduacao')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN posto_graduacao");
        }
    }
}
