<?php
namespace Model;

use DateTime;
use JsonSerializable;

class CartaoTrad implements JsonSerializable {

    private ?int $id;
    private ?int $socioId;
    private ?int $dependenteId;
    private DateTime $dataSolicitacao;
    private bool $pago;
    private float $valor;
    private ?string $matricula;
    private ?DateTime $dataValidade;

    public function __construct(
        ?int $socioId,
        ?int $dependenteId,
        DateTime $dataSolicitacao,
        bool $pago,
        float $valor,
        ?int $id = null,
        ?string $matricula = null,
        ?DateTime $dataValidade = null
    ) {
        $this->id = $id;
        $this->socioId = $socioId;
        $this->dependenteId = $dependenteId;
        $this->dataSolicitacao = $dataSolicitacao;
        $this->pago = $pago;
        $this->valor = $valor;
        $this->matricula = $matricula;
        $this->dataValidade = $dataValidade;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getSocioId(): ?int {
        return $this->socioId;
    }

    public function getDependenteId(): ?int {
        return $this->dependenteId;
    }

    public function getDataSolicitacao(): DateTime {
        return $this->dataSolicitacao;
    }

    public function isPago(): bool {
        return $this->pago;
    }

    public function getValor(): float {
        return $this->valor;
    }

    public function getMatricula(): ?string {
        return $this->matricula;
    }

    public function setMatricula(string $matricula): void {
        $this->matricula = $matricula;
    }

    public function getDataValidade(): ?DateTime {
        return $this->dataValidade;
    }

    public function setDataValidade(DateTime $dataValidade): void {
        $this->dataValidade = $dataValidade;
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'socio_id' => $this->socioId,
            'dependente_id' => $this->dependenteId,
            'data_solicitacao' => $this->dataSolicitacao->format('Y-m-d'),
            'pago' => $this->pago,
            'valor' => $this->valor,
            'matricula' => $this->matricula,
            'data_validade' => $this->dataValidade?->format('Y-m-d')
        ];
    }
}
