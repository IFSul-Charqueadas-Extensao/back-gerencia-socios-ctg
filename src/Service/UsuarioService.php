<?php

namespace Service;

use Error\APIException;
use Model\Usuario;
use Repository\RefreshTokenRepository;
use Repository\UsuarioRepository;

class UsuarioService
{
    //papéis aceitos — precisa espelhar o ENUM da coluna usuarios.role
    private const ROLES_VALIDAS = ['admin', 'financeiro', 'socios', 'consulta'];

    private const TAMANHO_MINIMO_SENHA = 8;

    private UsuarioRepository $repository;
    private RefreshTokenRepository $refreshTokens;

    public function __construct()
    {
        $this->repository = new UsuarioRepository();
        $this->refreshTokens = new RefreshTokenRepository();
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(int $id): ?Usuario
    {
        return $this->repository->findById($id);
    }

    public function create(string $nome, string $email, string $senha, string $role, bool $ativo = true): Usuario
    {
        $this->validarRole($role);
        $this->validarEmail($email);
        $this->validarSenha($senha);

        if ($this->repository->findByEmail($email)) {
            throw new APIException("Já existe um usuário com este e-mail!", 409);
        }

        $usuario = new Usuario(
            nome: $nome,
            email: $email,
            senhaHash: password_hash($senha, PASSWORD_BCRYPT),
            role: $role,
            ativo: $ativo
        );

        return $this->repository->create($usuario);
    }

    public function update(int $id, string $nome, string $email, string $role, bool $ativo): void
    {
        $this->validarRole($role);
        $this->validarEmail($email);

        $usuario = $this->buscarOuFalhar($id);

        //o e-mail é único: só pode ser reaproveitado pelo próprio usuário
        $existente = $this->repository->findByEmail($email);

        if ($existente && $existente->getId() !== $id) {
            throw new APIException("Já existe um usuário com este e-mail!", 409);
        }

        //impede que o sistema fique sem nenhum administrador ativo
        $perdeuAcessoAdmin = $usuario->getRole() === 'admin' && ($role !== 'admin' || !$ativo);

        if ($perdeuAcessoAdmin && $this->repository->contarAdminsAtivos() <= 1) {
            throw new APIException(
                "Este é o único administrador ativo; promova outro antes de alterá-lo.",
                409
            );
        }

        $usuario->setNome($nome);
        $usuario->setEmail($email);
        $usuario->setRole($role);
        $usuario->setAtivo($ativo);

        $this->repository->update($usuario);

        //ao desativar a conta, derruba as sessões abertas
        if (!$ativo) {
            $this->refreshTokens->revogarDoUsuario($id);
        }
    }

    public function alterarSenha(int $id, string $novaSenha): void
    {
        $this->validarSenha($novaSenha);
        $this->buscarOuFalhar($id);

        $this->repository->updateSenha($id, password_hash($novaSenha, PASSWORD_BCRYPT));

        //trocar a senha invalida as sessões antigas
        $this->refreshTokens->revogarDoUsuario($id);
    }

    public function delete(int $id): void
    {
        $usuario = $this->buscarOuFalhar($id);

        if ($usuario->getRole() === 'admin' && $this->repository->contarAdminsAtivos() <= 1) {
            throw new APIException(
                "Este é o único administrador ativo e não pode ser excluído.",
                409
            );
        }

        //os refresh tokens saem junto por ON DELETE CASCADE
        $this->repository->delete($id);
    }

    private function buscarOuFalhar(int $id): Usuario
    {
        $usuario = $this->repository->findById($id);

        if (!$usuario) {
            throw new APIException("Usuario not found!", 404);
        }

        return $usuario;
    }

    private function validarRole(string $role): void
    {
        if (!in_array($role, self::ROLES_VALIDAS, true)) {
            $validas = implode(', ', self::ROLES_VALIDAS);

            throw new APIException("Perfil inválido! Use um destes: {$validas}.", 400);
        }
    }

    private function validarEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new APIException("E-mail inválido!", 400);
        }
    }

    private function validarSenha(string $senha): void
    {
        if (strlen($senha) < self::TAMANHO_MINIMO_SENHA) {
            throw new APIException(
                "A senha deve ter ao menos " . self::TAMANHO_MINIMO_SENHA . " caracteres!",
                400
            );
        }
    }
}
