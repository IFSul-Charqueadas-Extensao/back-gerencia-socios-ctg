<?php

namespace Service;

use Error\APIException;
use Model\Usuario;
use Repository\RefreshTokenRepository;
use Repository\UsuarioRepository;
use Util\Env;
use Util\Jwt;

//Regras de autenticação: login, renovação e encerramento de sessão.
class AuthService
{
    private UsuarioRepository $usuarios;
    private RefreshTokenRepository $refreshTokens;

    public function __construct()
    {
        $this->usuarios = new UsuarioRepository();
        $this->refreshTokens = new RefreshTokenRepository();
    }

    //valida as credenciais e devolve os tokens junto do usuário
    public function login(string $email, string $senha): array
    {
        $usuario = $this->usuarios->findByEmail($email);

        //Mensagem propositalmente genérica e idêntica nos dois casos
        //(e-mail inexistente e senha errada): informar qual dos dois falhou
        //permitiria descobrir quais e-mails estão cadastrados.
        if (!$usuario || !password_verify($senha, $usuario->getSenhaHash())) {
            throw new APIException("Credenciais inválidas!", 401);
        }

        if (!$usuario->isAtivo()) {
            throw new APIException("Usuário desativado!", 403);
        }

        return [
            'access_token' => $this->gerarAccessToken($usuario),
            'refresh_token' => $this->gerarRefreshToken($usuario),
            'usuario' => $usuario
        ];
    }

    //troca um refresh token válido por um novo access token
    public function refresh(string $refreshToken): array
    {
        $hash = hash('sha256', $refreshToken);

        $registro = $this->refreshTokens->findValido($hash);

        if (!$registro) {
            throw new APIException("Refresh token inválido ou expirado!", 401);
        }

        $usuario = $this->usuarios->findById((int) $registro['usuario_id']);

        if (!$usuario || !$usuario->isAtivo()) {
            throw new APIException("Usuário não encontrado ou desativado!", 401);
        }

        return [
            'access_token' => $this->gerarAccessToken($usuario),
            'usuario' => $usuario
        ];
    }

    //encerra a sessão revogando o refresh token
    public function logout(string $refreshToken): void
    {
        $this->refreshTokens->revogar(hash('sha256', $refreshToken));
    }

    //monta o JWT de acesso com os dados que a API precisa a cada requisição
    private function gerarAccessToken(Usuario $usuario): string
    {
        return Jwt::encode(
            [
                'sub' => $usuario->getId(),
                'email' => $usuario->getEmail(),
                'role' => $usuario->getRole()
            ],
            Env::obrigatorio('JWT_SECRET'),
            Env::getInt('JWT_EXPIRA_MINUTOS', 60)
        );
    }

    //gera um refresh token aleatório e guarda apenas o seu hash
    private function gerarRefreshToken(Usuario $usuario): string
    {
        $token = bin2hex(random_bytes(32));

        $dias = Env::getInt('REFRESH_EXPIRA_DIAS', 7);
        $expiraEm = date('Y-m-d H:i:s', strtotime("+{$dias} days"));

        $this->refreshTokens->create(
            $usuario->getId(),
            hash('sha256', $token),
            $expiraEm
        );

        return $token;
    }
}
