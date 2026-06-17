<?php

namespace Repository;

use Database\Database;
use Model\Dependente;
use PDO;
use DateTime;

class DependenteRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->connection->query("SELECT * FROM dependentes ORDER BY nome_completo");
        $dependentes = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dependentes[] = $this->mapRowToDependente($row);
        }

        return $dependentes;
    }

    public function findById(int $id): ?Dependente
    {
        $stmt = $this->connection->prepare("SELECT * FROM dependentes WHERE id = ?");
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToDependente($row) : null;
    }

    public function findBySocioTitular(int $socioTitularId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM dependentes WHERE socio_titular_id = ? ORDER BY nome_completo"
        );
        $stmt->execute([$socioTitularId]);

        $dependentes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dependentes[] = $this->mapRowToDependente($row);
        }

        return $dependentes;
    }

    public function create(Dependente $dependente): Dependente
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO dependentes 
             (socio_titular_id, nome_completo, cpf, telefone, foto, data_nascimento, dancarino) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $dependente->getSocioTitularId(),
            $dependente->getNomeCompleto(),
            $dependente->getCpf(),
            $dependente->getTelefone(),
            $dependente->getFoto(),
            $dependente->getDataNascimento()->format('Y-m-d'),
            $dependente->isDancarino() ? 1 : 0,
        ]);

        $dependente->setId((int)$this->connection->lastInsertId());
        return $dependente;
    }

    public function update(Dependente $dependente): void
    {
        $stmt = $this->connection->prepare(
            "UPDATE dependentes SET 
             socio_titular_id = ?, nome_completo = ?, cpf = ?, telefone = ?, foto = ?,
             data_nascimento = ?, dancarino = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $dependente->getSocioTitularId(),
            $dependente->getNomeCompleto(),
            $dependente->getCpf(),
            $dependente->getTelefone(),
            $dependente->getFoto(),
            $dependente->getDataNascimento()->format('Y-m-d'),
            $dependente->isDancarino() ? 1 : 0,
            $dependente->getId()
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare("DELETE FROM dependentes WHERE id = ?");
        $stmt->execute([$id]);
    }

    private function mapRowToDependente(array $row): Dependente
    {
        return new Dependente(
            socioTitularId: (int)$row['socio_titular_id'],
            nomeCompleto: $row['nome_completo'],
            cpf: $row['cpf'],
            telefone: $row['telefone'] ?? '',
            dataNascimento: new DateTime($row['data_nascimento']),
            dancarino: (bool)$row['dancarino'],
            foto: $row['foto'] ?? null,
            id: (int)$row['id']
        );
    }
}

