<?php
namespace Model;

use DateTime;
use JsonSerializable;
use Util\Endereco;
use Util\StatusSocio;
use Util\CategoriaSocio;

class Socio implements JsonSerializable {

    private ?int $id;
    private string $nome;
    private string $cpf;
    private string $email;
    private string $telefone;
    private string $foto;
    private Endereco $endereco;
    private DateTime $dataNascimento;
    private DateTime $dataEntrada;
    private StatusSocio $status;

    private CategoriaSocio $categoria;
    private bool $dancarino;
    private bool $pagaInstrutor;

    /** @var Dependente[] */
    private array $dependentes = [];

    private ?CartaoTrad $cartaoTrad = null;

    public function __construct(
        string $nome,
        string $cpf,
        string $telefone,
        string $email,
        string $foto,
        Endereco $endereco,
        DateTime $dataNascimento,
        DateTime $dataEntrada,
        StatusSocio $status,
        CategoriaSocio $categoria,
        bool $dancarino,
        bool $pagaInstrutor,
        ?int $id = null
    ){
        $this->id = $id;
        $this->nome = $nome;
        $this->cpf = $cpf;
        $this->telefone = $telefone;
        $this->email = $email;
        $this->foto = $foto;
        $this->endereco = $endereco;
        $this->dataNascimento = $dataNascimento;
        $this->dataEntrada = $dataEntrada;
        $this->status = $status;
        $this->categoria = $categoria;
        $this->dancarino = $dancarino;
        $this->pagaInstrutor = $pagaInstrutor;
    }


    // GETTERS

    public function getId(): ?int {
        return $this->id;
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

    public function getEmail(): string {
        return $this->email;
    }

    public function getFoto(): string {
        return $this->foto;
    }

    public function getEndereco(): Endereco {
        return $this->endereco;
    }

    public function getDataNascimento(): DateTime {
        return $this->dataNascimento;
    }

    public function getDataEntrada(): DateTime {
        return $this->dataEntrada;
    }

    public function getStatus(): StatusSocio {
        return $this->status;
    }

    public function getCategoria(): CategoriaSocio {
        return $this->categoria;
    }

    public function isDancarino(): bool {
        return $this->dancarino;
    }

    public function isPagaInstrutor(): bool {
        return $this->pagaInstrutor;
    }

    public function getDependentes(): array {
        return $this->dependentes;
    }

    public function getCartaoTrad(): ?CartaoTrad {
        return $this->cartaoTrad;
    }


    // SETTERS

    public function setNome(string $nome): void {
        $this->nome = $nome;
    }

    public function setCpf(string $cpf): void {
        $this->cpf = $cpf;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setTelefone(string $telefone): void {
        $this->telefone = $telefone;
    }

    public function setEndereco(Endereco $endereco): void {
        $this->endereco = $endereco;
    }

    public function setCategoria(CategoriaSocio $categoria): void {
        $this->categoria = $categoria;
    }

    public function setDancarino(bool $dancarino): void {
        $this->dancarino = $dancarino;
    }

    public function setPagaInstrutor(bool $pagaInstrutor): void {
        $this->pagaInstrutor = $pagaInstrutor;
    }

    public function setCartaoTrad(?CartaoTrad $cartaoTrad): void {
        $this->cartaoTrad = $cartaoTrad;
    }

    public function addDependente(Dependente $dependente): void {
        $this->dependentes[] = $dependente;
    }


    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cpf' => $this->cpf,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'foto' => $this->foto,
            'endereco' => $this->endereco, // já serializa automático
            'data_nascimento' => $this->dataNascimento->format('Y-m-d'),
            'data_entrada' => $this->dataEntrada->format('Y-m-d'),
            'status' => $this->status->value,
            'categoria' => $this->categoria->value,
            'dancarino' => $this->dancarino,
            'paga_instrutor' => $this->pagaInstrutor
        ];
    }
}