<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2025_12_05_000001_update_staff_id_prefix extends Migration
{
    /**
     * Aumenta o tamanho do staff_id e substitui o prefixo FUNC- por FIREMAN-
     */
    public function up(): void
    {
        $this->execute("ALTER TABLE pessoal MODIFY staff_id VARCHAR(30) NULL");

        $sql = "UPDATE pessoal 
                SET staff_id = REPLACE(staff_id, 'FUNC-', 'FIREMAN-')
                WHERE staff_id LIKE 'FUNC-%'";
        $this->execute($sql);
    }

    /**
     * Reverte prefixo e tamanho da coluna
     */
    public function down(): void
    {
        $sql = "UPDATE pessoal 
                SET staff_id = REPLACE(staff_id, 'FIREMAN-', 'FUNC-')
                WHERE staff_id LIKE 'FIREMAN-%'";
        $this->execute($sql);

        $this->execute("ALTER TABLE pessoal MODIFY staff_id VARCHAR(20) NULL");
    }
}
