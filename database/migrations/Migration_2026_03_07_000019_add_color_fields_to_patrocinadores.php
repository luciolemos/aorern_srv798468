<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_07_000019_add_color_fields_to_patrocinadores extends Migration
{
    private const TABLE = 'patrocinadores';

    public function up(): void
    {
        if (!$this->tableExists(self::TABLE)) {
            return;
        }

        if (!$this->columnExists(self::TABLE, 'texto_cor_titulo')) {
            $this->execute(
                "ALTER TABLE " . self::TABLE . "
                 ADD COLUMN texto_cor_titulo CHAR(7) NOT NULL DEFAULT '#FFFFFF' AFTER exibir_texto_banner"
            );
        }

        if (!$this->columnExists(self::TABLE, 'texto_cor_descricao')) {
            $this->execute(
                "ALTER TABLE " . self::TABLE . "
                 ADD COLUMN texto_cor_descricao CHAR(7) NOT NULL DEFAULT '#FFFFFF' AFTER texto_cor_titulo"
            );
        }

        if (!$this->columnExists(self::TABLE, 'icone_cor')) {
            $this->execute(
                "ALTER TABLE " . self::TABLE . "
                 ADD COLUMN icone_cor CHAR(7) NOT NULL DEFAULT '#FFFFFF' AFTER texto_cor_descricao"
            );
        }
    }

    public function down(): void
    {
        if (!$this->tableExists(self::TABLE)) {
            return;
        }

        if ($this->columnExists(self::TABLE, 'icone_cor')) {
            $this->execute("ALTER TABLE " . self::TABLE . " DROP COLUMN icone_cor");
        }

        if ($this->columnExists(self::TABLE, 'texto_cor_descricao')) {
            $this->execute("ALTER TABLE " . self::TABLE . " DROP COLUMN texto_cor_descricao");
        }

        if ($this->columnExists(self::TABLE, 'texto_cor_titulo')) {
            $this->execute("ALTER TABLE " . self::TABLE . " DROP COLUMN texto_cor_titulo");
        }
    }
}

