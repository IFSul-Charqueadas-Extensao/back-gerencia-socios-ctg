<?php
namespace Service;

use Error\APIException;
use Model\Socio;
use Repository\SocioRepository;
use Util\StatusSocio;
use Util\Endereco;
use Util\CategoriaSocio;
use DateTime;

class SocioService
{
    private SocioRepository $socioRepository;

    public function __construct(){
        $this->socioRepository = new SocioRepository();
    }

    public function findAll(): array {
        return $this->socioRepository->findAll();
    }

    public function findById(int $id): ?Socio {
        return $this->socioRepository->findById($id);
    }

    public function findByName(?string $nome): array {
        if ($nome) {
            return $this->socioRepository->findByName($nome);
        }
        return $this->socioRepository->findAll();
    }

    public function create(Socio $socio): Socio {
        return $this->socioRepository->create($socio);
    }

    public function update(Socio $socio): void {
        $this->socioRepository->update($socio);
    }

    public function delete(int $id): void {
        $this->socioRepository->delete($id);
    }

    public function getSocios(?string $nome): array {
        return $this->findByName($nome);
    }

    public function getSocioById(int $id): Socio {
        $socio = $this->findById($id);
        if (!$socio) {
            throw new APIException("Sócio não encontrado!", 404);
        }
        return $socio;
    }

    public function createSocio(
        string $nome,
        string $cpf,
        string $telefone,
        string $email,
        string $foto,
        array $enderecoData,
        string $dataNascimento,
        string $dataEntrada,
        string $status,
        string $categoria,
        bool $dancarino,
        bool $pagaInstrutor,
    ): Socio {

        $endereco = new Endereco(
            $enderecoData['logradouro'],
            $enderecoData['numero'],
            $enderecoData['bairro'],
            $enderecoData['cidade'],
            $enderecoData['estado'],
            $enderecoData['cep'],
            $enderecoData['complemento'] ?? null
        );

        $socio = new Socio(
            nome: $nome,
            cpf: $cpf,
            telefone: $telefone,
            email: $email,
            foto: $foto,
            endereco: $endereco,
            dataNascimento: new DateTime($dataNascimento),
            dataEntrada: new DateTime($dataEntrada),
            status: StatusSocio::from($status),
            categoria: CategoriaSocio::from($categoria),
            dancarino: $dancarino,
            pagaInstrutor: $pagaInstrutor
        );

        return $this->create($socio);
    }

    public function updateSocio(
        int $id,
        string $nome,
        string $cpf,
        string $telefone,
        array $enderecoData
    ): Socio {
        $socio = $this->getSocioById($id);

        $endereco = new Endereco(
            $enderecoData['logradouro'],
            $enderecoData['numero'],
            $enderecoData['bairro'],
            $enderecoData['cidade'],
            $enderecoData['estado'],
            $enderecoData['cep'],
            $enderecoData['complemento'] ?? null
        );

        $socio->setNome($nome);
        $socio->setCpf($cpf);
        $socio->setTelefone($telefone);
        $socio->setEndereco($endereco);

        $this->update($socio);
        return $socio;
    }

    public function deleteSocio(int $id): void {
        $this->delete($id);
    }
}