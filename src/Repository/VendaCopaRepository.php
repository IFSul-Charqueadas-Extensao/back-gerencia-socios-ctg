<?php

namespace Repository;

use Database\Database;
use Model\VendaCopa;
use PDO;
use DateTime;

class VendaCopaRepository
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM vendas_copa
            ORDER BY data_venda DESC, id DESC
        ");

        $stmt->execute();

        $vendas = [];

        while ($row = $stmt->fetch()) {
            $vendas[] = $this->mapearVenda($row);
        }

        return $vendas;
    }

    public function findById(int $id): ?VendaCopa
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM vendas_copa
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->mapearVenda($row);
    }

    public function create(VendaCopa $venda): VendaCopa
    {
        $stmt = $this->connection->prepare("
            INSERT INTO vendas_copa (
                produto,
                valor,
                forma_pagamento
            )
            VALUES (
                :produto,
                :valor,
                :forma_pagamento
            )
        ");

        $stmt->bindValue(':produto', $venda->getProduto());
        $stmt->bindValue(':valor', $venda->getValor());
        $stmt->bindValue(
            ':forma_pagamento',
            $venda->getFormaPagamento()
        );

        $stmt->execute();

        $id = (int) $this->connection->lastInsertId();

        return $this->findById($id);
    }

    public function update(VendaCopa $venda): void
    {
        $stmt = $this->connection->prepare("
            UPDATE vendas_copa SET
                produto = :produto,
                valor = :valor,
                forma_pagamento = :forma_pagamento
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $venda->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':produto', $venda->getProduto());
        $stmt->bindValue(':valor', $venda->getValor());
        $stmt->bindValue(
            ':forma_pagamento',
            $venda->getFormaPagamento()
        );

        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare("
            DELETE FROM vendas_copa
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function mapearVenda(array $row): VendaCopa
    {
        return new VendaCopa(
            produto: $row['produto'],
            valor: (float) $row['valor'],
            formaPagamento: $row['forma_pagamento'],
            dataVenda: new DateTime($row['data_venda']),
            id: (int) $row['id']
        );
    }
}
