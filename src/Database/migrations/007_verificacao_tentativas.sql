-- ============================================================
-- Migração 007 — Rate limit da verificação pública de adimplência
-- Registra cada consulta a POST /api/verificacao para limitar tentativas
-- por IP (dificulta a enumeração de CPFs). Guarda o sha256 do IP, nunca o
-- IP puro. Idempotente: o deploy reexecuta todas as migrations a cada push.
-- ============================================================

CREATE TABLE IF NOT EXISTS `verificacao_tentativas` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `ip_hash` CHAR(64) NOT NULL,
    `tentativa_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_verificacao_ip_data` (`ip_hash`, `tentativa_em`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
