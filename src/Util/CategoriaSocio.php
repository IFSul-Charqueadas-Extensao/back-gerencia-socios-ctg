<?php
namespace Util;

enum CategoriaSocio: string
{
    case DENTE_DE_LEITE = '1';
    case PRE_MIRIM = '2';
    case MIRIM = '3';
    case JUVENIL = '4';
    case ADULTO = '5';
    case VETERANO = '6';
    case XIRU = '7';
    case CHULA = '8';

    public function label(): string
    {
        return match ($this) {
            self::DENTE_DE_LEITE => 'Dente de Leite',
            self::PRE_MIRIM       => 'Pré-Mirim',
            self::MIRIM            => 'Mirim',
            self::JUVENIL          => 'Juvenil',
            self::ADULTO           => 'Adulto',
            self::VETERANO         => 'Veterano',
            self::XIRU             => 'Xiru',
            self::CHULA            => 'Chula',
        };
    }
}
