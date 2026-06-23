<?php

namespace Controller;

use Error\APIException;
use Http\Request;
use Http\Response;
use Model\Dependente;
use Service\DependenteService;
use DateTime;

class DependenteController
{
    private DependenteService $dependenteService;

    public function __construct()
    {
        $this->dependenteService = new DependenteService();
    }

    public function processRequest(Request $request): void
    {
        $id = $request->getId();
        $method = $request->getMethod();

        switch ($method) {

            case "GET":
                if ($id) {
                    $dependente = $this->dependenteService->findById((int)$id);

                    if (!$dependente) {
                        throw new APIException("Dependente não encontrado!", 404);
                    }

                    Response::send($dependente);
                    return;
                }

                Response::send($this->dependenteService->findAll());
                break;

            case "POST":
                $data = $request->getBody();

                $dependente = new Dependente(
                    socioTitularId: (int)$data['socio_titular_id'],
                    nomeCompleto: $data['nome_completo'],
                    cpf: $data['cpf'],
                    telefone: $data['telefone'],
                    dataNascimento: new DateTime($data['data_nascimento']),
                    dancarino: (bool)$data['dancarino'],
                    foto: $data['foto'] ?? null
                );

                $created = $this->dependenteService->create($dependente);

                Response::send($created, 201);
                break;

            case "PUT":
                if (!$id) {
                    throw new APIException("ID é obrigatório!", 400);
                }

                $data = $request->getBody();

                $dependente = new Dependente(
                    socioTitularId: (int)$data['socio_titular_id'],
                    nomeCompleto: $data['nome_completo'],
                    cpf: $data['cpf'],
                    telefone: $data['telefone'],
                    dataNascimento: new DateTime($data['data_nascimento']),
                    dancarino: (bool)$data['dancarino'],
                    foto: $data['foto'] ?? null,
                    id: (int)$id
                );

                $this->dependenteService->update($dependente);

                Response::send([
                    "message" => "Dependente atualizado com sucesso"
                ]);

                break;

            case "DELETE":
                if (!$id) {
                    throw new APIException("ID é obrigatório!", 400);
                }

                $this->dependenteService->delete((int)$id);

                Response::send([
                    "message" => "Dependente deletado com sucesso"
                ]);

                break;

            default:
                throw new APIException("Método não permitido!", 405);
        }
    }
}
