<?php

namespace Util;

use Error\APIException;

//Geração e validação de JSON Web Tokens (JWT) com assinatura HS256.
//
//Escrito à mão porque o projeto não usa Composer (ver CLAUDE.md).
//Usa apenas hash_hmac e hash_equals, ambos nativos do PHP.
//
//Formato do token: <header>.<payload>.<assinatura>, cada parte em base64url.
class Jwt
{
    private const ALGORITMO = 'HS256';

    //base64 na variante "url safe": sem +, / e sem o = de preenchimento
    private static function base64UrlEncode(string $dados): string
    {
        return rtrim(strtr(base64_encode($dados), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $dados): string
    {
        //restaura o preenchimento retirado na codificação
        $resto = strlen($dados) % 4;

        if ($resto !== 0) {
            $dados .= str_repeat('=', 4 - $resto);
        }

        $decodificado = base64_decode(strtr($dados, '-_', '+/'), true);

        if ($decodificado === false) {
            throw new APIException("Token inválido!", 401);
        }

        return $decodificado;
    }

    //calcula a assinatura das duas primeiras partes do token
    private static function assinar(string $conteudo, string $segredo): string
    {
        //true no último parâmetro devolve os bytes crus (não o hexadecimal)
        return self::base64UrlEncode(
            hash_hmac('sha256', $conteudo, $segredo, true)
        );
    }

    //gera um token assinado a partir de um array de dados (claims).
    //$minutos define em quantos minutos o token expira
    public static function encode(array $payload, string $segredo, int $minutos): string
    {
        $agora = time();

        //claims padrão do JWT: emitido em (iat) e expira em (exp)
        $payload['iat'] = $agora;
        $payload['exp'] = $agora + ($minutos * 60);

        $cabecalho = self::base64UrlEncode(
            json_encode(['alg' => self::ALGORITMO, 'typ' => 'JWT'])
        );

        $corpo = self::base64UrlEncode(json_encode($payload));

        $assinatura = self::assinar("{$cabecalho}.{$corpo}", $segredo);

        return "{$cabecalho}.{$corpo}.{$assinatura}";
    }

    //valida a assinatura e o prazo do token e devolve os dados nele contidos.
    //gera APIException 401 para qualquer token que não seja legítimo e válido
    public static function decode(string $token, string $segredo): array
    {
        $partes = explode('.', $token);

        if (count($partes) !== 3) {
            throw new APIException("Token inválido!", 401);
        }

        [$cabecalho, $corpo, $assinaturaRecebida] = $partes;

        //confere se o algoritmo declarado é o que esperamos.
        //impede o ataque de trocar o alg para "none" e burlar a assinatura
        $cabecalhoDecodificado = json_decode(self::base64UrlDecode($cabecalho), true);

        if (($cabecalhoDecodificado['alg'] ?? null) !== self::ALGORITMO) {
            throw new APIException("Token inválido!", 401);
        }

        //recalcula a assinatura e compara com a recebida.
        //hash_equals compara em tempo constante, evitando timing attack
        $assinaturaEsperada = self::assinar("{$cabecalho}.{$corpo}", $segredo);

        if (!hash_equals($assinaturaEsperada, $assinaturaRecebida)) {
            throw new APIException("Token inválido!", 401);
        }

        $payload = json_decode(self::base64UrlDecode($corpo), true);

        if (!is_array($payload)) {
            throw new APIException("Token inválido!", 401);
        }

        //confere o prazo de validade
        if (!isset($payload['exp']) || time() >= $payload['exp']) {
            throw new APIException("Token expirado!", 401);
        }

        return $payload;
    }
}
