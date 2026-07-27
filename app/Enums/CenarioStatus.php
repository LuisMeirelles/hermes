<?php

namespace App\Enums;

enum CenarioStatus: string
{
    case AFazer = 'a_fazer';
    case EmAndamento = 'em_andamento';
    case Passou = 'passou';
    case Falhou = 'falhou';
    case Bloqueado = 'bloqueado';

    public function label(): string
    {
        return match ($this) {
            self::AFazer => 'A Fazer',
            self::EmAndamento => 'Em Andamento',
            self::Passou => 'Passou',
            self::Falhou => 'Falhou',
            self::Bloqueado => 'Bloqueado',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Passou, self::Falhou, self::Bloqueado], true);
    }

    /**
     * @return array<int, self>
     */
    public function allowedNextStatuses(): array
    {
        return match ($this) {
            self::AFazer => [self::EmAndamento],
            self::EmAndamento => [self::Passou, self::Falhou, self::Bloqueado],
            self::Passou, self::Falhou, self::Bloqueado => [self::EmAndamento],
        };
    }
}
