<?php

namespace Controller;

use Error\APIException;
use Http\Request;
use Http\Response;
use Model\Pagamento;
use Service\PagamentoService;
use DateTime;

class PagamentoController
{
    private PagamentoService $service;

    public function __construct()
    {
        $this->service = new PagamentoService();
    }

    public function processRequest(Request $request): void
    {
        $id = $request->getId();
        $method = $request->getMethod();

        switch ($method) {

            case "GET":

                if ($id) {
                    $pagamento = $this->service->findById($id);

                    if (!$pagamento) {
                        throw new APIException("Pagamento not found!", 404);
                    }

                    Response::send($pagamento);
                    return;
                }

                Response::send($this->service->findAll());
                break;

            case "POST":

                $data = $request->getBody();

                $pagamento = new Pagamento(
                    mensalidadeId: $data['mensalidade_id'],
                    dataPagamento: new DateTime($data['data_pagamento']),
                    formaPagamento: $data['forma_pagamento'],
                    valorPago: $data['valor_pago'],
                    multaJurosAplicados: $data['multa_juros_aplicados'] ?? 0
                );

                $created = $this->service->create($pagamento);

                Response::send($created, 201);

                break;

            case "PUT":

                if (!$id) {
                    throw new APIException("ID is required!", 400);
                }

                $data = $request->getBody();

                $pagamento = new Pagamento(
                    mensalidadeId: $data['mensalidade_id'],
                    dataPagamento: new DateTime($data['data_pagamento']),
                    formaPagamento: $data['forma_pagamento'],
                    valorPago: $data['valor_pago'],
                    multaJurosAplicados: $data['multa_juros_aplicados'] ?? 0,
                    id: $id
                );

                $this->service->update($pagamento);

                Response::send([
                    "message" => "Pagamento updated successfully"
                ]);

                break;

            case "DELETE":

                if (!$id) {
                    throw new APIException("ID is required!", 400);
                }

                $this->service->delete($id);

                Response::send([
                    "message" => "Pagamento deleted successfully"
                ]);

                break;

            default:
                throw new APIException("Method not allowed!", 405);
        }
    }
}