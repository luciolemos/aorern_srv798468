<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2025_12_13_000001_create_gallery_tables extends Migration
{
    public function up(): void
    {
        if (!$this->tableExists('gallery_categories')) {
            $this->execute(<<<SQL
                CREATE TABLE gallery_categories (
                    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    nome VARCHAR(64) NOT NULL,
                    slug VARCHAR(128) DEFAULT NULL,
                    color VARCHAR(16) DEFAULT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_gallery_categories_nome (nome),
                    UNIQUE KEY uq_gallery_categories_slug (slug)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (!$this->tableExists('gallery_images')) {
            $this->execute(<<<SQL
                CREATE TABLE gallery_images (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    category_id INT(10) UNSIGNED NOT NULL,
                    titulo VARCHAR(128) NOT NULL,
                    descricao TEXT DEFAULT NULL,
                    url VARCHAR(255) NOT NULL,
                    data_upload DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_gallery_category (category_id),
                    CONSTRAINT fk_gallery_images_category FOREIGN KEY (category_id)
                        REFERENCES gallery_categories (id)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        if ($this->tableExists('gallery_images')) {
            $this->execute('DROP TABLE gallery_images');
        }

        if ($this->tableExists('gallery_categories')) {
            $this->execute('DROP TABLE gallery_categories');
        }
    }
}
