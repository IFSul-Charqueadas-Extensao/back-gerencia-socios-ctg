-- ================================
-- FAKE DATABASE SEEDER
-- ================================
-- SOMENTE PARA TESTES!!!
--
-- USO:
-- para carregar o banco de dados, execute no terminal:
--   mysql -u ctg_user -p1234 ctg < src/Database/seed.sql
--
--
-- Esse script popula o banco de dados com data de teste para as operações CRUD
-- ================================

-- Limpa data existente (opcional)
-- refresh_tokens antes de usuarios por causa da foreign key
DELETE FROM refresh_tokens;
DELETE FROM usuarios;
DELETE FROM pagamentos;
DELETE FROM cartao_tradicionalista;
DELETE FROM mensalidades;
DELETE FROM dependentes;
DELETE FROM socios;
DELETE FROM categorias;

-- Insert Categorias
INSERT INTO categorias (id, nome, valor_sociedade, valor_instrutor) VALUES
(1, 'Mirim', 50.00, 25.00),
(2, 'Juvenil', 75.00, 35.00),
(3, 'Adulta', 100.00, 50.00),
(4, 'Instrutor', 150.00, 0.00);

-- Insert Socios
INSERT INTO socios (id, nome_completo, cpf, telefone, email, foto, endereco, data_nascimento, data_entrada, categoria_id, status, dancarino, paga_instrutor) VALUES
(1, 'João Silva', '476.457.130-70', '(11) 99999-9999','joao@gmail.com', NULL, 'Rua das Flores, 123', '1990-04-15', '2026-01-15', 3, 'Ativo', 1, 0),
(2, 'Maria Santos', '331.410.940-71', '(11) 98888-8888', 'maria@gmail.com', NULL, 'Avenida Principal, 456', '1985-06-20', '2026-02-01', 3, 'Ativo', 1, 1),
(3, 'Pedro Oliveira', '581.281.230-68', '(11) 97777-7777','pedro@gmail.com', NULL, 'Rua Central, 789', '2010-03-10', '2026-02-15', 1, 'Ativo', 1, 0),
(4, 'Ana Costa', '695.991.870-75', '(11) 96666-6666','ana@gmail.com', NULL, 'Travessa Lateral, 321', '2000-08-22', '2026-03-01', 2, 'Ativo', 1, 1),
(5, 'Carlos Ferreira', '850.182.530-10', '(11) 95555-5555','carlos@gmail.com', NULL, 'Rua Secundária, 654', '1995-11-30', '2026-03-15', 3, 'Inativo', 0, 0);

-- Insert Dependentes
INSERT INTO dependentes (id, socio_titular_id, nome_completo, cpf, foto, data_nascimento, data_entrada, categoria_id, dancarino) VALUES
(1, 1, 'Lucas Silva', '406.024.440-63', NULL, '2012-05-10', '2026-01-15', 1, 1),
(2, 2, 'Julia Santos', '582.050.510-70', NULL, '2015-07-25', '2026-02-01', 1, 1),
(3, 4, 'Felipe Costa', '613.147.770-17', NULL, '2008-09-12', '2026-03-01', 2, 1);

-- Insert Mensalidades
INSERT INTO mensalidades (id, socio_id, dependente_id, mes, ano, valor, status, data_vencimento) VALUES
(1, 1, NULL, 1, 2026, 100.00, 'Pago', '2026-01-31'),
(2, 1, NULL, 2, 2026, 100.00, 'Pago', '2026-02-28'),
(3, 1, NULL, 3, 2026, 100.00, 'Atrasado', '2026-03-31'),
(4, 1, NULL, 4, 2026, 100.00, 'Pendente', '2026-04-30'),
(5, 1, 1, 1, 2026, 50.00, 'Pago', '2026-01-31'),
(6, 1, 1, 2, 2026, 50.00, 'Pago', '2026-02-28'),
(7, 1, 1, 3, 2026, 50.00, 'Pendente', '2026-03-31'),
(8, 2, NULL, 1, 2026, 100.00, 'Pago', '2026-01-31'),
(9, 2, NULL, 2, 2026, 100.00, 'Pago', '2026-02-28'),
(10, 2, 2, 1, 2026, 50.00, 'Pago', '2026-01-31'),
(11, 3, NULL, 1, 2026, 50.00, 'Pago', '2026-01-31'),
(12, 3, NULL, 2, 2026, 50.00, 'Pendente', '2026-02-28'),
(13, 4, NULL, 1, 2026, 75.00, 'Pago', '2026-01-31'),
(14, 4, NULL, 2, 2026, 75.00, 'Atrasado', '2026-02-28');

