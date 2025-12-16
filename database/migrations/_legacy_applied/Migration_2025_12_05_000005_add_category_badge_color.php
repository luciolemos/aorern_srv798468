<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2025_12_05_000005_add_category_badge_color extends Migration
{
    public function up(): void
    {
        if (!$this->columnExists('categorias_posts', 'badge_color')) {
            $this->execute("ALTER TABLE categorias_posts ADD COLUMN badge_color VARCHAR(7) NOT NULL DEFAULT '#df6301' AFTER nome");
        }
    }

    public function down(): void
    {
        if ($this->columnExists('categorias_posts', 'badge_color')) {
            $this->execute("ALTER TABLE categorias_posts DROP COLUMN badge_color");
        }
    }
}
