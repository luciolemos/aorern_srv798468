<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_05_000013_expand_associative_status_options extends Migration
{
    public function up(): void
    {
        $enum = "ENUM('provisorio','efetivo','honorario','fundador','benemerito','veterano','aluno')";

        if ($this->tableExists('membership_applications') && $this->columnExists('membership_applications', 'status_associativo')) {
            $this->execute("ALTER TABLE membership_applications MODIFY COLUMN status_associativo {$enum} NOT NULL DEFAULT 'provisorio'");
        }

        if ($this->tableExists('pessoal') && $this->columnExists('pessoal', 'status_associativo')) {
            $this->execute("ALTER TABLE pessoal MODIFY COLUMN status_associativo {$enum} NOT NULL DEFAULT 'provisorio'");
        }
    }

    public function down(): void
    {
        if ($this->tableExists('membership_applications') && $this->columnExists('membership_applications', 'status_associativo')) {
            $this->execute("UPDATE membership_applications SET status_associativo = 'provisorio' WHERE status_associativo IN ('fundador','benemerito','veterano','aluno')");
            $this->execute("ALTER TABLE membership_applications MODIFY COLUMN status_associativo ENUM('provisorio','efetivo','honorario') NOT NULL DEFAULT 'provisorio'");
        }

        if ($this->tableExists('pessoal') && $this->columnExists('pessoal', 'status_associativo')) {
            $this->execute("UPDATE pessoal SET status_associativo = 'provisorio' WHERE status_associativo IN ('fundador','benemerito','veterano','aluno')");
            $this->execute("ALTER TABLE pessoal MODIFY COLUMN status_associativo ENUM('provisorio','efetivo','honorario') NOT NULL DEFAULT 'provisorio'");
        }
    }
}
