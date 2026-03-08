<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class Migration_2026_03_04_000007_add_notification_tracking_to_membership_applications extends Migration
{
    public function up(): void
    {
        if (!$this->tableExists('membership_applications')) {
            return;
        }

        if (!$this->columnExists('membership_applications', 'last_notification_type')) {
            $this->execute("ALTER TABLE membership_applications ADD COLUMN last_notification_type VARCHAR(32) NULL AFTER rejeitado_em");
        }

        if (!$this->columnExists('membership_applications', 'last_notification_status')) {
            $this->execute("ALTER TABLE membership_applications ADD COLUMN last_notification_status VARCHAR(16) NULL AFTER last_notification_type");
        }

        if (!$this->columnExists('membership_applications', 'last_notification_at')) {
            $this->execute("ALTER TABLE membership_applications ADD COLUMN last_notification_at DATETIME NULL AFTER last_notification_status");
        }

        if (!$this->columnExists('membership_applications', 'last_notification_error')) {
            $this->execute("ALTER TABLE membership_applications ADD COLUMN last_notification_error TEXT NULL AFTER last_notification_at");
        }
    }

    public function down(): void
    {
        if (!$this->tableExists('membership_applications')) {
            return;
        }

        if ($this->columnExists('membership_applications', 'last_notification_error')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN last_notification_error");
        }

        if ($this->columnExists('membership_applications', 'last_notification_at')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN last_notification_at");
        }

        if ($this->columnExists('membership_applications', 'last_notification_status')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN last_notification_status");
        }

        if ($this->columnExists('membership_applications', 'last_notification_type')) {
            $this->execute("ALTER TABLE membership_applications DROP COLUMN last_notification_type");
        }
    }
}
