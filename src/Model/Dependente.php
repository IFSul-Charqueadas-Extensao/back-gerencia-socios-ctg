<?php

namespace Model;

use DateTime;
use JsonSerializable;

class Dependente implements JsonSerializable {

    private ?int $id;
    private int $socioTitularId;
    private string $nomeCompleto;
    private string $cpf;
    private string $telefone;
    private ?string $foto;
    private DateTime $dataNascimento;
    private bool $dancarino;

    public function __construct(
        int $socioTitularId,
        string $nomeCompleto,
        string $cpf,
        string $telefone,
        DateTime $dataNascimento,
        bool $dancarino,
        ?string $foto = null,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->socioTitularId = $socioTitularId;
        $this->nomeCompleto = $nomeCompleto;
        $this->cpf = $cpf;
        $this->telefone = $telefone;
        $this->foto = $foto;
        $this->dataNascimento = $dataNascimento;
        $this->dancarino = $dancarino;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
    public function getSocioTitularId(): int { return $this->socioTitularId; }
    public function getNomeCompleto(): string { return $this->nomeCompleto; }
    public function getCpf(): string { return $this->cpf; }
    public function getTelefone(): string { return $this->telefone; }
    public function getFoto(): ?string { return $this->foto; }
    public function getDataNascimento(): DateTime { return $this->dataNascimento; }
    public function isDancarino(): bool { return $this->dancarino; }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'socio_titular_id' => $this->socioTitularId,
            'nome_completo' => $this->nomeCompleto,
            'cpf' => $this->cpf,
            'telefone' => $this->telefone,
            'foto' => $this->foto,
            'data_nascimento' => $this->dataNascimento->format('Y-m-d'),
            'dancarino' => $this->dancarino
        ];
    }
}