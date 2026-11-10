-- ============================================================
-- Migração 006 — Gestão de Recursos
-- Cria uma tabela independente para a gestão de recursos.
-- ============================================================

CREATE TABLE IF NOT EXISTS `gestao_recursos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `descricao` VARCHAR(255) NOT NULL,
    `valor` DECIMAL(10,2) NOT NULL,
    `forma_pagamento` ENUM(
        'Dinheiro',
        'Pix',
        'Cartão'
    ) NOT NULL,
    `data` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_gestao_recursos_data` (`data`),
    INDEX `idx_gestao_recursos_pagamento` (`forma_pagamento`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

