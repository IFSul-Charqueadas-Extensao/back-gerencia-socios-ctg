<?php

namespace Service;

use Repository\RelatorioRepository;

class RelatorioService
{
    private RelatorioRepository $repository;

    function __construct()
    {
        $this->repository = new RelatorioRepository();
    }

    function getSociosReport(): array
    {
        return $this->repository->getTotalSocios();
    }

    function getFinanceiroReport(): array
    {
        return $this->repository->getResumoFinanceiro();
    }
}
