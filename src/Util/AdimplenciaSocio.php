<?php

namespace Util;

use DateTimeImmutable;

//Regra de adimplência do sócio TITULAR.
//
//É um espelho em PHP de calcularStatusSocio() do front
//(front-gerencia-socios-ctg/src/utils/statusHelper.js), que é a regra que o
//sistema já exibe nas telas de sócios. Qualquer mudança lá deve ser refletida aqui:
//  - sócio Inativo                              -> não adimplente
//  - algum dos 4 meses ANTERIORES ao atual (a partir do mês de entrada)
//    sem mensalidade 'Pago'                     -> não adimplente (front: "Atrasado")
//  - mês atual 'Pago'                           -> adimplente (front: "Em dia")
//  - mês atual ausente/não pago, sem atraso     -> adimplente (front: "Pendente":
//                                                  o mês corrente só atrasa depois de virar)
//  - entrada em mês futuro                      -> adimplente (como o front)
//Só entram mensalidades do titular (dependente_id nulo).
class AdimplenciaSocio
{
    private const MESES_LOOKBACK = 4;

    //$statusSocio: 'Ativo'|'Inativo'; $dataEntrada: 'Y-m-d' ou null;
    //$mesesPagos: lista de ['ano' => int, 'mes' => int] com mensalidade 'Pago'.
    //Devolve ['adimplente' => bool, 'em_dia_ate' => 'AAAA-MM'|null]
    public static function calcular(
        string $statusSocio,
        ?string $dataEntrada,
        array $mesesPagos,
        DateTimeImmutable $hoje
    ): array {
        if ($statusSocio === StatusSocio::INATIVO->value) {
            return ['adimplente' => false, 'em_dia_ate' => null];
        }

        $anoAtual = (int) $hoje->format('Y');
        $mesAtual = (int) $hoje->format('n');

        $pagos = [];
        foreach ($mesesPagos as $p) {
            $pagos[(int) $p['ano'] * 100 + (int) $p['mes']] = true;
        }

        $entradaAno = 0;
        $entradaMes = 0;
        if ($dataEntrada) {
            $partes = explode('-', $dataEntrada);
            $entradaAno = (int) $partes[0];
            $entradaMes = (int) ($partes[1] ?? 0);
        }
        $chaveEntrada = $entradaAno * 100 + $entradaMes;

        //1. atraso nos 4 meses anteriores (meses antes da entrada são ignorados)
        for ($i = 1; $i <= self::MESES_LOOKBACK; $i++) {
            [$ano, $mes] = self::deslocar($anoAtual, $mesAtual, -$i);
            $chave = $ano * 100 + $mes;

            if ($chave < $chaveEntrada) {
                continue;
            }
            if (!isset($pagos[$chave])) {
                return ['adimplente' => false, 'em_dia_ate' => null];
            }
        }

        //2. adimplente. "Em dia até" = último mês pago consecutivo a partir do
        //mês atual (se pago, avança por mensalidades adiantadas); se o mês atual
        //ainda não foi pago, é o mês anterior.
        $chaveAtual = $anoAtual * 100 + $mesAtual;
        $ano = $anoAtual;
        $mes = $mesAtual;

        if (isset($pagos[$chaveAtual])) {
            for ($i = 0; $i < 24; $i++) {
                [$proxAno, $proxMes] = self::deslocar($ano, $mes, 1);
                if (!isset($pagos[$proxAno * 100 + $proxMes])) {
                    break;
                }
                [$ano, $mes] = [$proxAno, $proxMes];
            }
        } else {
            [$ano, $mes] = self::deslocar($anoAtual, $mesAtual, -1);
        }

        return ['adimplente' => true, 'em_dia_ate' => sprintf('%04d-%02d', $ano, $mes)];
    }

    //soma (ou subtrai) meses a um par ano/mês
    private static function deslocar(int $ano, int $mes, int $delta): array
    {
        $indice = $ano * 12 + ($mes - 1) + $delta;

        return [intdiv($indice, 12), $indice % 12 + 1];
    }
}
