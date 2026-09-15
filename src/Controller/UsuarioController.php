<?php

namespace Controller;

use Error\APIException;
use Http\Request;
use Http\Response;
use Service\UsuarioService;

//Gestão de usuários — acesso restrito ao perfil admin.
//A restrição é aplicada por Util\Permissao, chamada no index.php.
class UsuarioController
{
    private UsuarioService $service;

    public function __construct()
    {
        $this->service = new UsuarioService();
    }

    public function processRequest(Request $request): void
    {
        $id = $request->getId();
        $method = $request->getMethod();

        switch ($method) {

            case "GET":

                if ($id) {
                    $usuario = $this->service->findById($id);

                    if (!$usuario) {
                        throw new APIException("Usuario not found!", 404);
                    }

                    Response::send($usuario);
                    return;
                }

                Response::send($this->service->findAll());
                break;

            case "POST":

                $data = $request->getBody();

                $created = $this->service->create(
                    nome: $this->exigirCampo($data, 'nome'),
                    email: $this->exigirCampo($data, 'email'),
                    senha: $this->exigirCampo($data, 'senha'),
                    role: $this->exigirCampo($data, 'role'),
                    ativo: $data['ativo'] ?? true
                );

                Response::send($created, 201);

                break;

            case "PUT":

                if (!$id) {
                    throw new APIException("ID is required!", 400);
                }

                $data = $request->getBody();

                //troca de senha isolada: PUT com apenas o campo senha
                if (isset($data['senha']) && !isset($data['nome'])) {
                    $this->service->alterarSenha($id, $data['senha']);

                    Response::send([
                        "message" => "Senha alterada successfully"
                    ]);

                    return;
                }

                $this->service->update(
                    id: $id,
                    nome: $this->exigirCampo($data, 'nome'),
                    email: $this->exigirCampo($data, 'email'),
                    role: $this->exigirCampo($data, 'role'),
                    ativo: $data['ativo'] ?? true
                );

                Response::send([
                    "message" => "Usuario updated successfully"
                ]);

                break;

            case "DELETE":

                if (!$id) {
                    throw new APIException("ID is required!", 400);
                }

                $this->service->delete($id);

                Response::send([
                    "message" => "Usuario deleted successfully"
                ]);

                break;

            default:
                throw new APIException("Method not allowed!", 405);
        }
    }

    //os demais Controllers acessam $data['x'] direto, o que gera aviso do PHP
    //quando o campo falta; aqui a ausência vira um 400 explícito
    private function exigirCampo(array $data, string $campo): string
    {
        if (!isset($data[$campo]) || $data[$campo] === '') {
            throw new APIException("O campo '{$campo}' é obrigatório!", 400);
        }

        return $data[$campo];
    }
}
