<?php

namespace App\Database\Migrations;

use App\Database\Migration;
use RuntimeException;

class Migration_2025_12_15_000000_create_schema_baseline extends Migration
{
    public function shouldRun(string $direction): bool
    {
        if ($direction === 'up') {
            // Se já existir a tabela users, assumimos que o schema base está aplicado
            return !$this->tableExists('users');
        }

        return true;
    }

    public function up(): void
    {
        $schemaPath = dirname(__DIR__, 2) . '/sql/schema.sql';

        if (!file_exists($schemaPath)) {
            throw new RuntimeException('Arquivo sql/schema.sql não encontrado para compor o baseline.');
        }

        $sql = file_get_contents($schemaPath);
        if ($sql === false || trim($sql) === '') {
            throw new RuntimeException('Arquivo sql/schema.sql está vazio, impossível aplicar baseline.');
        }

        $this->db->exec($sql);
    }

    public function down(): void
    {
        throw new RuntimeException('Baseline não pode ser revertida automaticamente.');
    }
}
