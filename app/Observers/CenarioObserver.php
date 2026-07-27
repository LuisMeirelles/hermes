<?php

namespace App\Observers;

use App\Models\Cenario;
use App\Services\TesteAggregateRecalculator;

class CenarioObserver
{
    public function __construct(private readonly TesteAggregateRecalculator $recalculator) {}

    public function created(Cenario $cenario): void
    {
        $this->recalculator->recalculate($cenario->teste);
    }

    public function updated(Cenario $cenario): void
    {
        if ($cenario->wasChanged(['status', 'severidade'])) {
            $this->recalculator->recalculate($cenario->teste);
        }
    }

    public function deleted(Cenario $cenario): void
    {
        $this->recalculator->recalculate($cenario->teste);
    }
}
