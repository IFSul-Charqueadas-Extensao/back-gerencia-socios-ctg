ALTER TABLE dependentes
ADD COLUMN status ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo'
AFTER socio_titular_id;

UPDATE dependentes d
INNER JOIN socios s ON s.id = d.socio_titular_id
SET d.status = s.status;