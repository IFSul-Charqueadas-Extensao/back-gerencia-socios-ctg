<?php

namespace Controller;

use Error\APIException;
use Http\Autenticacao;
use Http\Request;
use Http\Response;
use Service\AuthService;

class AuthController
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    public function processRequest(Request $request): void
    {
        //em /api/auth/login o segundo segmento da URL é a ação, não um id
        $acao = $request->getId();
        $method = $request->getMethod();

        switch ($acao) {

            case "login":
                $this->exigirMetodo($method, "POST");

                $data = $request->getBody();

                $email = $data['email'] ?? null;
                $senha = $data['senha'] ?? null;

                if (!$email || !$senha) {
                    throw new APIException("E-mail e senha são obrigatórios!", 400);
                }

                Response::send($this->service->login($email, $senha));

                break;

            case "refresh":
                $this->exigirMetodo($method, "POST");

                $refreshToken = $request->getBody()['refresh_token'] ?? null;

                if (!$refreshToken) {
                    throw new APIException("Refresh token é obrigatório!", 400);
                }

                Response::send($this->service->refresh($refreshToken));

                break;

            case "logout":
                $this->exigirMetodo($method, "POST");

                $refreshToken = $request->getBody()['refresh_token'] ?? null;

                if (!$refreshToken) {
                    throw new APIException("Refresh token é obrigatório!", 400);
                }

                $this->service->logout($refreshToken);

                Response::send(null, 204);

                break;

            case "me":
                $this->exigirMetodo($method, "GET");

                //já autenticado pela guarda no index.php;
                //aqui apenas devolvemos o usuário da sessão
                Response::send(Autenticacao::autenticar($request));

                break;

            default:
                throw new APIException("Resource not found!", 404);
        }
    }

    private function exigirMetodo(string $recebido, string $esperado): void
    {
        if ($recebido !== $esperado) {
            throw new APIException("Method not allowed!", 405);
        }
    }
}
