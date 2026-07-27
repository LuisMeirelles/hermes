<?php

namespace App\Http\Controllers;

use App\Http\Requests\CasoDeTeste\StoreCasoDeTesteRequest;
use App\Http\Requests\CasoDeTeste\UpdateCasoDeTesteRequest;
use App\Models\CasoDeTeste;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CasoDeTesteController extends Controller
{
    /**
     * Show the Caso de Teste library list.
     */
    public function index(): Response
    {
        return Inertia::render('casos-de-teste/index', [
            'casosDeTeste' => CasoDeTeste::query()
                ->withCount('passos')
                ->latest('id')
                ->get(),
        ]);
    }

    /**
     * Show the form to author a new Caso de Teste.
     */
    public function create(): Response
    {
        return Inertia::render('casos-de-teste/create');
    }

    /**
     * Store a new Caso de Teste and its passos.
     *
     * Requests that want a JSON response (e.g. the inline creation form inside
     * the "Adicionar Cenários" dialog) get the created resource back directly,
     * instead of the usual Inertia redirect to the edit page.
     */
    public function store(StoreCasoDeTesteRequest $request): RedirectResponse|JsonResponse
    {
        $casoDeTeste = CasoDeTeste::query()->create($request->safe()->only(['titulo', 'descricao']));

        $this->syncPassos($casoDeTeste, $request->validated('passos'));

        if ($request->wantsJson()) {
            return response()->json($casoDeTeste->load('passos'), 201);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Caso de Teste criado.')]);

        return to_route('casos-de-teste.edit', $casoDeTeste);
    }

    /**
     * Show the form to edit an existing Caso de Teste (also serves as its detail view).
     */
    public function edit(CasoDeTeste $casoDeTeste): Response
    {
        return Inertia::render('casos-de-teste/edit', [
            'casoDeTeste' => $casoDeTeste->load('passos'),
        ]);
    }

    /**
     * Update a Caso de Teste, replacing its passos entirely.
     */
    public function update(UpdateCasoDeTesteRequest $request, CasoDeTeste $casoDeTeste): RedirectResponse
    {
        $casoDeTeste->update($request->safe()->only(['titulo', 'descricao']));

        $this->syncPassos($casoDeTeste, $request->validated('passos'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Caso de Teste atualizado.')]);

        return to_route('casos-de-teste.edit', $casoDeTeste);
    }

    public function destroy(CasoDeTeste $casoDeTeste): RedirectResponse
    {
        $casoDeTeste->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Caso de Teste excluído.')]);

        return to_route('casos-de-teste.index');
    }

    /**
     * Replace all passos of a Caso de Teste with the given ordered list.
     *
     * @param  array<int, array{palavra_chave: string, texto: string}>  $passos
     */
    private function syncPassos(CasoDeTeste $casoDeTeste, array $passos): void
    {
        $casoDeTeste->passos()->delete();

        $casoDeTeste->passos()->createMany(
            collect($passos)->values()->map(fn (array $passo, int $index): array => [
                'ordem' => $index,
                'palavra_chave' => $passo['palavra_chave'],
                'texto' => $passo['texto'],
            ])->all()
        );
    }
}
