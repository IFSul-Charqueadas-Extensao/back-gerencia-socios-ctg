<?php

namespace Util;

use Error\APIException;
use Http\Request;
use Model\Usuario;

//Níveis de permissão do sistema.
//
//Regra geral: todo usuário autenticado PODE LER (GET) qualquer recurso,
//exceto /usuarios. A escrita (POST/PUT/DELETE) depende do papel.
//
//Esta é a fonte da verdade da autorização — o front tem uma cópia da matriz
//em src/utils/permissoes.js, mas apenas para esconder botões. Quem decide
//de fato é este arquivo.
class Permissao
{
    //recursos que cada papel pode ALTERAR (criar, editar, excluir)
    private const ESCRITA = [
        'admin' => [
            'socios',
            'dependentes',
            'categorias',
            'cartao-tradicionalista',
            'mensalidades',
            'pagamentos',
            'usuarios',
        ],
        //secretaria: cuida do cadastro dos sócios
        'socios' => [
            'socios',
            'dependentes',
            'categorias',
            'cartao-tradicionalista',
        ],
        //tesouraria: registra o que foi pago
        'financeiro' => [
            'mensalidades',
            'pagamentos',
        ],
        //somente visualização
        'consulta' => [],
    ];

    //recursos visíveis apenas para o admin, inclusive na leitura
    private const SOMENTE_ADMIN = ['usuarios'];

    //métodos HTTP que apenas leem dados
    private const METODOS_LEITURA = ['GET', 'OPTIONS'];

    //indica se o papel pode alterar o recurso informado
    public static function podeEscrever(string $role, string $recurso): bool
    {
        $recursos = self::ESCRITA[$role] ?? [];

        return in_array($recurso, $recursos, true);
    }

    //indica se o papel pode ao menos visualizar o recurso informado
    public static function podeLer(string $role, string $recurso): bool
    {
        if (in_array($recurso, self::SOMENTE_ADMIN, true)) {
            return $role === 'admin';
        }

        //demais recursos são legíveis por qualquer usuário autenticado
        return true;
    }

    //verifica a permissão do usuário para a requisição e
    //gera APIException 403 quando o papel não autoriza a operação
    public static function exigir(Usuario $usuario, Request $request): void
    {
        $recurso = $request->getResource();
        $role    = $usuario->getRole();

        //a rota raiz (recurso vazio) apenas lista os endpoints
        if ($recurso === '' || $recurso === 'auth') {
            return;
        }

        if (!self::podeLer($role, $recurso)) {
            throw new APIException("Acesso negado para o seu perfil!", 403);
        }

        //leitura liberada; a partir daqui só sobram operações de escrita
        if (in_array($request->getMethod(), self::METODOS_LEITURA, true)) {
            return;
        }

        if (!self::podeEscrever($role, $recurso)) {
            throw new APIException(
                "Seu perfil não permite alterar o recurso '{$recurso}'!",
                403
            );
        }
    }
}
