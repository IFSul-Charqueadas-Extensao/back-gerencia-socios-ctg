<?php
namespace Service;

use Error\APIException;
use Model\Socio;
use Repository\SocioRepository;
//use Repository\CategoriaRepository;

class SocioService{
    private SocioRepository $socioRepository;
//    private CategoriaRepository $catRepository;

    public function __construct(){
        $this->socioRepository = new SocioRepository();
//        $this->catRepository = new CategoriaRepository();
    }

    public function getSocios(?string $nome) : array{
        if($nome){
            return $this->socioRepository->findByName($nome);
        }else{
            return $this->socioRepository->findAll();
        }
    }

     public function getSocioById(int $id) : Socio {
        $socio = $this->socioRepository->findById($id);

        if (!$socio){
            throw new APIException("Sócio não encontrado!", 404);
        }
        return $socio;
    }

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
            $nome,
            $cpf,
            $telefone,
            $foto,
            $identidade,
            $endereco,
            new \DateTime($dataNascimento),
            new \DateTime($dataEntrada),
            \StatusSocio::from($status),
            $categoriaId,
            $dancarino,
            $pagaInstrutor
        );

        $this->validateSocio($socio);

        return $this->socioRepository->create($socio);
    }

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

        $this->validateSocio($socio);

        $this->socioRepository->update($socio);

        return $socio;
    }

    public function deleteSocio(int $id): void {
        $socio = $this->getSocioById($id); 
        if ($socio->getStatus()->value === 'ATIVO')
            throw new APIException("Não é possível excluir um sócio ativo!", 409);

        $this->socioRepository->delete($id);
    }

      private function validateSocio(Socio $socio) {
        if (strlen(trim($socio->getNome())) < 3)
            throw new APIException("Nome inválido!", 400);

        if (empty($socio->getCpf()))
            throw new APIException("CPF obrigatório!", 400);

        if (empty($socio->getTelefone()))
            throw new APIException("Telefone obrigatório!", 400);

     //   $categoria = $this->catRepository->findById($socio->getCategoriaId());
       // if (!$categoria)
         //   throw new APIException("Categoria não encontrada!", 404); #VALIDAR CAT, QUANDO COLEGA FINANCEIRO CRIAR

        if ($socio->isPagaInstrutor() && !$socio->isDancarino())
            throw new APIException("Sócios que pagam instrutor devem ser dançarinos!", 400);
    }

}

?>