<?php

namespace Util;

use Error\APIException;

//Leitura das variáveis de ambiente do arquivo .env da raiz do projeto.
//Antes essa leitura existia apenas dentro de Database::config(); foi extraída
//para cá porque a autenticação também precisa do .env (segredo do JWT).
class Env
{
    //cache da leitura: o .env é lido uma única vez por requisição
    private static ?array $valores = null;

    //carrega o arquivo .env (uma única vez)
    private static function carregar(): array
    {
        if (self::$valores === null) {
            $caminho = __DIR__ . '/../../.env';

            if (!file_exists($caminho)) {
                throw new APIException("Arquivo .env não encontrado!", 500);
            }

            $valores = parse_ini_file($caminho);

            if ($valores === false) {
                throw new APIException("Não foi possível ler o arquivo .env!", 500);
            }

            self::$valores = $valores;
        }

        return self::$valores;
    }

    //devolve o valor da variável, ou o padrão informado se ela não existir
    public static function get(string $chave, ?string $padrao = null): ?string
    {
        $valores = self::carregar();

        return $valores[$chave] ?? $padrao;
    }

    //devolve o valor da variável, ou gera exceção se ela não estiver definida.
    //usado para configurações sem padrão seguro, como o segredo do JWT
    public static function obrigatorio(string $chave): string
    {
        $valor = self::get($chave);

        if ($valor === null || $valor === '') {
            throw new APIException("Variável {$chave} não definida no .env!", 500);
        }

        return $valor;
    }

    //devolve o valor convertido para inteiro
    public static function getInt(string $chave, int $padrao): int
    {
        $valor = self::get($chave);

        return $valor === null || $valor === '' ? $padrao : (int) $valor;
    }
}
