<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_05_000009_add_cam_rg_to_membership_applications extends Migration
{
    public function up(): void
    {
        if (!$this->tableExists('membership_applications')) {
            return;
        }

        if (!$this->columnExists('membership_applications', 'cam')) {
            $this->execute("ALTER TABLE membership_applications ADD COLUMN cam VARCHAR(40) NULL AFTER cpf");
        }

        if (!$this->columnExists('membership_applications', 'rg')) {
            $this->execute("ALTER TABLE membership_applications ADD COLUMN rg VARCHAR(40) NULL AFTER cam");
        }
    }

    public function down(): void
    {
        if (!$this->tableExists('membership_applications')) {
            return;
        }

        if ($this->columnExists('membership_applications', 'rg')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN rg");
        }

        if ($this->columnExists('membership_applications', 'cam')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN cam");
        }
    }
}
