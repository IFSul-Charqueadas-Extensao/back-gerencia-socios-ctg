<?php

require_once __DIR__ . '/../vendor/autoload.php';

function autoload(string $className)
{
    //$classname possui tanto o namespace quanto o nome da classe
    //exemplo: Model\Student
    //assim precisa trocar \ por / para formar o caminho ..../Model/Student.php
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);

    //define o caminho para o arquivo
    $file = __DIR__ . '/' . $className . '.php';

    // Verifica se o arquivo existe.
    // IMPORTANTE: se não existir, apenas retornamos (não lançamos exceção!).
    // Isso permite que outros autoloaders registrados (como o do Composer)
    // tenham a chance de tentar carregar a classe. Além disso, bibliotecas
    // como o php-font-lib (usado pelo Dompdf) fazem verificações defensivas
    // com class_exists(), que disparam toda a cadeia de autoloaders — se
    // lançarmos uma exceção aqui, quebramos essa verificação e causamos
    // um erro fatal onde deveria haver apenas um "false" silencioso.
    if (!file_exists($file)) {
        return;
    }

    // Inclui o arquivo
    require_once $file;
}

//registra a função autoload para ser responsável por carregar
//todos os arquivos das classes que forem sendo utilizadas 
spl_autoload_register('autoload');

use Error\APIException;
use Http\Response;

function exceptionHandler(\Throwable $exception)
{
    if ($exception instanceof APIException) {
        Response::send(['message' => $exception->getMessage()], $exception->getCode());
    } else {
        error_log($exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
        Response::send(['message' => 'Unable to process this request!'], 500);
    }
}

set_exception_handler('exceptionHandler');

function handleError($severity, $message, $file, $line)
{
    throw new ErrorException($message, 0, $severity, $file, $line);
}

set_error_handler('handleError');