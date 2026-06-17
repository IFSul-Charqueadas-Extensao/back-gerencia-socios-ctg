<?php
// --- CONFIGURAÇÃO DE CORS (Deve vir antes de qualquer outra lógica) ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
// Trata a requisição de Preflight (OPTIONS) do navegador
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Retorna o status 200 imediatamente e encerra o script sem processar nada mais
    http_response_code(200);
    exit();
}
// ----------------------------------------------------------------------

// executa as configurações iniciais (autoload, tratamento de erros etc)
require_once 'src/config.php';

use Controller\RelatorioController;
use Controller\MensalidadeController;
use Controller\PagamentoController;
use Controller\CategoriaController;
use Controller\SocioController;
use Controller\DependenteController;
use Controller\CartaoTradController;
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
    case 'categorias':
        // rotas para /categorias
        $categoriaController = new CategoriaController();
        $categoriaController->processRequest($request);
        break;
    case 'socios':
        // rotas para /socios
        $socioController = new SocioController();
        $socioController->processRequest($request);
        break;
    case 'dependentes':
        // rotas para /dependentes
        $dependenteController = new DependenteController();
        $dependenteController->processRequest($request);
        break;
    case 'cartao-tradicionalista':
        // rotas para /cartao-tradicionalista
        $cartaoTradController = new CartaoTradController();
        $cartaoTradController->processRequest($request);
        break;
    case null:
        //para a raiz (rota /)
        $endpoints = [
            "GET /api/relatorios/socios",
            "GET /api/relatorios/financeiro",
            "GET /api/relatorios/inadimplentes",
            "GET /api/relatorios/receita-mensal",
            "GET /api/relatorios/quantidade-status",
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
            "GET /api/dependentes",
            "GET /api/dependentes/:id",
            "POST /api/dependentes",
            "PUT /api/dependentes/:id",
            "DELETE /api/dependentes/:id",
            "GET /api/categorias",
            "GET /api/categorias/:id",
            "POST /api/categorias",
            "PUT /api/categorias/:id",
            "DELETE /api/categorias/:id",
            "GET /api/cartao-tradicionalista",
            "GET /api/cartao-tradicionalista/:id",
            "POST /api/cartao-tradicionalista",
            "PUT /api/cartao-tradicionalista/:id",
            "DELETE /api/cartao-tradicionalista/:id"
        ];
        Response::send(["endpoints" => $endpoints]);
        break;
    default:
        //para todos os demais casos, recurso não encontrado
        throw new APIException("Resource not found!", 404);
}
