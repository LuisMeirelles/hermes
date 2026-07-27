<?php

namespace App\Services;

use App\Models\Cenario;
use App\Models\Teste;

final class TesteAggregateRecalculator
{
    public function __construct(private readonly TesteAggregateCalculator $calculator) {}

    public function recalculate(Teste $teste): void
    {
        $pairs = $teste->cenarios()->get(['status', 'severidade'])
            ->map(fn (Cenario $cenario): array => [
                'status' => $cenario->status,
                'severidade' => $cenario->severidade,
            ])
            ->all();

        $result = $this->calculator->calculate($pairs);

        $teste->forceFill([
            'status' => $result->status,
            'percent_complete' => $result->percentComplete,
        ])->save();
    }
}
