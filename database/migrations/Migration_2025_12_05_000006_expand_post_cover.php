<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2025_12_05_000006_expand_post_cover extends Migration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE posts MODIFY capa_url VARCHAR(512) NULL");
    }

    public function down(): void
    {
        $this->execute("ALTER TABLE posts MODIFY capa_url VARCHAR(255) NULL");
    }
}
