<?php

/**
 * Migration: Add post workflow (status and user_id)
 * 
 * Status: pending, in_review, published, rejected, draft
 * Workflow:
 * - author (usuario): draft -> pending
 * - gerente: pending -> in_review or rejected
 * - admin: in_review -> published or rejected
 * - author: can view own posts in all stages
 */

use App\Database\Migration;

class Migration_2025_12_08_000001_add_post_workflow extends Migration
{
    public function up(): void
    {
        $sql = "ALTER TABLE posts 
                ADD COLUMN user_id INT UNSIGNED NULL AFTER id,
                ADD COLUMN status ENUM('draft', 'pending', 'in_review', 'published', 'rejected') DEFAULT 'draft' AFTER status_if_exists,
                ADD COLUMN reject_reason TEXT NULL AFTER status,
                ADD COLUMN published_at DATETIME NULL AFTER atualizado_em,
                ADD CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";
        
        $this->execute($sql);
    }

    public function down(): void
    {
        $sql = "ALTER TABLE posts 
                DROP CONSTRAINT fk_posts_user,
                DROP COLUMN user_id,
                DROP COLUMN status,
                DROP COLUMN reject_reason,
                DROP COLUMN published_at";
        
        $this->execute($sql);
    }
}