-- Insert Pagamentos
INSERT INTO pagamentos (id, mensalidade_id, data_pagamento, forma_pagamento, valor_pago, multa_juros_aplicados) VALUES
(1, 1, '2026-01-25', 'Cartao', 100.00, 0.00),
(2, 2, '2026-02-20', 'Dinheiro', 100.00, 0.00),
(3, 5, '2026-01-30', 'Transferencia', 50.00, 0.00),
(4, 6, '2026-02-25', 'Cartao', 50.00, 0.00),
(5, 8, '2026-01-28', 'Dinheiro', 100.00, 0.00),
(6, 9, '2026-02-22', 'Cartao', 100.00, 0.00),
(7, 10, '2026-01-31', 'Transferencia', 50.00, 0.00),
(8, 11, '2026-01-20', 'Dinheiro', 50.00, 0.00),
(9, 13, '2026-01-29', 'Cartao', 75.00, 0.00);

-- Insert Cartão Tradicionalista
INSERT INTO cartao_tradicionalista (id, socio_id, dependente_id, data_solicitacao, pago, valor) VALUES
(1, 1, NULL, '2026-02-01', 1, 50.00),
(2, 1, 1, '2026-02-05', 0, 25.00),
(3, 2, NULL, '2026-02-10', 1, 50.00),
(4, 4, NULL, '2026-02-15', 0, 50.00);

-- ================================
-- USUÁRIOS DE TESTE
-- ================================
-- SOMENTE PARA TESTES!!!
--
-- ATENÇÃO: as senhas abaixo são públicas (estão neste arquivo versionado).
-- ANTES DE SUBIR PARA PRODUÇÃO: troque a senha do admin e apague os demais.
--
--   email                  | senha          | papel
--   -----------------------+----------------+-----------
--   admin@ctg.local        | admin123       | admin
--   financeiro@ctg.local   | financeiro123  | financeiro
--   socios@ctg.local       | socios123      | socios
--   consulta@ctg.local     | consulta123    | consulta

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `role`) VALUES
(1, 'Administrador',   'admin@ctg.local',      '$2y$10$u8u1scygg8.isMTc73iyr.LpA3hEXvxZB3ecJNuMIP8otvODivrN2', 'admin'),
(2, 'Tesouraria',      'financeiro@ctg.local', '$2y$10$FG718U3XHIyoKdT44hpKk.HEY5AGWsfl/bu7f7SsIlD7j3v4xFOdC', 'financeiro'),
(3, 'Secretaria',      'socios@ctg.local',     '$2y$10$/zPJ4J1CFncD2igHcDRVfuZmT6d/kqiuV4nkJ1D7pIWyLsybrHPGK', 'socios'),
(4, 'Consulta Geral',  'consulta@ctg.local',   '$2y$10$IrcXDq9y6AO/gMBpbDhSVOywCTpZrhRAg1lWCkRXfap59X3htSbEa', 'consulta');

-- Confirmação
SELECT CONCAT(
    'Database populada com sucesso! ',
    'Created ', (SELECT COUNT(*) FROM categorias), ' categorias, ',
    (SELECT COUNT(*) FROM socios), ' socios, ',
    (SELECT COUNT(*) FROM dependentes), ' dependentes, ',
    (SELECT COUNT(*) FROM mensalidades), ' mensalidades, ',
    (SELECT COUNT(*) FROM pagamentos), ' pagamentos, ',
    (SELECT COUNT(*) FROM cartao_tradicionalista), ' cartoes tradicionalistas, ',
    (SELECT COUNT(*) FROM usuarios), ' usuarios.'
) AS status;
