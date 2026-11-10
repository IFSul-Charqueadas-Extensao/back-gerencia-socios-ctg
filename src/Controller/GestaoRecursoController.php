<?php

namespace Controller;

use Error\APIException;
use Http\Request;
use Http\Response;
use Model\GestaoRecurso;
use Service\GestaoRecursoService;
use DateTime;

class GestaoRecursoController
{
    private GestaoRecursoService $service;

    private const FORMAS_PAGAMENTO = [
        'Dinheiro',
        'Pix',
        'Cartão'
    ];

    public function __construct()
    {
        $this->service = new GestaoRecursoService();
    }

    public function processRequest(Request $request): void
    {
        $id = $request->getId();
        $method = $request->getMethod();

        switch ($method) {
            case 'GET':

                if ($id !== null) {
                    $recurso = $this->service->findById(
                        $this->validarId($id)
                    );

                    if (!$recurso) {
                        throw new APIException(
                            'Recurso não encontrado!',
                            404
                        );
                    }

                    Response::send($recurso);
                    return;
                }

                Response::send($this->service->findAll());
                break;

            case 'POST':

                $dados = $this->validarDados(
                    $request->getBody()
                );

                $recurso = new GestaoRecurso(
                    descricao: $dados['descricao'],
                    valor: $dados['valor'],
                    formaPagamento: $dados['forma_pagamento'],
                    data: $dados['data'] ?? new DateTime()
                );

                $criado = $this->service->create($recurso);

                Response::send($criado, 201);
                break;

            case 'PUT':

                $idRecurso = $this->validarId($id);
                $existente = $this->service->findById($idRecurso);

                if (!$existente) {
                    throw new APIException(
                        'Recurso não encontrado!',
                        404
                    );
                }

                $dados = $this->validarDados(
                    $request->getBody()
                );

                $recurso = new GestaoRecurso(
                    descricao: $dados['descricao'],
                    valor: $dados['valor'],
                    formaPagamento: $dados['forma_pagamento'],
                    data: $dados['data'] ?? $existente->getData(),
                    id: $idRecurso
                );

                $this->service->update($recurso);

                Response::send([
                    'message' => 'Recurso atualizado com sucesso!'
                ]);
                break;

            case 'DELETE':

                $idRecurso = $this->validarId($id);

                if (!$this->service->findById($idRecurso)) {
                    throw new APIException(
                        'Recurso não encontrado!',
                        404
                    );
                }

                $this->service->delete($idRecurso);

                Response::send([
                    'message' => 'Recurso excluído com sucesso!'
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
        $descricao = trim($dados['descricao'] ?? '');
        $valor = $dados['valor'] ?? null;
        $formaPagamento = $dados['forma_pagamento'] ?? '';

        if ($descricao === '' || mb_strlen($descricao) > 255) {
            throw new APIException(
                'Informe uma descrição com até 255 caracteres!',
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
                'Informe um valor válido para o recurso!',
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

        $retorno = [
            'descricao' => $descricao,
            'valor' => round((float) $valor, 2),
            'forma_pagamento' => $formaPagamento
        ];

        if (isset($dados['data']) && trim((string) $dados['data']) !== '') {
            try {
                $retorno['data'] = new DateTime($dados['data']);
            } catch (\Exception $e) {
                throw new APIException('Informe uma data válida!', 422);
            }
        }

        return $retorno;
    }
}

