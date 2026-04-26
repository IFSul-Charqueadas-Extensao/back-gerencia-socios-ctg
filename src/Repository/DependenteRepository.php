<?php

namespace Repository;

use Database\Database;
use Model\Dependente;
use PDO;


class DependenteRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->connection->query("SELECT * FROM dependentes ORDER BY nome");
        $dependentes = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dependentes[] = $this->mapRowToDependente($row);
        }

        return $dependentes;
    }

    public function findById(int $id): ?Dependente
    {
        $stmt = $this->connection->prepare("SELECT * FROM dependentes WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToDependente($row) : null;
    }

    public function findBySocioTitular(int $socioTitularId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM dependentes WHERE socio_titular_id = :socio_titular_id ORDER BY nome"
        );
        $stmt->bindValue(':socio_titular_id', $socioTitularId, PDO::PARAM_INT);
        $stmt->execute();

        $dependentes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dependentes[] = $this->mapRowToDependente($row);
        }

        return $dependentes;
    }

    public function create(Dependente $dependente): Dependente
    {
        $sql = "INSERT INTO dependentes
                (socio_titular_id, nome, cpf, telefone, foto, identidade,
                 endereco, data_nascimento, data_entrada, categoria_id,
                 dancarino, paga_instrutor)
                VALUES
                (:socio_titular_id, :nome, :cpf, :telefone, :foto, :identidade,
                 :endereco, :data_nascimento, :data_entrada, :categoria_id,
                 :dancarino, :paga_instrutor)";

        $stmt = $this->connection->prepare($sql);
        $this->bindDependenteParams($stmt, $dependente);
        $stmt->execute();

        return $this->findById((int)$this->connection->lastInsertId());
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare("DELETE FROM dependentes WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function mapRowToDependente(array $row): Dependente
    {
        return new Dependente(
            (int)$row['socio_titular_id'],
            $row['nome'],
            $row['cpf'],
            $row['telefone'],
            $row['foto'],
            $row['identidade'],
            $row['endereco'],
            new \DateTime($row['data_nascimento']),
            new \DateTime($row['data_entrada']),
            (int)$row['categoria_id'],
            (bool)$row['dancarino'],
            (bool)$row['paga_instrutor'],
            (int)$row['id']
        );
    }

    private function bindDependenteParams($stmt, Dependente $dependente): void
    {
        $stmt->bindValue(':socio_titular_id', $dependente->getSocioTitularId(), PDO::PARAM_INT);
        $stmt->bindValue(':nome', $dependente->getNome());
        $stmt->bindValue(':cpf', $dependente->getCpf());
        $stmt->bindValue(':telefone', $dependente->getTelefone());
        $stmt->bindValue(':foto', $dependente->getFoto());
        $stmt->bindValue(':identidade', $dependente->getIdentidade());
        $stmt->bindValue(':endereco', $dependente->getEndereco());
        $stmt->bindValue(':data_nascimento', $dependente->getDataNascimento()->format('Y-m-d'));
        $stmt->bindValue(':data_entrada', $dependente->getDataEntrada()->format('Y-m-d'));
        $stmt->bindValue(':categoria_id', $dependente->getCategoriaId(), PDO::PARAM_INT);
        $stmt->bindValue(':dancarino', $dependente->isDancarino(), PDO::PARAM_BOOL);
        $stmt->bindValue(':paga_instrutor', $dependente->isPagaInstrutor(), PDO::PARAM_BOOL);
    }
}
