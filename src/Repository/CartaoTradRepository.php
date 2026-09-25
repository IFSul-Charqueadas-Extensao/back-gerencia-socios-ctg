<?php
namespace Repository;

use Database\Database;
use Model\CartaoTrad;
use PDO;
use DateTime;

class CartaoTradRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM cartao_tradicionalista");
        $result = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $this->mapRowToCartao($row);
        }

        return $result;
    }

    public function findById(int $id): ?CartaoTrad
    {
        $stmt = $this->conn->prepare("SELECT * FROM cartao_tradicionalista WHERE id = ?");
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToCartao($row) : null;
    }

    public function findBySocioId(int $socioId): ?CartaoTrad
    {
        $stmt = $this->conn->prepare("SELECT * FROM cartao_tradicionalista WHERE socio_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$socioId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToCartao($row) : null;
    }

    public function create(CartaoTrad $cartao): CartaoTrad
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO cartao_tradicionalista 
             (socio_id, dependente_id, data_solicitacao, pago, valor, matricula, data_validade) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $cartao->getSocioId(),
            $cartao->getDependenteId(),
            $cartao->getDataSolicitacao()->format('Y-m-d'),
            $cartao->isPago() ? 1 : 0,
            $cartao->getValor(),
            $cartao->getMatricula(),
            $cartao->getDataValidade()?->format('Y-m-d')
        ]);

        $cartao->setId((int)$this->conn->lastInsertId());
        return $cartao;
    }

    public function update(CartaoTrad $cartao): void
    {
        $stmt = $this->conn->prepare(
            "UPDATE cartao_tradicionalista SET 
             socio_id = ?, dependente_id = ?, data_solicitacao = ?, pago = ?, valor = ?, 
             matricula = ?, data_validade = ? 
             WHERE id = ?"
        );

        $stmt->execute([
            $cartao->getSocioId(),
            $cartao->getDependenteId(),
            $cartao->getDataSolicitacao()->format('Y-m-d'),
            $cartao->isPago() ? 1 : 0,
            $cartao->getValor(),
            $cartao->getMatricula(),
            $cartao->getDataValidade()?->format('Y-m-d'),
            $cartao->getId()
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->conn->prepare("DELETE FROM cartao_tradicionalista WHERE id = ?");
        $stmt->execute([$id]);
    }

    private function mapRowToCartao(array $row): CartaoTrad
    {
        return new CartaoTrad(
            socioId: isset($row['socio_id']) && $row['socio_id'] !== null ? (int)$row['socio_id'] : null,
            dependenteId: isset($row['dependente_id']) && $row['dependente_id'] !== null ? (int)$row['dependente_id'] : null,
            dataSolicitacao: new DateTime($row['data_solicitacao']),
            pago: (bool)$row['pago'],
            valor: (float)$row['valor'],
            id: (int)$row['id'],
            matricula: $row['matricula'] ?? null,
            dataValidade: isset($row['data_validade']) && $row['data_validade'] !== null ? new DateTime($row['data_validade']) : null
        );
    }
}
