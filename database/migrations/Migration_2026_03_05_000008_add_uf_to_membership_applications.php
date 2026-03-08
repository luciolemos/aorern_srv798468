<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_05_000008_add_uf_to_membership_applications extends Migration
{
    public function up(): void
    {
        if (!$this->tableExists('membership_applications')) {
            return;
        }

        if (!$this->columnExists('membership_applications', 'uf')) {
            $this->execute("ALTER TABLE membership_applications ADD COLUMN uf CHAR(2) NULL AFTER cidade");
        }

        if (!$this->indexExists('membership_applications', 'idx_membership_applications_uf')) {
            $this->execute("ALTER TABLE membership_applications ADD INDEX idx_membership_applications_uf (uf)");
        }

        $this->execute(
            "UPDATE membership_applications
             SET
                uf = UPPER(TRIM(SUBSTRING_INDEX(cidade, '/', -1))),
                cidade = TRIM(SUBSTRING_INDEX(cidade, '/', 1))
             WHERE (uf IS NULL OR uf = '')
               AND cidade IS NOT NULL
               AND cidade REGEXP '/[A-Za-z]{2}$'"
        );

        $this->execute(
            "UPDATE membership_applications
             SET uf = NULL
             WHERE uf IS NOT NULL
               AND uf <> ''
               AND uf NOT REGEXP '^[A-Za-z]{2}$'"
        );

        $this->execute(
            "UPDATE membership_applications
             SET uf = UPPER(uf)
             WHERE uf REGEXP '^[A-Za-z]{2}$'"
        );
    }

    public function down(): void
    {
        if (!$this->tableExists('membership_applications')) {
            return;
        }

        if ($this->columnExists('membership_applications', 'uf')) {
            $this->execute(
                "UPDATE membership_applications
                 SET cidade = TRIM(
                    CASE
                        WHEN cidade IS NULL OR cidade = '' THEN ''
                        WHEN uf IS NULL OR uf = '' THEN cidade
                        ELSE CONCAT(cidade, '/', UPPER(uf))
                    END
                 )"
            );
        }

        if ($this->indexExists('membership_applications', 'idx_membership_applications_uf')) {
            $this->execute("ALTER TABLE membership_applications DROP INDEX idx_membership_applications_uf");
        }

        if ($this->columnExists('membership_applications', 'uf')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN uf");
        }
    }
}
