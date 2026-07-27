<?php

namespace App\Enums;

enum PalavraChaveGherkin: string
{
    case Dado = 'dado';
    case Quando = 'quando';
    case Entao = 'entao';
    case E = 'e';
    case Mas = 'mas';

    public function label(): string
    {
        return match ($this) {
            self::Dado => 'Dado',
            self::Quando => 'Quando',
            self::Entao => 'Então',
            self::E => 'E',
            self::Mas => 'Mas',
        };
    }

    /**
     * Posição da palavra-chave na progressão Dado -> Quando -> Então.
     * Retorna null para E/Mas, que apenas continuam a fase anterior.
     */
    public function faseOrdinal(): ?int
    {
        return match ($this) {
            self::Dado => 0,
            self::Quando => 1,
            self::Entao => 2,
            self::E, self::Mas => null,
        };
    }
}
