<?php

namespace Util;

//Utilitários de CPF: normalização e validação dos dígitos verificadores.
class Cpf
{
    //remove tudo que não for dígito (aceita CPF com ou sem máscara)
    public static function normalizar(string $cpf): string
    {
        return preg_replace('/\D/', '', $cpf) ?? '';
    }

    //valida o CPF (com ou sem máscara): 11 dígitos, não repetidos, dígitos verificadores corretos
    public static function validar(string $cpf): bool
    {
        $digitos = self::normalizar($cpf);

        if (strlen($digitos) !== 11 || preg_match('/^(\d)\1{10}$/', $digitos)) {
            return false;
        }

        for ($tamanho = 9; $tamanho <= 10; $tamanho++) {
            $soma = 0;
            for ($i = 0; $i < $tamanho; $i++) {
                $soma += (int) $digitos[$i] * ($tamanho + 1 - $i);
            }
            $verificador = ($soma * 10) % 11 % 10;

            if ($verificador !== (int) $digitos[$tamanho]) {
                return false;
            }
        }

        return true;
    }
}
