<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_03_000004_add_membership_complement_status extends Migration
{
    public function up(): void
    {
        if ($this->tableExists('membership_applications')) {
            $this->execute("ALTER TABLE membership_applications MODIFY COLUMN status ENUM('pendente','complementacao','aprovada','rejeitada') NOT NULL DEFAULT 'pendente'");
        }
    }

    public function down(): void
    {
        if ($this->tableExists('membership_applications')) {
            $this->execute("ALTER TABLE membership_applications MODIFY COLUMN status ENUM('pendente','aprovada','rejeitada') NOT NULL DEFAULT 'pendente'");
        }
    }
}
