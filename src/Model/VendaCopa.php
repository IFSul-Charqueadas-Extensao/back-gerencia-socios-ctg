<?php

namespace Model;

use DateTime;
use JsonSerializable;

class VendaCopa implements JsonSerializable
{
    private ?int $id;
    private string $produto;
    private float $valor;
    private string $formaPagamento;
    private DateTime $dataVenda;

    public function __construct(
        string $produto,
        float $valor,
        string $formaPagamento,
        DateTime $dataVenda,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->produto = $produto;
        $this->valor = $valor;
        $this->formaPagamento = $formaPagamento;
        $this->dataVenda = $dataVenda;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduto(): string
    {
        return $this->produto;
    }

    public function getValor(): float
    {
        return $this->valor;
    }

    public function getFormaPagamento(): string
    {
        return $this->formaPagamento;
    }

    public function getDataVenda(): DateTime
    {
        return $this->dataVenda;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'produto' => $this->produto,
            'valor' => $this->valor,
            'forma_pagamento' => $this->formaPagamento,
            'data_venda' => $this->dataVenda->format('Y-m-d H:i:s')
        ];
    }
}
