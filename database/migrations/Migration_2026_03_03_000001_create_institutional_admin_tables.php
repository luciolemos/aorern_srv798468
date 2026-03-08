<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_03_000001_create_institutional_admin_tables extends Migration
{
    public function up(): void
    {
        if (!$this->tableExists('institutional_documents')) {
            $this->execute(<<<SQL
                CREATE TABLE institutional_documents (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    titulo VARCHAR(180) NOT NULL,
                    slug VARCHAR(190) NOT NULL,
                    tipo ENUM('estatuto','ata','oficio','formulario','politica','termo','marca','outro') NOT NULL DEFAULT 'outro',
                    resumo TEXT NULL,
                    arquivo_url VARCHAR(512) NULL,
                    link_externo VARCHAR(512) NULL,
                    status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
                    publicado_em DATETIME NULL,
                    ordem INT NOT NULL DEFAULT 0,
                    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_institutional_documents_slug (slug),
                    KEY idx_institutional_documents_status (status),
                    KEY idx_institutional_documents_tipo (tipo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (!$this->tableExists('board_terms')) {
            $this->execute(<<<SQL
                CREATE TABLE board_terms (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    nome VARCHAR(160) NOT NULL,
                    status ENUM('planned','active','archived') NOT NULL DEFAULT 'planned',
                    data_inicio DATE NOT NULL,
                    data_fim DATE NULL,
                    observacoes TEXT NULL,
                    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    KEY idx_board_terms_status (status),
                    KEY idx_board_terms_inicio (data_inicio)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (!$this->tableExists('board_memberships')) {
            $this->execute(<<<SQL
                CREATE TABLE board_memberships (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    term_id INT UNSIGNED NULL,
                    pessoal_id INT UNSIGNED NULL,
                    funcao_id INT UNSIGNED NULL,
                    cargo VARCHAR(160) NOT NULL,
                    grupo VARCHAR(120) NULL,
                    ordem INT NOT NULL DEFAULT 0,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    observacoes TEXT NULL,
                    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    KEY idx_board_memberships_term (term_id),
                    KEY idx_board_memberships_pessoal (pessoal_id),
                    KEY idx_board_memberships_funcao (funcao_id),
                    KEY idx_board_memberships_active (is_active),
                    CONSTRAINT fk_board_memberships_term FOREIGN KEY (term_id) REFERENCES board_terms(id) ON DELETE SET NULL,
                    CONSTRAINT fk_board_memberships_pessoal FOREIGN KEY (pessoal_id) REFERENCES pessoal(id) ON DELETE SET NULL,
                    CONSTRAINT fk_board_memberships_funcao FOREIGN KEY (funcao_id) REFERENCES funcoes(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        if ($this->tableExists('board_memberships')) {
            $this->execute('DROP TABLE board_memberships');
        }

        if ($this->tableExists('board_terms')) {
            $this->execute('DROP TABLE board_terms');
        }

        if ($this->tableExists('institutional_documents')) {
            $this->execute('DROP TABLE institutional_documents');
        }
    }
}
