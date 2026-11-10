<?php

namespace Service;

use DateTimeImmutable;
use Error\APIException;
use Repository\VerificacaoRepository;
use Util\AdimplenciaSocio;
use Util\Cpf;

//Verificação PÚBLICA de adimplência: o sócio informa o próprio CPF.
//Por ser pública, devolve o mínimo: primeiro nome, situação e mês — nada de
//CPF, valores, parcelas, endereço ou dependentes.
class VerificacaoService
{
    //limite por IP: no máximo 10 consultas a cada 60 segundos
    private const LIMITE_CONSULTAS = 10;
    private const JANELA_SEGUNDOS = 60;

    private VerificacaoRepository $repository;

    public function __construct()
    {
        $this->repository = new VerificacaoRepository();
    }

    public function verificar(string $cpfInformado, string $ip, ?DateTimeImmutable $hoje = null): array
    {
        //o rate limit vem primeiro e conta toda consulta (inclusive CPF inválido)
        $this->aplicarRateLimit($ip);

        if (!Cpf::validar($cpfInformado)) {
            throw new APIException("CPF inválido!", 400);
        }

        $socio = $this->repository->buscarSocioPorCpf(Cpf::normalizar($cpfInformado));

        if ($socio === null) {
            return ['encontrado' => false];
        }

        $hoje ??= new DateTimeImmutable('today');
        $resultado = AdimplenciaSocio::calcular(
            $socio['status'],
            $socio['data_entrada'],
            $this->repository->mesesPagosDoTitular((int) $socio['id']),
            $hoje
        );

        $primeiroNome = explode(' ', trim((string) $socio['nome_completo']))[0];

        $resposta = [
            'encontrado' => true,
            'nome' => $primeiroNome,
            'adimplente' => $resultado['adimplente'],
            'verificado_em' => $hoje->format('Y-m-d'),
        ];

        if ($resultado['adimplente']) {
            $resposta['em_dia_ate'] = $resultado['em_dia_ate']; //AAAA-MM
        }

        return $resposta;
    }

    private function aplicarRateLimit(string $ip): void
    {
        //guarda só o hash do IP
        $ipHash = hash('sha256', $ip);

        //limpeza oportunista: ~1% das requisições apaga o que tem mais de 1 hora
        if (random_int(1, 100) === 1) {
            $this->repository->limparTentativasAntigas(3600);
        }

        if ($this->repository->contarTentativas($ipHash, self::JANELA_SEGUNDOS) >= self::LIMITE_CONSULTAS) {
            header('Retry-After: ' . self::JANELA_SEGUNDOS);
            throw new APIException("Muitas consultas. Aguarde um minuto e tente novamente.", 429);
        }

        $this->repository->registrarTentativa($ipHash);
    }
}
