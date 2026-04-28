<?php

// executa as configurações iniciais (autoload, tratamento de erros etc)
require_once 'src/config.php';

use Controller\RelatorioController;
use Controller\MensalidadeController;
use Controller\PagamentoController;
use Controller\SocioController;
use Http\Request;
use Http\Response;
use Error\APIException;

//cria um objeto para armazenar os principais dados da requisição
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER["REQUEST_METHOD"];
$body = file_get_contents("php://input");
$request = new Request($uri, $method, $body);

switch ($request->getResource()) { //conforme o recurso solicitado
    case 'relatorios':
        //para todas as rotas iniciadas por /relatorios
        $relatoriosController = new RelatorioController();
        $relatoriosController->processRequest($request);
        break;
    case 'mensalidades':
        // rotas para /mensalidades
        $mensalidadeController = new MensalidadeController();
        $mensalidadeController->processRequest($request);
        break;
    case 'pagamentos':
        // rotas para /pagamentos
        $pagamentoController = new PagamentoController();
        $pagamentoController->processRequest($request);
        break;
    case 'socios':
        // rotas para /socios
        $socioController = new SocioController();
        $socioController->processRequest($request);
        break;
    case null:
        //para a raiz (rota /)
        $endpoints = [
            "GET /api/relatorios/socios",
            "GET /api/relatorios/financeiro",
            "GET /api/mensalidades",
            "GET /api/mensalidades/:id",
            "POST /api/mensalidades",
            "PUT /api/mensalidades/:id",
            "DELETE /api/mensalidades/:id",
            "GET /api/pagamentos",
            "GET /api/pagamentos/:id",
            "POST /api/pagamentos",
            "PUT /api/pagamentos/:id",
            "DELETE /api/pagamentos/:id",
            "GET /api/socios",
            "GET /api/socios?nome=nome",
            "GET /api/socios/:id",
            "POST /api/socios",
            "PUT /api/socios/:id",
            "DELETE /api/socios/:id",
        ];
        Response::send(["endpoints" => $endpoints]);
        break;
    default:
        //para todos os demais casos, recurso não encontrado
        throw new APIException("Resource not found!", 404);
}