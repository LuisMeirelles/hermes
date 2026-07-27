<?php

namespace App\Enums;

enum TesteStatus: string
{
    case NaoIniciado = 'nao_iniciado';
    case EmAndamento = 'em_andamento';
    case Passou = 'passou';
    case Falhou = 'falhou';
    case Parcial = 'parcial';

    public function label(): string
    {
        return match ($this) {
            self::NaoIniciado => 'Não Iniciado',
            self::EmAndamento => 'Em Andamento',
            self::Passou => 'Passou',
            self::Falhou => 'Falhou',
            self::Parcial => 'Parcial',
        };
    }
}
