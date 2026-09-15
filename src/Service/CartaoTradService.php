<?php
namespace Service;

use Error\APIException;
use Model\CartaoTrad;
use Model\Socio;
use Repository\CartaoTradRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use DateTime;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Picqer\Barcode\BarcodeGeneratorPNG;

class CartaoTradService
{
    private CartaoTradRepository $cartaoRepository;
    private SocioService $socioService;

    public function __construct()
    {
        $this->cartaoRepository = new CartaoTradRepository();
        $this->socioService = new SocioService();
    }

    public function findAll(): array
    {
        return $this->cartaoRepository->findAll();
    }

    public function findById(int $id): ?CartaoTrad
    {
        return $this->cartaoRepository->findById($id);
    }

    public function create(CartaoTrad $cartao): CartaoTrad
    {
        $socioId = $cartao->getSocioId();
        if ($socioId) {
            $socio = $this->buscarSocioOuFalhar($socioId);
            $this->validarFotoObrigatoria($socio);
        }

        $matricula = $this->gerarMatricula();

        $dataValidade = (clone $cartao->getDataSolicitacao())->modify('+2 years');

        $cartao->setMatricula($matricula);
        $cartao->setDataValidade($dataValidade);

        return $this->cartaoRepository->create($cartao);
    }

    public function update(CartaoTrad $cartao): void
    {
        $this->cartaoRepository->update($cartao);
    }

    public function delete(int $id): void
    {
        $this->cartaoRepository->delete($id);
    }

    private function gerarMatricula(): string
    {
        $parte1 = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $parte2 = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        return "RS-{$parte1}-{$parte2}";
    }

    private function buscarSocioOuFalhar(int $socioId): Socio
    {
        $socio = $this->socioService->findById($socioId);
        if (!$socio) {
            throw new APIException("Sócio vinculado não foi encontrado!", 404);
        }
        return $socio;
    }

    private function validarFotoObrigatoria(Socio $socio): void
    {
        if (empty($socio->getFoto())) {
            throw new APIException(
                "O sócio precisa ter uma foto cadastrada para gerar o Cartão Tradicionalista.",
                422
            );
        }
    }

    public function generatePdf(int $cartaoId): string
    {
        $cartao = $this->cartaoRepository->findById($cartaoId);
        if (!$cartao) {
            throw new APIException("Cartão não encontrado!", 404);
        }

        $socioId = $cartao->getSocioId();
        if (!$socioId) {
            throw new APIException("Este cartão não está vinculado a um sócio.", 400);
        }

        $socio = $this->buscarSocioOuFalhar($socioId);

        $this->validarFotoObrigatoria($socio);

        $nomeCompleto   = $socio->getNome();
        $cpf            = $this->formatarCpf($socio->getCpf());
        $dataNascimento = $socio->getDataNascimento()->format('d/m/Y');
        $dataValidade   = $cartao->getDataValidade()
            ? $cartao->getDataValidade()->format('d/m/Y')
            : 'N/A';
        $categoria      = 'TITULAR';
        $matricula      = $cartao->getMatricula() ?? 'N/A';

        $barcodeGenerator   = new BarcodeGeneratorPNG();
        $codigoBarrasPng    = $barcodeGenerator->getBarcode($matricula, $barcodeGenerator::TYPE_CODE_128, 3, 60);
        $codigoBarrasBase64 = base64_encode($codigoBarrasPng);

        $conteudoQrCode = "MAT:{$matricula}|NOME:{$nomeCompleto}|VALIDADE:{$dataValidade}";
        $qrCode         = new QrCode($conteudoQrCode);
        $writer         = new PngWriter();
        $qrCodeResult   = $writer->write($qrCode);
        $qrCodeBase64   = base64_encode($qrCodeResult->getString());

        $fotoBase64 = $socio->getFoto();

        $brasaoBase64 = $this->getBrasaoBase64();

        $templatePath = __DIR__ . '/../templates/cartao_template.php';

        if (!file_exists($templatePath)) {
            throw new APIException("Template do cartão não encontrado!", 500);
        }

        ob_start();
        include $templatePath;
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        $dompdf->setPaper([0, 0, 300, 198]);
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }

    private function formatarCpf(string $cpf): string
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11) {
            return $cpf;
        }
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }

    private function getBrasaoBase64(): string
    {
        static $cache = null;

        if ($cache === null) {
            $path = __DIR__ . '/../templates/brasao_base64.txt';
            if (!file_exists($path)) {
                throw new APIException("Arquivo do brasão (base64) não encontrado!", 500);
            }
            $cache = trim(file_get_contents($path));
        }

        return $cache;
    }
}
