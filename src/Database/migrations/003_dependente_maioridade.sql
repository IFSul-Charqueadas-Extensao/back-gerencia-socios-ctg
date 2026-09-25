ALTER TABLE dependentes
ADD COLUMN data_maioridade DATE
GENERATED ALWAYS AS (
    DATE_ADD(data_nascimento, INTERVAL 18 YEAR)
) STORED

AFTER data_nascimento;
UPDATE dependentes
SET status = 'Inativo'
WHERE status = 'Ativo'
AND data_maioridade <= CURDATE();