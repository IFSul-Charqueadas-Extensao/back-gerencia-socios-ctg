<?php

namespace Model;

use DateTime;
use JsonSerializable;

class GestaoRecurso implements JsonSerializable
{
    private ?int $id;
    private string $descricao;
    private float $valor;
    private string $formaPagamento;
    private DateTime $data;

    public function __construct(
        string $descricao,
        float $valor,
        string $formaPagamento,
        DateTime $data,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->descricao = $descricao;
        $this->valor = $valor;
        $this->formaPagamento = $formaPagamento;
        $this->data = $data;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function getValor(): float
    {
        return $this->valor;
    }

    public function getFormaPagamento(): string
    {
        return $this->formaPagamento;
    }

    public function getData(): DateTime
    {
        return $this->data;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'valor' => $this->valor,
            'forma_pagamento' => $this->formaPagamento,
            'data' => $this->data->format('Y-m-d H:i:s')
        ];
    }
}

