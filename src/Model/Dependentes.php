<?php
namespace Model;

class Dependente {

    private ?int $id;
    private int $socioTitularId;
    private string $nome;
    private string $cpf;
    private string $telefone;
    private string $foto;
    private string $identidade;
    private string $endereco;
    private DateTime $dataNascimento;
    private DateTime $dataEntrada;
    private int $categoriaId;
    private bool $dancarino;
    private bool $pagaInstrutor;

    public function __construct(int $socioTitularId, string $nome, string $cpf, string $telefone, string $foto, string $identidade, string $endereco, DateTime $dataNascimento, DateTime $dataEntrada, int $categoriaId, bool $dancarino, bool $pagaInstrutor, ?int $id = null ){
        $this->id = $id;
        $this->socioTitularId = $socioTitularId;
        $this->nome = $nome;
        $this->cpf = $cpf;
        $this->telefone = $telefone;
        $this->foto = $foto;
        $this->identidade = $identidade;
        $this->endereco = $endereco;
        $this->dataNascimento = $dataNascimento;
        $this->dataEntrada = $dataEntrada;
        $this->categoriaId = $categoriaId;
        $this->dancarino = $dancarino;
        $this->pagaInstrutor = $pagaInstrutor;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getSocioTitularId(): int {
        return $this->socioTitularId;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function getCpf(): string {
        return $this->cpf;
    }

    public function getTelefone(): string {
        return $this->telefone;
    }

    public function getFoto(): string {
        return $this->foto;
    }

    public function getIdentidade(): string {
        return $this->identidade;
    }

    public function getEndereco(): string {
        return $this->endereco;
    }

    public function getDataNascimento(): DateTime {
        return $this->dataNascimento;
    }

    public function getDataEntrada(): DateTime {
        return $this->dataEntrada;
    }

    public function getCategoriaId(): int {
        return $this->categoriaId;
    }

    public function isDancarino(): bool {
        return $this->dancarino;
    }

    public function isPagaInstrutor(): bool {
        return $this->pagaInstrutor;
    }
}