<?php

namespace Controller;

use Error\APIException;
use Http\Request;
use Http\Response;
use Model\Mensalidade;
use Service\MensalidadeService;
use DateTime;

class MensalidadeController
{
    private MensalidadeService $service;

    public function __construct()
    {
        $this->service = new MensalidadeService();
    }

    public function processRequest(Request $request): void
    {
        $id = $request->getId();
        $method = $request->getMethod();

        switch ($method) {

            case "GET":

                if ($id) {
                    $mensalidade = $this->service->findById($id);

                    if (!$mensalidade) {
                        throw new APIException("Mensalidade not found!", 404);
                    }

                    Response::send($mensalidade);
                    return;
                }

                Response::send($this->service->findAll());
                break;

            case "POST":

                $data = $request->getBody();

                $mensalidade = new Mensalidade(
                    socioId: $data['socio_id'],
                    dependenteId: $data['dependente_id'] ?? null,
                    mes: $data['mes'],
                    ano: $data['ano'],
                    valor: $data['valor'],
                    dataVencimento: new DateTime($data['data_vencimento']),
                    status: $data['status'] ?? 'Pendente'
                );

                $created = $this->service->create($mensalidade);

                Response::send($created, 201);
                break;

            case "PUT":

                if (!$id) {
                    throw new APIException("ID is required!", 400);
                }

                $data = $request->getBody();

                $mensalidade = new Mensalidade(
                    socioId: $data['socio_id'],
                    dependenteId: $data['dependente_id'] ?? null,
                    mes: $data['mes'],
                    ano: $data['ano'],
                    valor: $data['valor'],
                    dataVencimento: new DateTime($data['data_vencimento']),
                    status: $data['status'],
                    id: $id
                );

                $this->service->update($mensalidade);

                Response::send([
                    "message" => "Mensalidade updated successfully"
                ]);

                break;

            case "DELETE":

                if (!$id) {
                    throw new APIException("ID is required!", 400);
                }

                $this->service->delete($id);

                Response::send([
                    "message" => "Mensalidade deleted successfully"
                ]);

                break;

            default:
                throw new APIException("Method not allowed!", 405);
        }
    }
}