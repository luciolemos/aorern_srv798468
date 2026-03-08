<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_03_000003_extend_membership_and_associate_status extends Migration
{
    public function up(): void
    {
        if ($this->tableExists('membership_applications')) {
            if (!$this->columnExists('membership_applications', 'documentos_json')) {
                $this->execute("ALTER TABLE membership_applications ADD COLUMN documentos_json LONGTEXT NULL AFTER avatar");
            }

            if (!$this->columnExists('membership_applications', 'status_associativo')) {
                $this->execute("ALTER TABLE membership_applications ADD COLUMN status_associativo ENUM('provisorio','efetivo','honorario') NOT NULL DEFAULT 'provisorio' AFTER status");
            }
        }

        if ($this->tableExists('pessoal') && !$this->columnExists('pessoal', 'status_associativo')) {
            $this->execute("ALTER TABLE pessoal ADD COLUMN status_associativo ENUM('provisorio','efetivo','honorario') NOT NULL DEFAULT 'provisorio' AFTER status");
        }
    }

    public function down(): void
    {
        if ($this->tableExists('membership_applications') && $this->columnExists('membership_applications', 'documentos_json')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN documentos_json");
        }

        if ($this->tableExists('membership_applications') && $this->columnExists('membership_applications', 'status_associativo')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN status_associativo");
        }

        if ($this->tableExists('pessoal') && $this->columnExists('pessoal', 'status_associativo')) {
            $this->execute("ALTER TABLE pessoal DROP COLUMN status_associativo");
        }
    }
}
