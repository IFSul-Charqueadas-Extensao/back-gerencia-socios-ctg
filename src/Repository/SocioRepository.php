<?php

namespace Repository;

use Database\Database;
use Model\Socio;
use Model\CartaoTrad;
use Util\Endereco;
use Util\StatusSocio;
use Util\CategoriaSocio;
use PDO;
use DateTime;

class SocioRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM socios");
        $stmt->execute();

        $socios = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $socios[] = $this->mapRow($row);
        }

        return $socios;
    }

    public function findById(int $id): ?Socio
    {
        $stmt = $this->connection->prepare("SELECT * FROM socios WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->mapRow($row);
    }

    public function findByName(string $name): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM socios WHERE nome_completo LIKE :nome");
        $stmt->bindValue(':nome', "%$name%");
        $stmt->execute();

        $socios = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $socios[] = $this->mapRow($row);
        }

        return $socios;
    }

    public function create(Socio $socio): Socio
    {
        $stmt = $this->connection->prepare("
            INSERT INTO socios (
                nome_completo,
                cpf,
                email,
                telefone,
                foto,
        
                endereco,
                data_nascimento,
                data_entrada,
                categoria_id,
                status,
                dancarino,
                paga_instrutor,
                cartao_trad_id
            )
            VALUES (
                :nome_completo,
                :cpf,
                :email,
                :telefone,
                :foto,
            
                :endereco,
                :data_nascimento,
                :data_entrada,
                :categoria_id,
                :status,
                :dancarino,
                :paga_instrutor,
                :cartao_trad_id
            )
        ");

        $stmt->execute([
            ':nome_completo' => $socio->getNome(),
            ':cpf' => $socio->getCpf(),
            ':email' => $socio->getEmail(),
            ':telefone' => $socio->getTelefone(),
            ':foto' => $socio->getFoto(),
            ':endereco' => $this->enderecoToString($socio->getEndereco()),
            ':data_nascimento' => $socio->getDataNascimento()->format('Y-m-d'),
            ':data_entrada' => $socio->getDataEntrada()->format('Y-m-d'),
            ':categoria_id' => $socio->getCategoria()->value,
            ':status' => $socio->getStatus()->value,
            ':dancarino' => $socio->isDancarino() ? 1 : 0,
            ':paga_instrutor' => $socio->isPagaInstrutor() ? 1 : 0,
            ':cartao_trad_id' => $socio->getCartaoTrad()?->getId()
        ]);

        $id = (int)$this->connection->lastInsertId();

        $created = new Socio(
            $socio->getNome(),
            $socio->getCpf(),
            $socio->getTelefone(),
            $socio->getEmail(),
            $socio->getFoto(),
            $socio->getEndereco(),
            $socio->getDataNascimento(),
            $socio->getDataEntrada(),
            $socio->getStatus(),
            $socio->getCategoria(),
            $socio->isDancarino(),
            $socio->isPagaInstrutor(),
            $id
        );

        if ($socio->getCartaoTrad()) {
            $created->setCartaoTrad($socio->getCartaoTrad());
        }

        return $created;
    }

    public function update(Socio $socio): void
    {
        $stmt = $this->connection->prepare("
            UPDATE socios SET
                nome_completo = :nome_completo,
                cpf = :cpf,
                telefone = :telefone,
                email = :email,
                foto = :foto,
                endereco = :endereco,
                data_nascimento = :data_nascimento,
                data_entrada = :data_entrada,
                categoria_id = :categoria_id,
                status = :status,
                dancarino = :dancarino,
                paga_instrutor = :paga_instrutor,
                cartao_trad_id = :cartao_trad_id
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $socio->getId(),
            ':nome_completo' => $socio->getNome(),
            ':cpf' => $socio->getCpf(),
            ':telefone' => $socio->getTelefone(),
            ':email' => $socio->getEmail(),
            ':foto' => $socio->getFoto(),
            ':endereco' => $this->enderecoToString($socio->getEndereco()),
            ':data_nascimento' => $socio->getDataNascimento()->format('Y-m-d'),
            ':data_entrada' => $socio->getDataEntrada()->format('Y-m-d'),
            ':categoria_id' => $socio->getCategoria()->value,
            ':status' => $socio->getStatus()->value,
            ':dancarino' => $socio->isDancarino() ? 1 : 0,
            ':paga_instrutor' => $socio->isPagaInstrutor() ? 1 : 0,
            ':cartao_trad_id' => $socio->getCartaoTrad()?->getId()
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare("DELETE FROM socios WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    private function mapRow(array $row): Socio
    {
        $endereco = $this->stringToEndereco($row['endereco']);

        $socio = new Socio(
            $row['nome_completo'],
            $row['cpf'],
            $row['telefone'],
            $row['email'] ?? '',
            $row['foto'] ?? '',
            $endereco,
            new DateTime($row['data_nascimento']),
            new DateTime($row['data_entrada']),
            StatusSocio::from($row['status']),
            CategoriaSocio::from((string)$row['categoria_id']),
            (bool)$row['dancarino'],
            (bool)$row['paga_instrutor'],
            (int)$row['id']
        );

        if (!empty($row['cartao_trad_id'])) {
            $cartao = new CartaoTrad(
                null,
                null,
                new DateTime(),
                false,
                0,
                (int)$row['cartao_trad_id']
            );
            $socio->setCartaoTrad($cartao);
        }

        return $socio;
    }

    private function enderecoToString(Endereco $e): string
    {
        return implode('|', [
            $e->getLogradouro(),
            $e->getNumero(),
            $e->getComplemento(),
            $e->getBairro(),
            $e->getCidade(),
            $e->getEstado(),
            $e->getCep()
        ]);
    }

    private function stringToEndereco(string $str): Endereco
    {
        $parts = explode('|', $str);
        
        return new Endereco(
            $parts[0] ?? '', // logradouro
            $parts[1] ?? '', // numero
            $parts[3] ?? '', // bairro
            $parts[4] ?? '', // cidade
            $parts[5] ?? '', // estado
            $parts[6] ?? '', // cep
            empty($parts[2]) ? null : $parts[2] // complemento
        );
    }
}