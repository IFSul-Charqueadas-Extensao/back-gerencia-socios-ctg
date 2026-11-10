<?php

namespace Controller;

use Error\APIException;
use Http\Request;
use Http\Response;
use Service\VerificacaoService;

//POST /api/verificacao  { "cpf": "123.456.789-09" }  — rota PÚBLICA (sem token).
//Usa POST e não GET para que o CPF não fique na URL (logs do Apache, histórico
//do navegador, cabeçalho Referer).
class VerificacaoController
{
    private VerificacaoService $service;

    public function __construct()
    {
        $this->service = new VerificacaoService();
    }

    public function processRequest(Request $request): void
    {
        if ($request->getMethod() !== "POST") {
            throw new APIException("Method not allowed!", 405);
        }

        $cpf = $request->getBody()['cpf'] ?? null;

        //CPF ausente ou não-texto segue o mesmo caminho (rate limit + "CPF inválido")
        if (!is_string($cpf)) {
            $cpf = '';
        }

        //resposta com situação financeira não deve ser guardada em cache
        header('Cache-Control: no-store');

        Response::send($this->service->verificar($cpf, $_SERVER['REMOTE_ADDR'] ?? 'desconhecido'));
    }
}
