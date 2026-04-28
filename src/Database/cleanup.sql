-- ================================
-- DATABASE FAKE CLEANUP
-- ================================
-- 
-- USO:
-- Para remover todas as informações do banco de dados, execute no terminal:
--   mysql -u ctg_user -p1234 ctg < src/Database/cleanup.sql
-- 
-- Or in MySQL client:
--   SOURCE src/Database/cleanup.sql;
--
-- Esse script deleta tudo criado pelo seed.sql
-- ================================

DELETE FROM pagamentos;
DELETE FROM cartao_tradicionalista;
DELETE FROM mensalidades;
DELETE FROM dependentes;
DELETE FROM socios;
DELETE FROM categorias;

-- Reset auto-increment
ALTER TABLE categorias AUTO_INCREMENT = 1;
ALTER TABLE socios AUTO_INCREMENT = 1;
ALTER TABLE dependentes AUTO_INCREMENT = 1;
ALTER TABLE mensalidades AUTO_INCREMENT = 1;
ALTER TABLE pagamentos AUTO_INCREMENT = 1;
ALTER TABLE cartao_tradicionalista AUTO_INCREMENT = 1;

-- confirmação
SELECT 'Database deletada com sucesso! Todos os dados de teste foram apagados.' AS status;
