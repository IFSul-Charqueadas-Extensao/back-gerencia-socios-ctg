<?php
namespace Controller;

use Error\APIException;
use Http\Request;
use Http\Response;
use Service\RelatorioService;

class RelatorioController
{
    private RelatorioService $service;

    public function __construct()
    {
        $this->service = new RelatorioService();
    }

    public function processRequest(Request $request): void
    {
        //recupera o "id" que na verdade será o tipo do relatório (ex: socios, financeiro)
        $tipo = $request->getId();
        $method = $request->getMethod();

        if ($method !== "GET") {
            throw new APIException("Method not allowed!", 405);
        }

        if ($tipo === "socios") {
            $response = $this->service->getSociosReport();
            Response::send($response);
        } else if ($tipo === "financeiro") {
            $response = $this->service->getFinanceiroReport();
            Response::send($response);
        } else {
            // Se for outro tipo, não foi encontrado
            throw new APIException("Resource not found or invalid report type!", 404);
        }
    }
}
