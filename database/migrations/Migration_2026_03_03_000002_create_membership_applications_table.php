<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_03_000002_create_membership_applications_table extends Migration
{
    public function up(): void
    {
        if ($this->tableExists('membership_applications')) {
            return;
        }

        $this->execute(<<<SQL
            CREATE TABLE membership_applications (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome_completo VARCHAR(160) NOT NULL,
                username_desejado VARCHAR(60) NOT NULL,
                email VARCHAR(120) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                cpf VARCHAR(14) NOT NULL,
                data_nascimento DATE NULL,
                telefone VARCHAR(20) NULL,
                cidade VARCHAR(120) NULL,
                ano_npor VARCHAR(10) NOT NULL,
                turma_npor VARCHAR(80) NULL,
                arma_quadro VARCHAR(120) NULL,
                situacao_militar VARCHAR(120) NULL,
                avatar VARCHAR(255) NULL,
                observacoes TEXT NULL,
                aceite_termo TINYINT(1) NOT NULL DEFAULT 1,
                status ENUM('pendente','aprovada','rejeitada') NOT NULL DEFAULT 'pendente',
                user_id INT UNSIGNED NULL,
                pessoal_id INT UNSIGNED NULL,
                observacoes_admin TEXT NULL,
                aprovado_em DATETIME NULL,
                rejeitado_em DATETIME NULL,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_membership_applications_status (status),
                KEY idx_membership_applications_email (email),
                KEY idx_membership_applications_cpf (cpf),
                KEY idx_membership_applications_username (username_desejado),
                KEY idx_membership_applications_user (user_id),
                KEY idx_membership_applications_pessoal (pessoal_id),
                CONSTRAINT fk_membership_applications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                CONSTRAINT fk_membership_applications_pessoal FOREIGN KEY (pessoal_id) REFERENCES pessoal(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(): void
    {
        if ($this->tableExists('membership_applications')) {
            $this->execute('DROP TABLE membership_applications');
        }
    }
}
