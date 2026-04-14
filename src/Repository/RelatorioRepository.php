<?php

namespace Repository;

use Database\Database;
use PDO;

class RelatorioRepository
{
    private $connection;

    public function __construct()
    {
        //obtém a conexão
        $this->connection = Database::getConnection();
    }

    public function getTotalSocios(): array
    {
        //executa a consulta no banco
        $stmt = $this->connection->prepare("SELECT COUNT(*) as total_socios FROM socios");
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ['total_socios' => $row ? (int)$row['total_socios'] : 0];
    }

    public function getResumoFinanceiro(): array
    {
        //executa a consulta para pagamentos
        $stmtPagamentos = $this->connection->prepare("SELECT SUM(valor_pago) as total_pago FROM pagamentos");
        $stmtPagamentos->execute();
        $rowPagamentos = $stmtPagamentos->fetch(PDO::FETCH_ASSOC);
        $total_pago = $rowPagamentos && $rowPagamentos['total_pago'] !== null ? (float)$rowPagamentos['total_pago'] : 0.0;

        //executa a consulta para mensalidades
        $stmtMensalidades = $this->connection->prepare("SELECT SUM(valor) as total FROM mensalidades");
        $stmtMensalidades->execute();
        $rowMensalidades = $stmtMensalidades->fetch(PDO::FETCH_ASSOC);
        $total_mensalidades = $rowMensalidades && $rowMensalidades['total'] !== null ? (float)$rowMensalidades['total'] : 0.0;

        return [
            'total_valor_pago' => $total_pago,
            'total_valor_mensalidades' => $total_mensalidades
        ];
    }
}
