
-- ============================================================
-- Migração 002 — Vendas da Copa
-- Cria uma tabela independente para as vendas do bar.
-- ============================================================

CREATE TABLE IF NOT EXISTS `vendas_copa` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `produto` VARCHAR(150) NOT NULL,
    `valor` DECIMAL(10,2) NOT NULL,
    `forma_pagamento` ENUM(
        'Dinheiro',
        'Pix',
        'Cartão'
    ) NOT NULL,
    `data_venda` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_vendas_copa_data` (`data_venda`),
    INDEX `idx_vendas_copa_pagamento` (`forma_pagamento`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
