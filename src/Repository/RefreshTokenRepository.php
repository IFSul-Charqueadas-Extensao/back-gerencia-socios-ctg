<?php

namespace Repository;

use Database\Database;
use PDO;

//Armazena os refresh tokens emitidos.
//
//O banco guarda apenas o sha256 do token, nunca o valor original: assim,
//um vazamento da tabela não permite reutilizar as sessões dos usuários.
class RefreshTokenRepository
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function create(int $usuarioId, string $tokenHash, string $expiraEm): void
    {
        $stmt = $this->connection->prepare("
            INSERT INTO refresh_tokens (
                usuario_id,
                token_hash,
                expira_em
            )
            VALUES (
                :usuario_id,
                :token_hash,
                :expira_em
            )
        ");

        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':token_hash', $tokenHash);
        $stmt->bindValue(':expira_em', $expiraEm);

        $stmt->execute();
    }

    //busca um token válido: não revogado e ainda dentro do prazo
    public function findValido(string $tokenHash): ?array
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM refresh_tokens
            WHERE token_hash = :token_hash
              AND revogado = false
              AND expira_em > NOW()
        ");

        $stmt->bindValue(':token_hash', $tokenHash);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function revogar(string $tokenHash): void
    {
        $stmt = $this->connection->prepare("
            UPDATE refresh_tokens SET
                revogado = true
            WHERE token_hash = :token_hash
        ");

        $stmt->bindValue(':token_hash', $tokenHash);
        $stmt->execute();
    }

    //revoga todas as sessões do usuário.
    //usado quando a senha é trocada ou a conta é desativada
    public function revogarDoUsuario(int $usuarioId): void
    {
        $stmt = $this->connection->prepare("
            UPDATE refresh_tokens SET
                revogado = true
            WHERE usuario_id = :usuario_id
        ");

        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
    }

    //remove tokens já expirados ou revogados, para a tabela não crescer sem fim
    public function limparExpirados(): void
    {
        $stmt = $this->connection->prepare("
            DELETE FROM refresh_tokens
            WHERE expira_em < NOW() OR revogado = true
        ");

        $stmt->execute();
    }
}
