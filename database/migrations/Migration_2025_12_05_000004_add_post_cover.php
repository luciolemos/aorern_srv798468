<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2025_12_05_000004_add_post_cover extends Migration
{
    public function up(): void
    {
        if (!$this->columnExists('posts', 'capa_url')) {
            $this->execute("ALTER TABLE posts ADD COLUMN capa_url VARCHAR(255) NULL AFTER slug");
        }
    }

    public function down(): void
    {
        if ($this->columnExists('posts', 'capa_url')) {
            $this->execute("ALTER TABLE posts DROP COLUMN capa_url");
        }
    }
}
