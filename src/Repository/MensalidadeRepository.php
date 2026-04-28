<?php

namespace Repository;

use Database\Database;
use Model\Mensalidade;
use PDO;
use DateTime;

class MensalidadeRepository
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM mensalidades");
        $stmt->execute();

        $mensalidades = [];

        while ($row = $stmt->fetch()) {
            $mensalidade = new Mensalidade(
                socioId: $row['socio_id'],
                dependenteId: $row['dependente_id'],
                mes: $row['mes'],
                ano: $row['ano'],
                valor: $row['valor'],
                dataVencimento: new DateTime($row['data_vencimento']),
                status: $row['status'],
                id: $row['id']
            );

            $mensalidades[] = $mensalidade;
        }

        return $mensalidades;
    }

    public function findById(int $id): ?Mensalidade
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM mensalidades
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new Mensalidade(
            socioId: $row['socio_id'],
            dependenteId: $row['dependente_id'],
            mes: $row['mes'],
            ano: $row['ano'],
            valor: $row['valor'],
            dataVencimento: new DateTime($row['data_vencimento']),
            status: $row['status'],
            id: $row['id']
        );
    }

    public function create(Mensalidade $mensalidade): Mensalidade
    {
        $stmt = $this->connection->prepare("
            INSERT INTO mensalidades (
                socio_id,
                dependente_id,
                mes,
                ano,
                valor,
                status,
                data_vencimento
            )
            VALUES (
                :socio_id,
                :dependente_id,
                :mes,
                :ano,
                :valor,
                :status,
                :data_vencimento
            )
        ");

        $stmt->bindValue(':socio_id', $mensalidade->getSocioId(), PDO::PARAM_INT);
        $stmt->bindValue(':dependente_id', $mensalidade->getDependenteId(), PDO::PARAM_INT);
        $stmt->bindValue(':mes', $mensalidade->getMes(), PDO::PARAM_INT);
        $stmt->bindValue(':ano', $mensalidade->getAno(), PDO::PARAM_INT);
        $stmt->bindValue(':valor', $mensalidade->getValor());
        $stmt->bindValue(':status', $mensalidade->getStatus());
        $stmt->bindValue(
            ':data_vencimento',
            $mensalidade->getDataVencimento()->format('Y-m-d')
        );

        $stmt->execute();

        // Get the last inserted ID and return a new instance with it
        $lastId = $this->connection->lastInsertId();
        return new Mensalidade(
            socioId: $mensalidade->getSocioId(),
            dependenteId: $mensalidade->getDependenteId(),
            mes: $mensalidade->getMes(),
            ano: $mensalidade->getAno(),
            valor: $mensalidade->getValor(),
            dataVencimento: $mensalidade->getDataVencimento(),
            status: $mensalidade->getStatus(),
            id: (int)$lastId
        );
    }

    public function update(Mensalidade $mensalidade): void
    {
        $stmt = $this->connection->prepare("
            UPDATE mensalidades SET
                socio_id = :socio_id,
                dependente_id = :dependente_id,
                mes = :mes,
                ano = :ano,
                valor = :valor,
                status = :status,
                data_vencimento = :data_vencimento
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $mensalidade->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':socio_id', $mensalidade->getSocioId(), PDO::PARAM_INT);
        $stmt->bindValue(':dependente_id', $mensalidade->getDependenteId(), PDO::PARAM_INT);
        $stmt->bindValue(':mes', $mensalidade->getMes(), PDO::PARAM_INT);
        $stmt->bindValue(':ano', $mensalidade->getAno(), PDO::PARAM_INT);
        $stmt->bindValue(':valor', $mensalidade->getValor());
        $stmt->bindValue(':status', $mensalidade->getStatus());
        $stmt->bindValue(
            ':data_vencimento',
            $mensalidade->getDataVencimento()->format('Y-m-d')
        );

        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare("
            DELETE FROM mensalidades
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}