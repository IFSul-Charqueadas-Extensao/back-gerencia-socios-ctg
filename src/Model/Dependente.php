<?php

namespace Model;

use DateTime;
use JsonSerializable;
use Util\StatusSocio;

class Dependente implements JsonSerializable {

    private ?int $id;
    private int $socioTitularId;
    private string $nomeCompleto;
    private string $cpf;
    private string $telefone;
    private ?string $foto;
    private DateTime $dataNascimento;
    private bool $dancarino;
    private StatusSocio $status;

    public function __construct(
        int $socioTitularId,
        string $nomeCompleto,
        string $cpf,
        string $telefone,
        DateTime $dataNascimento,
        bool $dancarino,
        ?string $foto = null,
        ?int $id = null,
        StatusSocio $status = StatusSocio::ATIVO
    ) {
        $this->id = $id;
        $this->socioTitularId = $socioTitularId;
        $this->nomeCompleto = $nomeCompleto;
        $this->cpf = $cpf;
        $this->telefone = $telefone;
        $this->foto = $foto;
        $this->dataNascimento = $dataNascimento;
        $this->dancarino = $dancarino;
        $this->status = $status;
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
    public function getStatus(): StatusSocio { return $this->status; }
    public function setStatus(StatusSocio $status): void { $this->status = $status; }
    public function getDataMaiorIdade(): DateTime { $data = clone $this->dataNascimento; $data->modify('+18 years'); return $data; }
    public function IsMaiorDeIdade(?DateTime $referencia = null): bool { $referencia ??= new DateTime(); return $this->getDataMaiorIdade() <= $referencia; }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'socio_titular_id' => $this->socioTitularId,
            'nome_completo' => $this->nomeCompleto,
            'cpf' => $this->cpf,
            'telefone' => $this->telefone,
            'foto' => $this->foto,
            'data_nascimento' => $this->dataNascimento->format('Y-m-d'),
            'data_maioridade' => $this->getDataMaioridade()->format('Y-m-d'),
            'dancarino' => $this->dancarino,
            'status' => $this->status->value
        ];
    }
}