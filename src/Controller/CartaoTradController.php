<?php

namespace Controller;

use Error\APIException;
use Http\Request;
use Http\Response;
use Model\CartaoTrad;
use Service\CartaoTradService;
use DateTime;

class CartaoTradController {

    private CartaoTradService $cartaoService;

    public function __construct() {
        $this->cartaoService = new CartaoTradService();
    }

    public function processRequest(Request $request): void {
        $id = $request->getId();
        $method = $request->getMethod();

        switch ($method) {

            case "GET":
                if ($id && $request->getSubCollection() === 'pdf') {
                    $this->handleGeneratePdf((int)$id);
                    return;
                }

                if ($id) {
                    $cartao = $this->cartaoService->findById((int)$id);

                    if (!$cartao) {
                        throw new APIException("Cartão não encontrado!", 404);
                    }

                    Response::send($cartao);
                    return;
                }

                Response::send($this->cartaoService->findAll());
                break;

            case "POST":
                $data = $request->getBody();

                $cartao = new CartaoTrad(
                    socioId: isset($data['socio_id']) && $data['socio_id'] !== null ? (int)$data['socio_id'] : null,
                    dependenteId: isset($data['dependente_id']) && $data['dependente_id'] !== null ? (int)$data['dependente_id'] : null,
                    dataSolicitacao: new DateTime($data['data_solicitacao']),
                    pago: (bool)$data['pago'],
                    valor: (float)$data['valor']
                );

                $created = $this->cartaoService->create($cartao);

                Response::send($created, 201);
                break;

            case "PUT":
                if (!$id) {
                    throw new APIException("ID é obrigatório!", 400);
                }

                $data = $request->getBody();

                $cartao = new CartaoTrad(
                    socioId: isset($data['socio_id']) && $data['socio_id'] !== null ? (int)$data['socio_id'] : null,
                    dependenteId: isset($data['dependente_id']) && $data['dependente_id'] !== null ? (int)$data['dependente_id'] : null,
                    dataSolicitacao: new DateTime($data['data_solicitacao']),
                    pago: (bool)$data['pago'],
                    valor: (float)$data['valor'],
                    id: (int)$id
                );

                $this->cartaoService->update($cartao);

                Response::send([
                    "message" => "Cartão atualizado com sucesso"
                ]);

                break;

            case "DELETE":
                if (!$id) {
                    throw new APIException("ID é obrigatório!", 400);
                }

                $this->cartaoService->delete($id);

                Response::send([
                    "message" => "Cartão excluído com sucesso"
                ]);

                break;

            default:
                throw new APIException("Método não permitido!", 405);
        }
    }

    private function handleGeneratePdf(int $cartaoId): void {
        $pdfContent = $this->cartaoService->generatePdf($cartaoId);

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="cartao-tradicionalista-' . $cartaoId . '.pdf"');
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdfContent;
    }
}
