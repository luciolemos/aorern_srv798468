-- Migração: adiciona posto_graduacao em membership_applications
-- Data: 2026-03-05

SET @has_posto_column := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'membership_applications'
      AND COLUMN_NAME = 'posto_graduacao'
);

SET @ddl_add_posto := IF(
    @has_posto_column = 0,
    'ALTER TABLE membership_applications ADD COLUMN posto_graduacao VARCHAR(60) NULL AFTER ano_npor',
    'SELECT 1'
);
PREPARE stmt_add_posto FROM @ddl_add_posto;
EXECUTE stmt_add_posto;
DEALLOCATE PREPARE stmt_add_posto;
