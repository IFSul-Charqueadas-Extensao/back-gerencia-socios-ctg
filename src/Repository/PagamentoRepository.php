<?php

namespace Repository;

use Database\Database;
use Model\Pagamento;
use PDO;
use DateTime;

class PagamentoRepository
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM pagamentos");
        $stmt->execute();

        $pagamentos = [];

        while ($row = $stmt->fetch()) {
            $pagamento = new Pagamento(
                mensalidadeId: $row['mensalidade_id'],
                dataPagamento: new DateTime($row['data_pagamento']),
                formaPagamento: $row['forma_pagamento'],
                valorPago: $row['valor_pago'],
                multaJurosAplicados: $row['multa_juros_aplicados'],
                id: $row['id']
            );

            $pagamentos[] = $pagamento;
        }

        return $pagamentos;
    }

    public function findById(int $id): ?Pagamento
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM pagamentos
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new Pagamento(
            mensalidadeId: $row['mensalidade_id'],
            dataPagamento: new DateTime($row['data_pagamento']),
            formaPagamento: $row['forma_pagamento'],
            valorPago: $row['valor_pago'],
            multaJurosAplicados: $row['multa_juros_aplicados'],
            id: $row['id']
        );
    }

    public function create(Pagamento $pagamento): Pagamento
    {
        $stmt = $this->connection->prepare("
            INSERT INTO pagamentos (
                mensalidade_id,
                data_pagamento,
                forma_pagamento,
                valor_pago,
                multa_juros_aplicados
            )
            VALUES (
                :mensalidade_id,
                :data_pagamento,
                :forma_pagamento,
                :valor_pago,
                :multa_juros_aplicados
            )
        ");

        $stmt->bindValue(
            ':mensalidade_id',
            $pagamento->getMensalidadeId(),
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':data_pagamento',
            $pagamento->getDataPagamento()->format('Y-m-d')
        );

        $stmt->bindValue(
            ':forma_pagamento',
            $pagamento->getFormaPagamento()
        );

        $stmt->bindValue(':valor_pago', $pagamento->getValorPago());

        $stmt->bindValue(
            ':multa_juros_aplicados',
            $pagamento->getMultaJurosAplicados()
        );

        $stmt->execute();

        // Get the last inserted ID and return a new instance with it
        $lastId = $this->connection->lastInsertId();
        return new Pagamento(
            mensalidadeId: $pagamento->getMensalidadeId(),
            dataPagamento: $pagamento->getDataPagamento(),
            formaPagamento: $pagamento->getFormaPagamento(),
            valorPago: $pagamento->getValorPago(),
            multaJurosAplicados: $pagamento->getMultaJurosAplicados(),
            id: (int)$lastId
        );
    }

    public function update(Pagamento $pagamento): void
    {
        $stmt = $this->connection->prepare("
            UPDATE pagamentos SET
                mensalidade_id = :mensalidade_id,
                data_pagamento = :data_pagamento,
                forma_pagamento = :forma_pagamento,
                valor_pago = :valor_pago,
                multa_juros_aplicados = :multa_juros_aplicados
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $pagamento->getId(), PDO::PARAM_INT);

        $stmt->bindValue(
            ':mensalidade_id',
            $pagamento->getMensalidadeId(),
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':data_pagamento',
            $pagamento->getDataPagamento()->format('Y-m-d')
        );

        $stmt->bindValue(
            ':forma_pagamento',
            $pagamento->getFormaPagamento()
        );

        $stmt->bindValue(':valor_pago', $pagamento->getValorPago());

        $stmt->bindValue(
            ':multa_juros_aplicados',
            $pagamento->getMultaJurosAplicados()
        );

        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare("
            DELETE FROM pagamentos
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}