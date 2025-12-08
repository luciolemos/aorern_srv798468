<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2025_12_04_144941_rename_foto_to_avatar_in_pessoal extends Migration
{
    /**
     * Executa a migration
     */
    public function up(): void
    {
        // Renomeia coluna foto para avatar
        $sql = "ALTER TABLE pessoal CHANGE foto avatar VARCHAR(255) NULL";
        $this->execute($sql);
    }

    /**
     * Reverte a migration
     */
    public function down(): void
    {
        // Reverte renomeação
        $sql = "ALTER TABLE pessoal CHANGE avatar foto VARCHAR(255) NULL";
        $this->execute($sql);
    }
}
