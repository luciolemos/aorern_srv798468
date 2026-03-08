-- Migração: adiciona numero_militar e nome_guerra em membership_applications
-- Data: 2026-03-05

SET @has_numero_column := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'membership_applications'
      AND COLUMN_NAME = 'numero_militar'
);

SET @ddl_add_numero := IF(
    @has_numero_column = 0,
    'ALTER TABLE membership_applications ADD COLUMN numero_militar VARCHAR(30) NULL AFTER posto_graduacao',
    'SELECT 1'
);
PREPARE stmt_add_numero FROM @ddl_add_numero;
EXECUTE stmt_add_numero;
DEALLOCATE PREPARE stmt_add_numero;

SET @has_nome_guerra_column := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'membership_applications'
      AND COLUMN_NAME = 'nome_guerra'
);

SET @ddl_add_nome_guerra := IF(
    @has_nome_guerra_column = 0,
    'ALTER TABLE membership_applications ADD COLUMN nome_guerra VARCHAR(60) NULL AFTER numero_militar',
    'SELECT 1'
);
PREPARE stmt_add_nome_guerra FROM @ddl_add_nome_guerra;
EXECUTE stmt_add_nome_guerra;
DEALLOCATE PREPARE stmt_add_nome_guerra;
