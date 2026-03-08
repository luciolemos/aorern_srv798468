<?php

namespace App\Database\Migrations;

use App\Database\Migration;
use RuntimeException;

class Migration_2026_03_07_000015_add_unique_term_funcao_to_board_memberships extends Migration
{
    private const TABLE = 'board_memberships';
    private const INDEX = 'uq_board_memberships_term_funcao';

    public function up(): void
    {
        if (
            !$this->tableExists(self::TABLE)
            || !$this->columnExists(self::TABLE, 'term_id')
            || !$this->columnExists(self::TABLE, 'funcao_id')
        ) {
            return;
        }

        if ($this->indexExists(self::TABLE, self::INDEX)) {
            return;
        }

        $duplicate = $this->db->query(
            "SELECT term_id, funcao_id, COUNT(*) AS total
             FROM " . self::TABLE . "
             WHERE term_id IS NOT NULL AND funcao_id IS NOT NULL
             GROUP BY term_id, funcao_id
             HAVING COUNT(*) > 1
             LIMIT 1"
        )->fetch(\PDO::FETCH_ASSOC);

        if ($duplicate) {
            throw new RuntimeException(
                'Não foi possível aplicar a chave única em board_memberships: existem funções duplicadas no mesmo mandato.'
            );
        }

        $this->execute(
            "ALTER TABLE " . self::TABLE . "
             ADD CONSTRAINT " . self::INDEX . " UNIQUE (term_id, funcao_id)"
        );
    }

    public function down(): void
    {
        if (!$this->tableExists(self::TABLE) || !$this->indexExists(self::TABLE, self::INDEX)) {
            return;
        }

        $this->execute("ALTER TABLE " . self::TABLE . " DROP INDEX " . self::INDEX);
    }
}

