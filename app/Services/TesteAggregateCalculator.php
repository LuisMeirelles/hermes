<?php

namespace App\Services;

use App\Enums\CenarioStatus;
use App\Enums\Severidade;
use App\Enums\TesteStatus;
use Illuminate\Support\Collection;

final class TesteAggregateCalculator
{
    /**
     * @param  iterable<array{status: CenarioStatus, severidade: Severidade}>  $cenarios
     */
    public function calculate(iterable $cenarios, TesteStatus $statusAnterior = TesteStatus::NaoIniciado): TesteAggregateResult
    {
        $efetivos = Collection::make($cenarios)->reject(
            fn (array $cenario): bool => $cenario['status'] === CenarioStatus::Bloqueado
                && ! $cenario['severidade']->isBloqueante()
        );

        $total = $efetivos->count();

        if ($total === 0) {
            return new TesteAggregateResult($statusAnterior, 100.0);
        }

        $terminal = $efetivos->filter(
            fn (array $cenario): bool => $cenario['status'] === CenarioStatus::Passou || $this->falhouEfetivamente($cenario)
        );

        $percentComplete = round($terminal->count() / $total * 100, 2);

        $falhas = $efetivos->filter(fn (array $cenario): bool => $this->falhouEfetivamente($cenario));

        if ($falhas->isNotEmpty()) {
            $temFalhaBloqueante = $falhas->contains(fn (array $cenario): bool => $cenario['severidade']->isBloqueante());

            return new TesteAggregateResult(
                $temFalhaBloqueante ? TesteStatus::Falhou : TesteStatus::Parcial,
                $percentComplete,
            );
        }

        if ($terminal->count() === $total) {
            return new TesteAggregateResult(TesteStatus::Passou, $percentComplete);
        }

        $status = $efetivos->every(fn (array $cenario): bool => $cenario['status'] === CenarioStatus::AFazer)
            ? TesteStatus::NaoIniciado
            : TesteStatus::EmAndamento;

        return new TesteAggregateResult($status, $percentComplete);
    }

    /**
     * @param  array{status: CenarioStatus, severidade: Severidade}  $cenario
     */
    private function falhouEfetivamente(array $cenario): bool
    {
        return $cenario['status'] === CenarioStatus::Falhou
            || ($cenario['status'] === CenarioStatus::Bloqueado && $cenario['severidade']->isBloqueante());
    }
}
