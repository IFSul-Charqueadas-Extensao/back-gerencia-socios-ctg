<?php
/** @var string $brasaoBase64    data URI JPEG */
/** @var string $gradienteBase64 data URI JPEG */
/** @var string $fotoBase64      data URI JPEG (ou base64 puro) */
/** @var string $nomeCompleto */
/** @var string $dataNascimento */
/** @var string $dataValidade */
/** @var string $categoria */
/** @var string $cpf */
/** @var string $matricula */
/** @var string $codigoBarrasBase64 data URI SVG */
/** @var string $qrCodeBase64       data URI SVG */

if (!str_starts_with($fotoBase64, 'data:')) {
    $fotoBase64 = 'data:image/jpeg;base64,' . $fotoBase64;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
    @page {
        size: 300pt 198pt;
        margin: 0;
    }

    * {
        box-sizing: border-box;
        font-family: DejaVu Sans, sans-serif;
        line-height: 1.1;
    }

    html, body {
        margin: 0;
        padding: 0;
        width: 300pt;
        height: 198pt;
    }

    .cartao {
        position: absolute;
        top: 1pt;
        left: 1pt;
        width: 298pt;
        height: 196pt;
        border-radius: 10pt;
        background-color: #0a6fd6;
    }

    /* Gradiente como <img> e não background-image: sem GD o Dompdf
       só desenha fundos via GD, mas desenha <img> JPEG direto. */
    .fundo {
        position: absolute;
        top: 0;
        left: 0;
        width: 298pt;
        height: 196pt;
        border-radius: 10pt;
    }

    .faixa-branca {
        position: absolute;
        top: 8pt;
        width: 298pt;
        height: 40pt;
        background: #ffffff;
    }

    .brasao-img {
        position: fixed;
        top: 9pt;
        left: 17pt;
        width: 35pt;
        height: 40pt;
    }

    .titulo-linha1 {
        position: fixed;
        top: 12pt;
        right: 11pt;
        font-size: 12.5pt;
        font-weight: bold;
        color: #1a1a1a;
        letter-spacing: 0.4pt;
        white-space: nowrap;
    }

    .titulo-linha2 {
        position: fixed;
        top: 26pt;
        right: 11pt;
        font-size: 8pt;
        font-weight: bold;
        color: #1a1a1a;
        letter-spacing: 0.2pt;
        white-space: nowrap;
    }

    .foto {
        position: fixed;
        top: 53pt;
        right: 13pt;
        width: 60pt;
        height: 80pt;
        background: #ddd;
    }

    .foto img {
        width: 60pt;
        height: 80pt;
    }

    .campo-nome {
        position: fixed;
        top: 55pt;
        left: 13pt;
        width: 178pt;
        font-size: 7pt;
        font-weight: bold;
        color: #06264a;
    }

    .grupo-coluna-esquerda {
        position: fixed;
        top: 70pt;
        left: 13pt;
        width: 118pt;
    }

    .grupo-coluna-esquerda .label {
        font-size: 6.5pt;
        font-weight: bold;
        color: #06264a;
        margin: 0;
    }

    .grupo-coluna-esquerda .valor {
        font-size: 6.5pt;
        color: #06264a;
        margin: 0 0 5pt 0;
    }

    .grupo-coluna-esquerda .categoria-titulo {
        font-size: 6.5pt;
        font-weight: bold;
        color: #06264a;
        margin: 0;
    }

    .grupo-coluna-esquerda .categoria-valor {
        font-size: 9pt;
        font-weight: bold;
        color: #06264a;
        margin: 0;
    }

    .grupo-coluna-direita {
        position: fixed;
        top: 70pt;
        left: 135pt;
        width: 150pt;
    }

    .grupo-coluna-direita .label {
        font-size: 6.5pt;
        font-weight: bold;
        color: #06264a;
        margin: 0;
    }

    .grupo-coluna-direita .valor {
        font-size: 6.5pt;
        color: #06264a;
        margin: 0 0 5pt 0;
    }

    .grupo-rodape {
        position: fixed;
        top: 147pt;
        left: 13pt;
        width: 274pt;
        overflow: visible;
    }

    .grupo-rodape::after {
        content: "";
        display: block;
        clear: both;
    }

    .cod-barras-extenso {
        border-radius: 2pt;
        font-size: 5.5pt;
        font-weight: bold;
        color: #000000;
        padding: 1.5pt 4pt;
        margin: 0 0 3pt 0;
    }

    .caixa-codigo-barras {
        float: left;
        width: 205pt;
        height: 32pt;
        background: #ffffff;
        border-radius: 2pt;
        text-align: center;
        font-size: 6.5pt;
        color: #888;
        line-height: 32pt;
        margin: 0;
        box-sizing: border-box;
        overflow: hidden;
    }

    .caixa-codigo-barras img {
        height: 100%;
        width: auto;
        max-width: 100%;
    }

    .caixa-qr {
        position: fixed;
        top: 139pt;
        left: 238pt;
        width: 50pt;
        height: 50pt;
        background: #ffffff;
        border-radius: 2pt;
        text-align: center;
        font-size: 6.5pt;
        color: #888;
        line-height: 50pt;
        box-sizing: border-box;
        overflow: hidden;
    }

    .caixa-qr img {
        width: 100%;
        height: 100%;
    }
    
</style>
</head>
<body>

<div class="cartao">

    <img class="fundo" src="<?php echo $gradienteBase64; ?>" alt="">

    <div class="faixa-branca"></div>
    <img class="brasao-img" src="<?php echo $brasaoBase64; ?>" alt="Brasão">
    <div class="titulo-linha1">MTG RS&nbsp;&nbsp;&nbsp;2ª RT</div>
    <div class="titulo-linha2">CTG RAÍZES DA TRADIÇÃO</div>

    <div class="foto">
        <img src="<?php echo $fotoBase64; ?>" alt="Foto">
    </div>

    <div class="campo-nome"><?php echo htmlspecialchars($nomeCompleto); ?></div>

    <div class="grupo-coluna-esquerda">
        <div class="label">Nascimento</div>
        <div class="valor"><?php echo htmlspecialchars($dataNascimento); ?></div>

        <div class="label">Validade</div>
        <div class="valor"><?php echo htmlspecialchars($dataValidade); ?></div>

        <div class="categoria-titulo">Categoria</div>
        <div class="categoria-valor"><?php echo htmlspecialchars($categoria); ?></div>
    </div>

    <div class="grupo-coluna-direita">
        <div class="label">CPF</div>
        <div class="valor"><?php echo htmlspecialchars($cpf); ?></div>

        <div class="label">Matrícula Unificada</div>
        <div class="valor"><?php echo htmlspecialchars($matricula); ?></div>
    </div>

    <div class="grupo-rodape">
        <div class="cod-barras-extenso"><?php echo htmlspecialchars($matricula); ?></div>
        <div class="caixa-codigo-barras"><img src="<?php echo $codigoBarrasBase64; ?>" alt="Código de Barras"></div>
    </div>

    <div class="caixa-qr"><img src="<?php echo $qrCodeBase64; ?>" alt="QR Code"></div>

</div>

</body>
</html>