<?php

namespace App\Http\Controllers;

use App\Enums\CenarioStatus;
use App\Http\Requests\Cenario\BulkStoreCenarioRequest;
use App\Http\Requests\Cenario\UpdateCenarioRequest;
use App\Models\CasoDeTeste;
use App\Models\CasoDeTestePasso;
use App\Models\Cenario;
use App\Models\Teste;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CenarioController extends Controller
{
    /**
     * Bulk-instantiate Cenários from the Caso de Teste library into a Teste.
     */
    public function store(BulkStoreCenarioRequest $request, Teste $teste): RedirectResponse
    {
        DB::transaction(function () use ($request, $teste): void {
            collect($request->validated('casos'))->each(function (array $item) use ($teste): void {
                $caso = CasoDeTeste::query()->with('passos')->findOrFail($item['caso_de_teste_id']);

                $teste->cenarios()->create([
                    'caso_de_teste_id' => $caso->id,
                    'titulo' => $caso->titulo,
                    'passos_snapshot' => $caso->passos->map(fn (CasoDeTestePasso $passo): array => [
                        'ordem' => $passo->ordem,
                        'palavra_chave' => $passo->palavra_chave->value,
                        'texto' => $passo->texto,
                    ])->all(),
                    'severidade' => $item['severidade'],
                    'status' => CenarioStatus::AFazer,
                ]);
            });
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Cenários adicionados.')]);

        return to_route('testes.show', $teste);
    }

    /**
     * Update a Cenário's status and/or severidade.
     */
    public function update(UpdateCenarioRequest $request, Teste $teste, Cenario $cenario): RedirectResponse
    {
        $cenario->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Cenário atualizado.')]);

        return to_route('testes.show', $teste);
    }

    public function destroy(Teste $teste, Cenario $cenario): RedirectResponse
    {
        $cenario->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Cenário removido.')]);

        return to_route('testes.show', $teste);
    }
}
