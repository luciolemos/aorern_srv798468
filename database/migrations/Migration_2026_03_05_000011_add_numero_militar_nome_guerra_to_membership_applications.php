<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_05_000011_add_numero_militar_nome_guerra_to_membership_applications extends Migration
{
    public function up(): void
    {
        if (!$this->tableExists('membership_applications')) {
            return;
        }

        if (!$this->columnExists('membership_applications', 'numero_militar')) {
            $this->execute("ALTER TABLE membership_applications ADD COLUMN numero_militar VARCHAR(30) NULL AFTER posto_graduacao");
        }

        if (!$this->columnExists('membership_applications', 'nome_guerra')) {
            $this->execute("ALTER TABLE membership_applications ADD COLUMN nome_guerra VARCHAR(60) NULL AFTER numero_militar");
        }
    }

    public function down(): void
    {
        if (!$this->tableExists('membership_applications')) {
            return;
        }

        if ($this->columnExists('membership_applications', 'nome_guerra')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN nome_guerra");
        }

        if ($this->columnExists('membership_applications', 'numero_militar')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN numero_militar");
        }
    }
}
