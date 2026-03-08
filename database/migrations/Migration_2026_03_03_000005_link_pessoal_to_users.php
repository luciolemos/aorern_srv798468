<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_03_000005_link_pessoal_to_users extends Migration
{
    public function up(): void
    {
        if ($this->tableExists('pessoal') && !$this->columnExists('pessoal', 'user_id')) {
            $this->execute("ALTER TABLE pessoal ADD COLUMN user_id INT UNSIGNED NULL AFTER foto");
            $this->execute("ALTER TABLE pessoal ADD KEY idx_pessoal_user (user_id)");
            $this->execute("ALTER TABLE pessoal ADD CONSTRAINT fk_pessoal_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
        }
    }

    public function down(): void
    {
        if ($this->tableExists('pessoal') && $this->columnExists('pessoal', 'user_id')) {
            $this->execute("ALTER TABLE pessoal DROP FOREIGN KEY fk_pessoal_user");
            $this->execute("ALTER TABLE pessoal DROP INDEX idx_pessoal_user");
            $this->execute("ALTER TABLE pessoal DROP COLUMN user_id");
        }
    }
}
