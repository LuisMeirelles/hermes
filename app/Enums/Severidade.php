<?php

namespace App\Enums;

enum Severidade: string
{
    case Bloqueante = 'bloqueante';
    case Critica = 'critica';
    case Maior = 'maior';
    case Menor = 'menor';

    public function label(): string
    {
        return match ($this) {
            self::Bloqueante => 'Bloqueante',
            self::Critica => 'Crítica',
            self::Maior => 'Maior',
            self::Menor => 'Menor',
        };
    }

    public function isBloqueante(): bool
    {
        return in_array($this, [self::Bloqueante, self::Critica], true);
    }
}
