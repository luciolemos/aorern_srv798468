-- Migração: separa UF da coluna cidade em membership_applications
-- Data: 2026-03-05
-- Objetivo:
-- 1) adicionar coluna uf (CHAR(2)) e índice
-- 2) migrar dados legados no formato "Cidade/UF" para "cidade" + "uf"

SET @has_uf_column := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'membership_applications'
      AND COLUMN_NAME = 'uf'
);

SET @ddl_add_uf := IF(
    @has_uf_column = 0,
    'ALTER TABLE membership_applications ADD COLUMN uf CHAR(2) NULL AFTER cidade',
    'SELECT 1'
);
PREPARE stmt_add_uf FROM @ddl_add_uf;
EXECUTE stmt_add_uf;
DEALLOCATE PREPARE stmt_add_uf;

SET @has_uf_index := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'membership_applications'
      AND INDEX_NAME = 'idx_membership_applications_uf'
);

SET @ddl_add_uf_index := IF(
    @has_uf_index = 0,
    'ALTER TABLE membership_applications ADD INDEX idx_membership_applications_uf (uf)',
    'SELECT 1'
);
PREPARE stmt_add_uf_index FROM @ddl_add_uf_index;
EXECUTE stmt_add_uf_index;
DEALLOCATE PREPARE stmt_add_uf_index;

-- Migra registros legados apenas quando UF ainda está vazia
UPDATE membership_applications
SET
    uf = UPPER(TRIM(SUBSTRING_INDEX(cidade, '/', -1))),
    cidade = TRIM(SUBSTRING_INDEX(cidade, '/', 1))
WHERE (uf IS NULL OR uf = '')
  AND cidade IS NOT NULL
  AND cidade REGEXP '/[A-Za-z]{2}$';

-- Normaliza UF inválida
UPDATE membership_applications
SET uf = NULL
WHERE uf IS NOT NULL
  AND uf <> ''
  AND uf NOT REGEXP '^[A-Za-z]{2}$';

UPDATE membership_applications
SET uf = UPPER(uf)
WHERE uf REGEXP '^[A-Za-z]{2}$';
