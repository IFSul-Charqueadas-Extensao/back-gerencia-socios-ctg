<?php
namespace Service;

use Model\Dependente;
use Repository\DependenteRepository;
use Error\APIException;
use Repository\SocioRepository;
use Util\StatusSocio;

class DependenteService
{
    private DependenteRepository $dependenteRepository;
    private SocioRepository $socio_repository;

    public function __construct()
    {
        $this->dependenteRepository = new DependenteRepository();
        $this->socio_repository = new SocioRepository();
    }

    public function findAll(): array
    {
        $this->dependenteRepository->sincronizarMaioridade();
        return $this->dependenteRepository->findAll();
    }

    public function findById(int $id): ?Dependente
    {

        $this->dependenteRepository->sincronizarMaioridade();

        return $this->dependenteRepository->findById($id);
    }

    public function findBySocioTitular(int $socioTitularId): array
    {
        $this->dependenteRepository->sincronizarMaioridade();

        return $this->dependenteRepository->findBySocioTitular($socioTitularId);
    }

    public function create(Dependente $dependente): Dependente
    {
        $dependente->setStatus($this->statusEfetivo($dependente));

        return $this->dependenteRepository->create($dependente);
    }

    public function update(Dependente $dependente): void
    {
        $dependente->setStatus($this->statusEfetivo($dependente));

        $this->dependenteRepository->update($dependente);
    }

    public function delete(int $id): void
    {
        $this->dependenteRepository->delete($id);
    }

    public function atualizarStatusPorSocio(
        int $socioTitularId,
        StatusSocio $status
    ): void
    {
        $this->dependenteRepository->atualizarStatusPorSocio($socioTitularId, $status);
    }

    private function statusEfetivo(Dependente $dependente): StatusSocio
    {
        if($dependente->IsMaiorDeIdade()){
            return StatusSocio::INATIVO;
        }

        $socio = $this->socio_repository->findById($dependente->getSocioTitularId());

        if(!$socio){
            throw new APIException(
                "Sócio titular não encontrado!",
                404
            );
        }
        return $socio->getStatus();
    }
}
