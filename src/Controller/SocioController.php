<?php

namespace Controller;

use Error\APIException;
use Http\Request;
use Http\Response;
use Service\SocioService;

class SocioController{
    private SocioService $socioService;

    public function __construct(){
        $this->socioService = new SocioService();
    }

    public function processRequest(Request $request) : void{
        $method = $request->getMethod();
        $id = $request->getId();
        $data = $request->getBody();

        switch ($method) {

            case "GET":
                if ($id) {
                    $response = $this->service->getSocioById((int)$id);
                } else {
                    $nome = $request->getQueryParam("nome");
                    $response = $this->service->getSocios($nome);
                }
                break;

            case "POST":
                $response = $this->socioService->createSocio(
                    $data['nome'],
                    $data['cpf'],
                    $data['telefone'],
                    $data['foto'],
                    $data['identidade'],
                    $data['endereco'],
                    $data['data_nascimento'],
                    $data['data_entrada'],
                    $data['status'],
                    (int)$data['categoria_id'],
                    isset($data['dancarino']),
                    isset($data['paga_instrutor'])
                );
                break;

            case "PUT":
                if (!$id)
                    throw new APIException("ID não informado!", 400);

                $response = $this->socioService->updateSocio(
                    (int)$id,
                    $data['nome'],
                    $data['cpf'],
                    $data['telefone'],
                    $data['endereco']
                );
                break;

            case "DELETE":
                if (!$id)
                    throw new APIException("ID não informado!", 400);

                $this->socioService->deleteSocio((int)$id);
                $response = ["mensagem" => "Sócio excluído com sucesso"];
                break;

            default:
                throw new APIException("Method not allowed!", 405);
        }
        Response:: send($response);
    }
}

?>