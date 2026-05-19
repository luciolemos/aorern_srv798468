<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_05_19_000001_add_is_published_to_gallery_images extends Migration
{
    private const TABLE = 'gallery_images';
    private const INDEX = 'idx_gallery_images_is_published';

    public function up(): void
    {
        if (!$this->tableExists(self::TABLE)) {
            return;
        }

        if (!$this->columnExists(self::TABLE, 'is_published')) {
            $this->execute(
                "ALTER TABLE " . self::TABLE . "
                 ADD COLUMN is_published TINYINT(1) NOT NULL DEFAULT 1 AFTER url"
            );
        }

        if (!$this->indexExists(self::TABLE, self::INDEX)) {
            $this->execute(
                "ALTER TABLE " . self::TABLE . "
                 ADD INDEX " . self::INDEX . " (is_published)"
            );
        }
    }

    public function down(): void
    {
        if (!$this->tableExists(self::TABLE)) {
            return;
        }

        if ($this->indexExists(self::TABLE, self::INDEX)) {
            $this->execute(
                "ALTER TABLE " . self::TABLE . "
                 DROP INDEX " . self::INDEX
            );
        }

        if ($this->columnExists(self::TABLE, 'is_published')) {
            $this->execute(
                "ALTER TABLE " . self::TABLE . "
                 DROP COLUMN is_published"
            );
        }
    }
}

