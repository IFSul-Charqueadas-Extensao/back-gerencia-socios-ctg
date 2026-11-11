<?php

namespace Repository;

use Database\Database;
use Model\GestaoRecurso;
use PDO;
use DateTime;

class GestaoRecursoRepository
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM gestao_recursos
            ORDER BY data DESC, id DESC
        ");

        $stmt->execute();

        $recursos = [];

        while ($row = $stmt->fetch()) {
            $recursos[] = $this->mapearRecurso($row);
        }

        return $recursos;
    }

    public function findById(int $id): ?GestaoRecurso
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM gestao_recursos
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->mapearRecurso($row);
    }

    public function create(GestaoRecurso $recurso): GestaoRecurso
    {
        $stmt = $this->connection->prepare("
            INSERT INTO gestao_recursos (
                descricao,
                valor,
                forma_pagamento,
                data
            )
            VALUES (
                :descricao,
                :valor,
                :forma_pagamento,
                :data
            )
        ");

        $stmt->bindValue(':descricao', $recurso->getDescricao());
        $stmt->bindValue(':valor', $recurso->getValor());
        $stmt->bindValue(
            ':forma_pagamento',
            $recurso->getFormaPagamento()
        );
        $stmt->bindValue(
            ':data',
            $recurso->getData()->format('Y-m-d H:i:s')
        );

        $stmt->execute();

        $id = (int) $this->connection->lastInsertId();

        return $this->findById($id);
    }

    public function update(GestaoRecurso $recurso): void
    {
        $stmt = $this->connection->prepare("
            UPDATE gestao_recursos SET
                descricao = :descricao,
                valor = :valor,
                forma_pagamento = :forma_pagamento,
                data = :data
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $recurso->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $recurso->getDescricao());
        $stmt->bindValue(':valor', $recurso->getValor());
        $stmt->bindValue(
            ':forma_pagamento',
            $recurso->getFormaPagamento()
        );
        $stmt->bindValue(
            ':data',
            $recurso->getData()->format('Y-m-d H:i:s')
        );

        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare("
            DELETE FROM gestao_recursos
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function mapearRecurso(array $row): GestaoRecurso
    {
        return new GestaoRecurso(
            descricao: $row['descricao'],
            valor: (float) $row['valor'],
            formaPagamento: $row['forma_pagamento'],
            data: new DateTime($row['data']),
            id: (int) $row['id']
        );
    }
}

