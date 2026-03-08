-- Migração: adiciona nome_mae e nome_pai em membership_applications
-- Data: 2026-03-05

SET @has_nome_mae_column := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'membership_applications'
      AND COLUMN_NAME = 'nome_mae'
);

SET @ddl_add_nome_mae := IF(
    @has_nome_mae_column = 0,
    'ALTER TABLE membership_applications ADD COLUMN nome_mae VARCHAR(160) NULL AFTER nome_completo',
    'SELECT 1'
);
PREPARE stmt_add_nome_mae FROM @ddl_add_nome_mae;
EXECUTE stmt_add_nome_mae;
DEALLOCATE PREPARE stmt_add_nome_mae;

SET @has_nome_pai_column := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'membership_applications'
      AND COLUMN_NAME = 'nome_pai'
);

SET @ddl_add_nome_pai := IF(
    @has_nome_pai_column = 0,
    'ALTER TABLE membership_applications ADD COLUMN nome_pai VARCHAR(160) NULL AFTER nome_mae',
    'SELECT 1'
);
PREPARE stmt_add_nome_pai FROM @ddl_add_nome_pai;
EXECUTE stmt_add_nome_pai;
DEALLOCATE PREPARE stmt_add_nome_pai;
