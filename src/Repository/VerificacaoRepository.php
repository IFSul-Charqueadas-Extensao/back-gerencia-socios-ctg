<?php

namespace Repository;

use Database\Database;
use PDO;

//Acesso a dados da verificação pública de adimplência.
//Só expõe o mínimo necessário para a regra (nunca foto, endereço, telefone etc.).
class VerificacaoRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    //busca o sócio por CPF já normalizado (11 dígitos).
    //o CPF é gravado com máscara (varchar(14)), então compara-se sem pontuação
    public function buscarSocioPorCpf(string $cpfNormalizado): ?array
    {
        $stmt = $this->connection->prepare("
            SELECT id, nome_completo, status, data_entrada
            FROM socios
            WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = :cpf
            LIMIT 1
        ");
        $stmt->bindValue(':cpf', $cpfNormalizado, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    //meses com mensalidade 'Pago' do titular (dependentes ficam de fora)
    public function mesesPagosDoTitular(int $socioId): array
    {
        $stmt = $this->connection->prepare("
            SELECT ano, mes
            FROM mensalidades
            WHERE socio_id = :socio_id AND dependente_id IS NULL AND status = 'Pago'
        ");
        $stmt->bindValue(':socio_id', $socioId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //--- rate limit ---

    //apaga tentativas antigas (mantém a tabela pequena)
    public function limparTentativasAntigas(int $segundos): void
    {
        $stmt = $this->connection->prepare("
            DELETE FROM verificacao_tentativas
            WHERE tentativa_em < (NOW() - INTERVAL :s SECOND)
        ");
        $stmt->bindValue(':s', $segundos, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function contarTentativas(string $ipHash, int $segundos): int
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) FROM verificacao_tentativas
            WHERE ip_hash = :ip AND tentativa_em >= (NOW() - INTERVAL :s SECOND)
        ");
        $stmt->bindValue(':ip', $ipHash, PDO::PARAM_STR);
        $stmt->bindValue(':s', $segundos, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function registrarTentativa(string $ipHash): void
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO verificacao_tentativas (ip_hash) VALUES (:ip)"
        );
        $stmt->bindValue(':ip', $ipHash, PDO::PARAM_STR);
        $stmt->execute();
    }
}
