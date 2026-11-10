<?php

namespace Controller;

use Error\APIException;
use Http\Request;
use Http\Response;
use Model\VendaCopa;
use Service\VendaCopaService;
use DateTime;

class VendaCopaController
{
    private VendaCopaService $service;

    private const FORMAS_PAGAMENTO = [
        'Dinheiro',
        'Pix',
        'Cartão'
    ];

    public function __construct()
    {
        $this->service = new VendaCopaService();
    }

    public function processRequest(Request $request): void
    {
        $id = $request->getId();
        $method = $request->getMethod();

        switch ($method) {
            case 'GET':

                if ($id !== null) {
                    $venda = $this->service->findById(
                        $this->validarId($id)
                    );

                    if (!$venda) {
                        throw new APIException(
                            'Venda não encontrada!',
                            404
                        );
                    }

                    Response::send($venda);
                    return;
                }

                Response::send($this->service->findAll());
                break;

            case 'POST':

                $dados = $this->validarDados(
                    $request->getBody()
                );

                $venda = new VendaCopa(
                    produto: $dados['produto'],
                    valor: $dados['valor'],
                    formaPagamento: $dados['forma_pagamento'],
                    dataVenda: new DateTime()
                );

                $criada = $this->service->create($venda);

                Response::send($criada, 201);
                break;

            case 'PUT':

                $idVenda = $this->validarId($id);

                if (!$this->service->findById($idVenda)) {
                    throw new APIException(
                        'Venda não encontrada!',
                        404
                    );
                }

                $dados = $this->validarDados(
                    $request->getBody()
                );

                $venda = new VendaCopa(
                    produto: $dados['produto'],
                    valor: $dados['valor'],
                    formaPagamento: $dados['forma_pagamento'],
                    dataVenda: new DateTime(),
                    id: $idVenda
                );

                $this->service->update($venda);

                Response::send([
                    'message' => 'Venda atualizada com sucesso!'
                ]);
                break;

            case 'DELETE':

                $idVenda = $this->validarId($id);

                if (!$this->service->findById($idVenda)) {
                    throw new APIException(
                        'Venda não encontrada!',
                        404
                    );
                }

                $this->service->delete($idVenda);

                Response::send([
                    'message' => 'Venda excluída com sucesso!'
                ]);
                break;

            default:
                throw new APIException(
                    'Método não permitido!',
                    405
                );
        }
    }

    private function validarId(?string $id): int
    {
        if ($id === null || !ctype_digit($id) || (int) $id < 1) {
            throw new APIException('ID inválido!', 400);
        }

        return (int) $id;
    }

    private function validarDados(array $dados): array
    {
        $produto = trim($dados['produto'] ?? '');
        $valor = $dados['valor'] ?? null;
        $formaPagamento = $dados['forma_pagamento'] ?? '';

        if ($produto === '' || strlen($produto) > 150) {
            throw new APIException(
                'Informe um produto com até 150 caracteres!',
                422
            );
        }

        if (
            !is_numeric($valor) ||
            !is_finite((float) $valor) ||
            (float) $valor <= 0 ||
            (float) $valor > 99999999.99
        ) {
            throw new APIException(
                'Informe um valor válido para a venda!',
                422
            );
        }

        if (!in_array(
            $formaPagamento,
            self::FORMAS_PAGAMENTO,
            true
        )) {
            throw new APIException(
                'Forma de pagamento inválida!',
                422
            );
        }

        return [
            'produto' => $produto,
            'valor' => round((float) $valor, 2),
            'forma_pagamento' => $formaPagamento
        ];
    }
}
