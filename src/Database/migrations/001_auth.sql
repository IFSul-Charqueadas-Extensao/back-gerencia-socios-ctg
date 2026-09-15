-- ============================================================
-- Migração 001 — Autenticação e níveis de permissão
-- ============================================================
-- Aplicável sobre um banco JÁ POPULADO, sem perder dados.
-- Usa CREATE TABLE IF NOT EXISTS porque o schema.sql principal
-- não é idempotente e falharia num banco existente.
--
-- Como aplicar (PowerShell, na raiz do repo back):
--   $mysql = 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe'
--   $env:MYSQL_PWD = '1234'
--   & $mysql -u ctg_user ctg --default-character-set=utf8mb4 `
--       -e "source src/Database/migrations/001_auth.sql"
-- ============================================================

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `senha_hash` varchar(255) NOT NULL,
  `role` ENUM('admin','financeiro','socios','consulta') NOT NULL DEFAULT 'consulta',
  `ativo` boolean NOT NULL DEFAULT true,
  `criado_em` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `refresh_tokens` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `usuario_id` integer NOT NULL,
  -- guarda o sha256 do token, nunca o token puro:
  -- vazamento do banco não permite reutilizar as sessões
  `token_hash` char(64) NOT NULL,
  `expira_em` datetime NOT NULL,
  `revogado` boolean NOT NULL DEFAULT false,
  `criado_em` timestamp DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_refresh_token_hash` (`token_hash`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
);

