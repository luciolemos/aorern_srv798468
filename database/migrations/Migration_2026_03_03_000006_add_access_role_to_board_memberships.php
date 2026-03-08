<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_03_000006_add_access_role_to_board_memberships extends Migration
{
    public function up(): void
    {
        if ($this->tableExists('board_memberships') && !$this->columnExists('board_memberships', 'access_role')) {
            $this->execute("ALTER TABLE board_memberships ADD COLUMN access_role ENUM('operador','gerente') NULL AFTER is_active");
        }
    }

    public function down(): void
    {
        if ($this->tableExists('board_memberships') && $this->columnExists('board_memberships', 'access_role')) {
            $this->execute("ALTER TABLE board_memberships DROP COLUMN access_role");
        }
    }
}
