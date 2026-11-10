ALTER TABLE socios
ADD COLUMN data_desligamento DATE NULL DEFAULT NULL
AFTER data_entrada;
