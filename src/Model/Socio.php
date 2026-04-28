<?php
namespace Model;

use DateTime;
use JsonSerializable;
use StatusSocio;

class Socio implements JsonSerializable {

    private ?int $id;
    private string $nome;
    private string $cpf;
    private string $telefone;
    private string $foto;
    private string $identidade;
    private string $endereco;
    private DateTime $dataNascimento;
    private DateTime $dataEntrada;
    private StatusSocio $status;
    private int $categoriaId;
    private bool $dancarino;
    private bool $pagaInstrutor;

    public function __construct(
        string $nome,
        string $cpf,
        string $telefone,
        string $foto,
        string $identidade,
        string $endereco,
        DateTime $dataNascimento,
        DateTime $dataEntrada,
        StatusSocio $status,
        int $categoriaId,
        bool $dancarino,
        bool $pagaInstrutor,
        ?int $id = null
    ){
        $this->id = $id;
        $this -> nome = $nome;
        $this -> cpf = $cpf;
        $this -> telefone = $telefone;
        $this -> foto = $foto;
        $this -> identidade = $identidade;
        $this -> endereco = $endereco;
        $this -> dataNascimento = $dataNascimento;
        $this -> dataEntrada = $dataEntrada;
        $this -> status = $status;
        $this -> categoriaId = $categoriaId; 
        $this -> dancarino = $dancarino; 
        $this -> pagaInstrutor = $pagaInstrutor;
    }

    public function getId(): ?int{
        return $this->id;
    }

    public function getNome(): string{
        return $this->nome;
    }

    public function getCpf(): string{
        return $this->cpf;
    }

    public function getTelefone(): string{
        return $this->telefone;
    }

    public function getFoto(): string{
        return $this->foto;
    }

    public function getIdentidade(): string{
        return $this->identidade;
    }

    public function getEndereco(): string{
        return $this->endereco;
    }

    public function getDataNascimento(): DateTime{
        return $this->dataNascimento;
    }

    public function getDataEntrada(): DateTime{
        return $this->dataEntrada;
    }

    public function getStatus(): StatusSocio{
        return $this->status;
    }

    public function getCategoriaId(): int{
        return $this->categoriaId;
    }

    public function isDancarino(): bool{
        return $this->dancarino;    
    }

    public function isPagaInstrutor(): bool{
        return $this->pagaInstrutor;
    }

    public function setNome(string $nome): void {
        $this->nome = $nome;
    }

    public function setCpf(string $cpf): void {
        $this->cpf = $cpf;
    }

    public function setTelefone(string $telefone): void {
        $this->telefone = $telefone;    
    }

    public function setEndereco(string $endereco): void {
        $this->endereco = $endereco;
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cpf' => $this->cpf,
            'telefone' => $this->telefone,
            'foto' => $this->foto,
            'identidade' => $this->identidade,
            'endereco' => $this->endereco,
            'data_nascimento' => $this->dataNascimento->format('Y-m-d'),
            'data_entrada' => $this->dataEntrada->format('Y-m-d'),
            'status' => $this->status->value,
            'categoria_id' => $this->categoriaId,
            'dancarino' => $this->dancarino,
            'paga_instrutor' => $this->pagaInstrutor
        ];
    }
}

?>