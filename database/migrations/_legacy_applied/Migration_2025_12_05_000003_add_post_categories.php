<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2025_12_05_000003_add_post_categories extends Migration
{
    public function up(): void
    {
        if (!$this->tableExists('categorias_posts')) {
            $this->execute("
                CREATE TABLE categorias_posts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    staff_id VARCHAR(30) NOT NULL,
                    nome VARCHAR(150) NOT NULL,
                    descricao TEXT NULL,
                    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    CONSTRAINT uq_categorias_posts_nome UNIQUE (nome)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        if (!$this->columnExists('posts', 'categoria_id')) {
            $this->execute("ALTER TABLE posts ADD COLUMN categoria_id INT NULL AFTER autor");
        }

        if (!$this->indexExists('posts', 'idx_posts_categoria')) {
            $this->execute("ALTER TABLE posts ADD INDEX idx_posts_categoria (categoria_id)");
        }

        if ($this->hasReferencePrivilege() && !$this->fkExists()) {
            $this->execute("ALTER TABLE posts ADD CONSTRAINT fk_posts_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_posts(id)");
        }
    }

    public function down(): void
    {
        if ($this->hasReferencePrivilege() && $this->fkExists()) {
            $this->execute("ALTER TABLE posts DROP FOREIGN KEY fk_posts_categoria");
        }

        if ($this->indexExists('posts', 'idx_posts_categoria')) {
            $this->execute("ALTER TABLE posts DROP INDEX idx_posts_categoria");
        }

        if ($this->columnExists('posts', 'categoria_id')) {
            $this->execute("ALTER TABLE posts DROP COLUMN categoria_id");
        }

        if ($this->tableExists('categorias_posts')) {
            $this->execute("DROP TABLE categorias_posts");
        }
    }

    private function fkExists(): bool
    {
        $stmt = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_NAME = 'posts' AND CONSTRAINT_NAME = 'fk_posts_categoria'");
        return (bool)$stmt->fetch();
    }

    private function hasReferencePrivilege(): bool
    {
        try {
            $this->execute("ALTER TABLE posts ADD INDEX tmp_posts_categoria_idx (categoria_id)");
            $this->execute("ALTER TABLE posts DROP INDEX tmp_posts_categoria_idx");
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }
}
