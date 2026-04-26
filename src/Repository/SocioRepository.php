<?php

namespace Repository;

use Database\Database;
use Model\Socio;
use Enum\StatusSocio;
use PDO;

class SocioRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->connection->query("SELECT * FROM socios ORDER BY nome");
        $socios = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $socios[] = $this->mapRowToSocio($row);
        }

        return $socios;
    }

    public function findById(int $id): ?Socio
    {
        $stmt = $this->connection->prepare("SELECT * FROM socios WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToSocio($row) : null;
    }

    public function findByName(string $nome): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM socios WHERE nome LIKE :nome ORDER BY nome");
        $stmt->bindValue(':nome', "%{$nome}%");
        $stmt->execute();

        $socios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $socios[] = $this->mapRowToSocio($row);
        }

        return $socios;
    }

    public function create(Socio $socio): Socio
    {
        $sql = "INSERT INTO socios
                (nome, cpf, telefone, foto, identidade, endereco, data_nascimento,
                 data_entrada, status, categoria_id, dancarino, paga_instrutor)
                VALUES
                (:nome, :cpf, :telefone, :foto, :identidade, :endereco, :data_nascimento,
                 :data_entrada, :status, :categoria_id, :dancarino, :paga_instrutor)";

        $stmt = $this->connection->prepare($sql);
        $this->bindSocioParams($stmt, $socio);
        $stmt->execute();

        return $this->findById((int)$this->connection->lastInsertId());
    }

    public function update(Socio $socio): void
    {
        $sql = "UPDATE socios SET
                    nome = :nome,
                    cpf = :cpf,
                    telefone = :telefone,
                    endereco = :endereco
                WHERE id = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':nome', $socio->getNome());
        $stmt->bindValue(':cpf', $socio->getCpf());
        $stmt->bindValue(':telefone', $socio->getTelefone());
        $stmt->bindValue(':endereco', $socio->getEndereco());
        $stmt->bindValue(':id', $socio->getId(), PDO::PARAM_INT);
        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare("DELETE FROM socios WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function mapRowToSocio(array $row): Socio
    {
        return new Socio(
            $row['nome'],
            $row['cpf'],
            $row['telefone'],
            $row['foto'],
            $row['identidade'],
            $row['endereco'],
            new \DateTime($row['data_nascimento']),
            new \DateTime($row['data_entrada']),
            StatusSocio::from($row['status']),
            (int)$row['categoria_id'],
            (bool)$row['dancarino'],
            (bool)$row['paga_instrutor'],
            (int)$row['id']
        );
    }

    private function bindSocioParams($stmt, Socio $socio): void
    {
        $stmt->bindValue(':nome', $socio->getNome());
        $stmt->bindValue(':cpf', $socio->getCpf());
        $stmt->bindValue(':telefone', $socio->getTelefone());
        $stmt->bindValue(':foto', $socio->getFoto());
        $stmt->bindValue(':identidade', $socio->getIdentidade());
        $stmt->bindValue(':endereco', $socio->getEndereco());
        $stmt->bindValue(':data_nascimento', $socio->getDataNascimento()->format('Y-m-d'));
        $stmt->bindValue(':data_entrada', $socio->getDataEntrada()->format('Y-m-d'));
        $stmt->bindValue(':status', $socio->getStatus()->value);
        $stmt->bindValue(':categoria_id', $socio->getCategoriaId(), PDO::PARAM_INT);
        $stmt->bindValue(':dancarino', $socio->isDancarino(), PDO::PARAM_BOOL);
        $stmt->bindValue(':paga_instrutor', $socio->isPagaInstrutor(), PDO::PARAM_BOOL);
    }
}