<?php

namespace Model;

use JsonSerializable;

class Usuario implements JsonSerializable
{
    private ?int $id;
    private string $nome;
    private string $email;
    //hash bcrypt da senha — nunca a senha em texto puro
    private string $senhaHash;
    private string $role;
    private bool $ativo;

    public function __construct(
        string $nome,
        string $email,
        string $senhaHash,
        string $role = 'consulta',
        bool $ativo = true,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->senhaHash = $senhaHash;
        $this->role = $role;
        $this->ativo = $ativo;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSenhaHash(): string
    {
        return $this->senhaHash;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function isAtivo(): bool
    {
        return $this->ativo;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setSenhaHash(string $senhaHash): void
    {
        $this->senhaHash = $senhaHash;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function setAtivo(bool $ativo): void
    {
        $this->ativo = $ativo;
    }

    //IMPORTANTE: senha_hash fica de fora de propósito.
    //É este método que a API usa para serializar o usuário na resposta,
    //então o hash nunca pode aparecer aqui.
    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'role' => $this->role,
            'ativo' => $this->ativo
        ];
    }
}
