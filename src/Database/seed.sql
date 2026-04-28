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
-- DELETE FROM pagamentos;
-- DELETE FROM cartao_tradicionalista;
-- DELETE FROM mensalidades;
-- DELETE FROM dependentes;
-- DELETE FROM socios;
-- DELETE FROM categorias;

-- Insert Categorias
INSERT INTO categorias (id, nome, valor_sociedade, valor_instrutor) VALUES
(1, 'Mirim', 50.00, 25.00),
(2, 'Juvenil', 75.00, 35.00),
(3, 'Adulta', 100.00, 50.00),
(4, 'Instrutor', 150.00, 0.00);

-- Insert Socios
INSERT INTO socios (id, nome_completo, cpf, telefone, foto, identidade, endereco, data_nascimento, data_entrada, categoria_id, status, dancarino, paga_instrutor) VALUES
(1, 'João Silva', '123.456.789-00', '(11) 99999-9999', NULL, 'MG-12.345.678', 'Rua das Flores, 123', '1990-04-15', '2026-01-15', 3, 'Ativo', 1, 0),
(2, 'Maria Santos', '987.654.321-00', '(11) 98888-8888', NULL, 'SP-98.765.432', 'Avenida Principal, 456', '1985-06-20', '2026-02-01', 3, 'Ativo', 1, 1),
(3, 'Pedro Oliveira', '456.789.123-00', '(11) 97777-7777', NULL, 'RJ-45.678.901', 'Rua Central, 789', '2010-03-10', '2026-02-15', 1, 'Ativo', 1, 0),
(4, 'Ana Costa', '789.123.456-00', '(11) 96666-6666', NULL, 'MG-78.901.234', 'Travessa Lateral, 321', '2000-08-22', '2026-03-01', 2, 'Ativo', 1, 1),
(5, 'Carlos Ferreira', '321.654.987-00', '(11) 95555-5555', NULL, 'SP-32.165.498', 'Rua Secundária, 654', '1995-11-30', '2026-03-15', 3, 'Inativo', 0, 0);

-- Insert Dependentes
INSERT INTO dependentes (id, socio_titular_id, nome_completo, cpf, telefone, foto, identidade, endereco, data_nascimento, data_entrada, categoria_id, dancarino, paga_instrutor) VALUES
(1, 1, 'Lucas Silva', '111.222.333-00', '(11) 99999-9999', NULL, 'MG-11.222.333', 'Rua das Flores, 123', '2012-05-10', '2026-01-15', 1, 1, 0),
(2, 2, 'Julia Santos', '222.333.444-00', '(11) 98888-8888', NULL, 'SP-22.333.444', 'Avenida Principal, 456', '2015-07-25', '2026-02-01', 1, 1, 0),
(3, 4, 'Felipe Costa', '333.444.555-00', '(11) 96666-6666', NULL, 'MG-33.444.555', 'Travessa Lateral, 321', '2008-09-12', '2026-03-01', 2, 1, 1);

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

-- Confirmação
SELECT CONCAT(
    'Database populada com sucesso! ',
    'Created ', (SELECT COUNT(*) FROM categorias), ' categorias, ',
    (SELECT COUNT(*) FROM socios), ' socios, ',
    (SELECT COUNT(*) FROM dependentes), ' dependentes, ',
    (SELECT COUNT(*) FROM mensalidades), ' mensalidades, ',
    (SELECT COUNT(*) FROM pagamentos), ' pagamentos, ',
    (SELECT COUNT(*) FROM cartao_tradicionalista), ' cartoes tradicionalistas.'
) AS status;
