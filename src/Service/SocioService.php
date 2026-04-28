<?php
namespace Service;

use Error\APIException;
use Model\Socio;
use Repository\SocioRepository;
use DateTime;
use StatusSocio;

class SocioService{
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

    // Legacy method for backward compatibility
    public function getSocios(?string $nome): array {
        return $this->findByName($nome);
    }

    // Legacy method for backward compatibility
    public function getSocioById(int $id): Socio {
        $socio = $this->findById($id);
        if (!$socio) {
            throw new APIException("Sócio não encontrado!", 404);
        }
        return $socio;
    }

    // Legacy method for backward compatibility
    public function createSocio( 
        string $nome,
        string $cpf,
        string $telefone,
        string $foto,
        string $identidade,
        string $endereco,
        string $dataNascimento,
        string $dataEntrada,
        string $status,
        int $categoriaId,
        bool $dancarino,
        bool $pagaInstrutor
    ): Socio {
        $socio = new Socio(
            nome: $nome,
            cpf: $cpf,
            telefone: $telefone,
            foto: $foto,
            identidade: $identidade,
            endereco: $endereco,
            dataNascimento: new DateTime($dataNascimento),
            dataEntrada: new DateTime($dataEntrada),
            status: StatusSocio::from($status),
            categoriaId: $categoriaId,
            dancarino: $dancarino,
            pagaInstrutor: $pagaInstrutor
        );

        return $this->create($socio);
    }

    // Legacy method for backward compatibility
    public function updateSocio(
        int $id,
        string $nome,
        string $cpf,
        string $telefone,
        string $endereco
    ): Socio {
        $socio = $this->getSocioById($id);
        $socio->setNome($nome);
        $socio->setCpf($cpf);
        $socio->setTelefone($telefone);
        $socio->setEndereco($endereco);

        $this->update($socio);
        return $socio;
    }

    // Legacy method for backward compatibility
    public function deleteSocio(int $id): void {
        $this->delete($id);
    }
}

?>