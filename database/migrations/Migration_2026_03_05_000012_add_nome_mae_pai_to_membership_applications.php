<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_05_000012_add_nome_mae_pai_to_membership_applications extends Migration
{
    public function up(): void
    {
        if (!$this->tableExists('membership_applications')) {
            return;
        }

        if (!$this->columnExists('membership_applications', 'nome_mae')) {
            $this->execute("ALTER TABLE membership_applications ADD COLUMN nome_mae VARCHAR(160) NULL AFTER nome_completo");
        }

        if (!$this->columnExists('membership_applications', 'nome_pai')) {
            $this->execute("ALTER TABLE membership_applications ADD COLUMN nome_pai VARCHAR(160) NULL AFTER nome_mae");
        }
    }

    public function down(): void
    {
        if (!$this->tableExists('membership_applications')) {
            return;
        }

        if ($this->columnExists('membership_applications', 'nome_pai')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN nome_pai");
        }

        if ($this->columnExists('membership_applications', 'nome_mae')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN nome_mae");
        }
    }
}
