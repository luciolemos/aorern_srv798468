<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_09_000021_create_digital_cards_table extends Migration
{
    public function up(): void
    {
        if ($this->tableExists('digital_cards')) {
            return;
        }

        $this->execute(<<<SQL
            CREATE TABLE digital_cards (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                pessoal_id INT UNSIGNED NOT NULL,
                card_code VARCHAR(40) NOT NULL,
                token VARCHAR(80) NOT NULL,
                status ENUM('ativa','revogada','expirada') NOT NULL DEFAULT 'ativa',
                emitida_em DATETIME NOT NULL,
                validade_ate DATETIME NULL,
                revogada_em DATETIME NULL,
                motivo_revogacao VARCHAR(255) NULL,
                snapshot_json LONGTEXT NULL,
                criado_em DATETIME NOT NULL,
                atualizado_em DATETIME NOT NULL,
                UNIQUE KEY uq_digital_cards_code (card_code),
                UNIQUE KEY uq_digital_cards_token (token),
                KEY idx_digital_cards_pessoal_status (pessoal_id, status),
                CONSTRAINT fk_digital_cards_pessoal FOREIGN KEY (pessoal_id) REFERENCES pessoal(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(): void
    {
        if ($this->tableExists('digital_cards')) {
            $this->execute('DROP TABLE digital_cards');
        }
    }
}

