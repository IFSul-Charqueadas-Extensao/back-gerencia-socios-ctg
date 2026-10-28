-- ============================================================
-- Migração 005 — Parentesco do dependente
-- Cônjuge não perde a dependência aos 18 anos.
-- Idempotente: o deploy roda todas as migrations a cada push,
-- e o MySQL não aceita "ADD COLUMN IF NOT EXISTS".
-- ============================================================

SET @existe := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'dependentes'
    AND COLUMN_NAME = 'parentesco'
);

SET @sql := IF(@existe = 0,
  "ALTER TABLE dependentes ADD COLUMN parentesco ENUM('conjuge','filho','outro') NOT NULL DEFAULT 'filho' AFTER socio_titular_id",
  'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
