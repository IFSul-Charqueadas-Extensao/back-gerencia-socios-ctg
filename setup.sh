#!/bin/bash

# 1. Configuração automática do .env
if [ ! -f .env ]; then
    echo "Criando arquivo .env a partir do .env.example..."
    cp .env.example .env
    
    # Substitui os valores padrão do README pelos valores do ambiente Docker
    sed -i 's/DB_HOST=localhost/DB_HOST=db_php/g' .env
    sed -i 's/DB_NAME=ctg/DB_NAME=phpdb/g' .env
    sed -i 's/DB_USER=ctg_user/DB_USER=phpuser/g' .env
    sed -i 's/DB_PASSWORD=1234/DB_PASSWORD=phppass/g' .env
    echo " .env configurado para o Docker!"
fi

# 2. Executar os scripts SQL dentro do container MySQL
echo "Importando o Schema do banco de dados..."
docker exec -i mysql_db_php mysql -uphpuser -pphppass phpdb < src/Database/schema.sql

echo "Populando o banco com dados de teste (Seed)..."
docker exec -i mysql_db_php mysql -uphpuser -pphppass phpdb < src/Database/seed.sql

echo "Ambiente configurado com sucesso!"