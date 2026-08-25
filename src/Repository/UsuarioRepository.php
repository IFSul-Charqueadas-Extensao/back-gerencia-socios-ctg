<?php

namespace Repository;

use Database\Database;
use Model\Usuario;
use PDO;

class UsuarioRepository
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    //converte uma linha do banco em objeto Usuario
    private function mapRow(array $row): Usuario
    {
        return new Usuario(
            nome: $row['nome'],
            email: $row['email'],
            senhaHash: $row['senha_hash'],
            role: $row['role'],
            ativo: (bool) $row['ativo'],
            id: (int) $row['id']
        );
    }

    public function findAll(): array
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM usuarios
            ORDER BY nome
        ");

        $stmt->execute();

        $usuarios = [];

        while ($row = $stmt->fetch()) {
            $usuarios[] = $this->mapRow($row);
        }

        return $usuarios;
    }

    public function findById(int $id): ?Usuario
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM usuarios
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->mapRow($row);
    }

    //usado no login
    public function findByEmail(string $email): ?Usuario
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM usuarios
            WHERE email = :email
        ");

        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->mapRow($row);
    }

    public function create(Usuario $usuario): Usuario
    {
        $stmt = $this->connection->prepare("
            INSERT INTO usuarios (
                nome,
                email,
                senha_hash,
                role,
                ativo
            )
            VALUES (
                :nome,
                :email,
                :senha_hash,
                :role,
                :ativo
            )
        ");

        $stmt->bindValue(':nome', $usuario->getNome());
        $stmt->bindValue(':email', $usuario->getEmail());
        $stmt->bindValue(':senha_hash', $usuario->getSenhaHash());
        $stmt->bindValue(':role', $usuario->getRole());
        $stmt->bindValue(':ativo', $usuario->isAtivo(), PDO::PARAM_BOOL);

        $stmt->execute();

        $lastId = $this->connection->lastInsertId();

        return new Usuario(
            nome: $usuario->getNome(),
            email: $usuario->getEmail(),
            senhaHash: $usuario->getSenhaHash(),
            role: $usuario->getRole(),
            ativo: $usuario->isAtivo(),
            id: (int) $lastId
        );
    }

    //atualiza os dados cadastrais; a senha tem método próprio
    public function update(Usuario $usuario): void
    {
        $stmt = $this->connection->prepare("
            UPDATE usuarios SET
                nome = :nome,
                email = :email,
                role = :role,
                ativo = :ativo
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $usuario->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':nome', $usuario->getNome());
        $stmt->bindValue(':email', $usuario->getEmail());
        $stmt->bindValue(':role', $usuario->getRole());
        $stmt->bindValue(':ativo', $usuario->isAtivo(), PDO::PARAM_BOOL);

        $stmt->execute();
    }

    public function updateSenha(int $id, string $senhaHash): void
    {
        $stmt = $this->connection->prepare("
            UPDATE usuarios SET
                senha_hash = :senha_hash
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':senha_hash', $senhaHash);

        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    //conta quantos administradores ativos existem.
    //serve para impedir que o último admin seja apagado ou rebaixado
    public function contarAdminsAtivos(): int
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) AS total FROM usuarios
            WHERE role = 'admin' AND ativo = true
        ");

        $stmt->execute();
        $row = $stmt->fetch();

        return (int) $row['total'];
    }
}
