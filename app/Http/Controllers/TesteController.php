<?php

namespace App\Http\Controllers;

use App\Enums\TesteStatus;
use App\Http\Requests\Teste\StoreTesteRequest;
use App\Models\CasoDeTeste;
use App\Models\Teste;
use App\Services\GithubApp;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TesteController extends Controller
{
    /**
     * Show the list of Testes, optionally filtered by status
     * (an exact TesteStatus value, or the virtual group "pendente").
     */
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in([
                ...array_column(TesteStatus::cases(), 'value'),
                'pendente',
            ])],
        ]);

        $status = $validated['status'] ?? null;

        $testes = Teste::query()
            ->when($status === 'pendente', fn ($query) => $query->whereIn('status', TesteStatus::pendentes()))
            ->when($status !== null && $status !== 'pendente', fn ($query) => $query->where('status', TesteStatus::from($status)))
            ->latest('id')
            ->get();

        return Inertia::render('testes/index', [
            'testes' => $testes,
            'statusFilter' => $status,
        ]);
    }

    /**
     * Show the form to pick a repo + issue and create a Teste shell.
     */
    public function create(GithubApp $githubApp): Response
    {
        return Inertia::render('testes/create', [
            'repositorios' => collect($githubApp->listRepositories())
                ->map(fn (array $repo): array => [
                    'name' => $repo['name'],
                    'full_name' => $repo['full_name'],
                ])
                ->values()
                ->all(),
        ]);
    }

    /**
     * Live-preview a GitHub issue before a Teste is created for it.
     */
    public function lookupIssue(Request $request, GithubApp $githubApp): JsonResponse
    {
        $data = $request->validate([
            'repo_name' => ['required', 'string'],
            'issue_number' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $issue = $githubApp->getIssue($data['repo_name'], $data['issue_number']);
        } catch (RequestException) {
            return response()->json(['message' => __('Issue não encontrada.')], 404);
        }

        return response()->json([
            'title' => $issue['title'],
            'state' => $issue['state'],
            'html_url' => $issue['html_url'],
        ]);
    }

    public function store(StoreTesteRequest $request): RedirectResponse
    {
        $teste = Teste::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Teste criado.')]);

        return to_route('testes.show', $teste);
    }

    /**
     * Show a Teste's detail, fetching its linked issue live.
     */
    public function show(Teste $teste, GithubApp $githubApp): Response
    {
        return Inertia::render('testes/show', [
            'teste' => $teste,
            'issue' => $githubApp->getIssue($teste->repo_name, $teste->issue_number),
            'cenarios' => $teste->cenarios()->latest('id')->get(),
            'casosDeTeste' => CasoDeTeste::query()->withCount('passos')->latest('id')->get(),
        ]);
    }

    public function destroy(Teste $teste): RedirectResponse
    {
        $teste->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Teste excluído.')]);

        return to_route('testes.index');
    }
}
