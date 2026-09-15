<?php

namespace Http;

use Error\APIException;
use Model\Usuario;
use Repository\UsuarioRepository;
use Util\Env;
use Util\Jwt;

//Guarda de autenticação: identifica quem está fazendo a requisição.
//
//Sobre o cabeçalho usado: o servidor do IFSul protege a aplicação com
//HTTP Basic Auth, que já ocupa o cabeçalho Authorization. Por isso o token
//viaja em X-Auth-Token, e Authorization: Bearer é aceito apenas como
//alternativa (útil no Postman/Insomnia em ambiente local).
class Autenticacao
{
    //rotas liberadas sem access token, no formato 'recurso/acao'.
    //refresh e logout se autenticam pelo próprio refresh token enviado no corpo:
    //exigir access token válido aqui impediria encerrar a sessão depois que ele
    //expirasse, deixando o refresh token ativo até o prazo acabar.
    private const ROTAS_PUBLICAS = [
        'auth/login',
        'auth/refresh',
        'auth/logout',
    ];

    //usuário já identificado nesta requisição.
    //a guarda roda no index.php e o AuthController precisa do mesmo usuário
    //em /auth/me — o cache evita validar o token e consultar o banco duas vezes
    private static ?Usuario $autenticado = null;

    //verifica se a requisição pode seguir sem autenticação
    public static function ehPublica(Request $request): bool
    {
        $recurso = $request->getResource();

        //a rota raiz apenas lista os endpoints disponíveis
        if ($recurso === '') {
            return true;
        }

        $rota = $recurso . '/' . ($request->getId() ?? '');

        return in_array($rota, self::ROTAS_PUBLICAS, true);
    }

    //devolve o usuário autenticado ou gera APIException 401
    public static function autenticar(Request $request): Usuario
    {
        if (self::$autenticado !== null) {
            return self::$autenticado;
        }

        $token = self::extrairToken();

        if ($token === null) {
            throw new APIException("Autenticação necessária!", 401);
        }

        //valida assinatura e prazo; gera 401 se o token não for legítimo
        $payload = Jwt::decode($token, Env::obrigatorio('JWT_SECRET'));

        $id = $payload['sub'] ?? null;

        if (!$id) {
            throw new APIException("Token inválido!", 401);
        }

        //relê o usuário do banco em vez de confiar apenas no conteúdo do token:
        //assim, mudança de perfil ou desativação da conta valem de imediato,
        //sem esperar o token expirar
        $usuario = (new UsuarioRepository())->findById((int) $id);

        if (!$usuario) {
            throw new APIException("Usuário não encontrado!", 401);
        }

        if (!$usuario->isAtivo()) {
            throw new APIException("Usuário desativado!", 403);
        }

        self::$autenticado = $usuario;

        return $usuario;
    }

    //procura o token nos cabeçalhos aceitos
    private static function extrairToken(): ?string
    {
        $cabecalhos = self::cabecalhos();

        //formato preferido nesta API
        if (!empty($cabecalhos['x-auth-token'])) {
            return trim($cabecalhos['x-auth-token']);
        }

        //alternativa: Authorization: Bearer <token>.
        //Basic é ignorado de propósito — em produção ele pertence ao Apache
        $authorization = $cabecalhos['authorization'] ?? '';

        if (stripos($authorization, 'Bearer ') === 0) {
            return trim(substr($authorization, 7));
        }

        return null;
    }

    //lê os cabeçalhos da requisição com as chaves em minúsculas.
    //getallheaders() não existe em todo SAPI, por isso o fallback via $_SERVER
    private static function cabecalhos(): array
    {
        $cabecalhos = [];

        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $nome => $valor) {
                $cabecalhos[strtolower($nome)] = $valor;
            }
        }

        //complementa com o $_SERVER, que também cobre o HTTP_AUTHORIZATION
        //repassado pela regra do .htaccess
        foreach ($_SERVER as $chave => $valor) {
            if (str_starts_with($chave, 'HTTP_')) {
                $nome = strtolower(str_replace('_', '-', substr($chave, 5)));

                $cabecalhos[$nome] ??= $valor;
            }
        }

        if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $cabecalhos['authorization'] ??= $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        return $cabecalhos;
    }
}
