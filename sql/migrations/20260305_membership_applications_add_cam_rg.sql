-- Migração: adiciona CAM e RG em membership_applications
-- Data: 2026-03-05

SET @has_cam_column := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'membership_applications'
      AND COLUMN_NAME = 'cam'
);

SET @ddl_add_cam := IF(
    @has_cam_column = 0,
    'ALTER TABLE membership_applications ADD COLUMN cam VARCHAR(40) NULL AFTER cpf',
    'SELECT 1'
);
PREPARE stmt_add_cam FROM @ddl_add_cam;
EXECUTE stmt_add_cam;
DEALLOCATE PREPARE stmt_add_cam;

SET @has_rg_column := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'membership_applications'
      AND COLUMN_NAME = 'rg'
);

SET @ddl_add_rg := IF(
    @has_rg_column = 0,
    'ALTER TABLE membership_applications ADD COLUMN rg VARCHAR(40) NULL AFTER cam',
    'SELECT 1'
);
PREPARE stmt_add_rg FROM @ddl_add_rg;
EXECUTE stmt_add_rg;
DEALLOCATE PREPARE stmt_add_rg;
