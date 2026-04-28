<?php

namespace Controller;

use Error\APIException;
use Http\Request;
use Http\Response;
use Model\Socio;
use Service\SocioService;
use DateTime;
use StatusSocio;

class SocioController{
    private SocioService $socioService;

    public function __construct(){
        $this->socioService = new SocioService();
    }

    public function processRequest(Request $request) : void{
        $id = $request->getId();
        $method = $request->getMethod();

        switch ($method) {

            case "GET":
                if ($id) {
                    $socio = $this->socioService->findById((int)$id);

                    if (!$socio) {
                        throw new APIException("Sócio não encontrado!", 404);
                    }

                    Response::send($socio);
                    return;
                }

                Response::send($this->socioService->findAll());
                break;

            case "POST":
                $data = $request->getBody();

                $socio = new Socio(
                    $data['nome'],
                    $data['cpf'],
                    $data['telefone'],
                    $data['foto'] ?? '',
                    $data['identidade'],
                    $data['endereco'],
                    new DateTime($data['data_nascimento']),
                    new DateTime($data['data_entrada']),
                    StatusSocio::from($data['status']),
                    (int)$data['categoria_id'],
                    (bool)$data['dancarino'],
                    (bool)$data['paga_instrutor']
                );

                $created = $this->socioService->create($socio);

                Response::send($created, 201);
                break;

            case "PUT":
                if (!$id) {
                    throw new APIException("ID é obrigatório!", 400);
                }

                $data = $request->getBody();

                $socio = new Socio(
                    $data['nome'],
                    $data['cpf'],
                    $data['telefone'],
                    $data['foto'] ?? '',
                    $data['identidade'],
                    $data['endereco'],
                    new DateTime($data['data_nascimento']),
                    new DateTime($data['data_entrada']),
                    StatusSocio::from($data['status']),
                    (int)$data['categoria_id'],
                    (bool)$data['dancarino'],
                    (bool)$data['paga_instrutor'],
                    $id
                );

                $this->socioService->update($socio);

                Response::send([
                    "message" => "Sócio atualizado com sucesso"
                ]);

                break;

            case "DELETE":
                if (!$id) {
                    throw new APIException("ID é obrigatório!", 400);
                }

                $this->socioService->delete($id);

                Response::send([
                    "message" => "Sócio excluído com sucesso"
                ]);

                break;

            default:
                throw new APIException("Método não permitido!", 405);
        }
    }
}

?>
