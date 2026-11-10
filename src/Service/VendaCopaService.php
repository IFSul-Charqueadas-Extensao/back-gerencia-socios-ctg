<?php

namespace Service;

use Model\VendaCopa;
use Repository\VendaCopaRepository;

class VendaCopaService
{
    private VendaCopaRepository $repository;

    public function __construct()
    {
        $this->repository = new VendaCopaRepository();
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(int $id): ?VendaCopa
    {
        return $this->repository->findById($id);
    }

    public function create(VendaCopa $venda): VendaCopa
    {
        return $this->repository->create($venda);
    }

    public function update(VendaCopa $venda): void
    {
        $this->repository->update($venda);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}
