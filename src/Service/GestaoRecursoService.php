<?php

namespace Service;

use Model\GestaoRecurso;
use Repository\GestaoRecursoRepository;

class GestaoRecursoService
{
    private GestaoRecursoRepository $repository;

    public function __construct()
    {
        $this->repository = new GestaoRecursoRepository();
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(int $id): ?GestaoRecurso
    {
        return $this->repository->findById($id);
    }

    public function create(GestaoRecurso $recurso): GestaoRecurso
    {
        return $this->repository->create($recurso);
    }

    public function update(GestaoRecurso $recurso): void
    {
        $this->repository->update($recurso);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}

