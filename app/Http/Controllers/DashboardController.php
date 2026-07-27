<?php

namespace App\Http\Controllers;

use App\Enums\CenarioStatus;
use App\Enums\Severidade;
use App\Enums\TesteStatus;
use App\Models\CasoDeTeste;
use App\Models\Cenario;
use App\Models\Teste;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('dashboard', [
            'stats' => $this->statusCounts(),
            'cenariosBloqueantes' => $this->cenariosBloqueantesEmAberto(),
            'testesRecentes' => Teste::query()->latest('updated_at')->limit(5)->get(),
            'casosDeTeste' => $this->bibliotecaCasosDeTeste(),
        ]);
    }

    /**
     * @return array{total: int, sucesso: int, falha: int, parcial: int, pendente: int}
     */
    private function statusCounts(): array
    {
        $counts = Teste::query()
            ->toBase()
            ->select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'sucesso' => (int) ($counts[TesteStatus::Passou->value] ?? 0),
            'falha' => (int) ($counts[TesteStatus::Falhou->value] ?? 0),
            'parcial' => (int) ($counts[TesteStatus::Parcial->value] ?? 0),
            'pendente' => (int) collect(TesteStatus::pendentes())
                ->sum(fn (TesteStatus $status): int => $counts[$status->value] ?? 0),
        ];
    }

    /**
     * Cenários com severidade bloqueante/crítica que ainda não passaram.
     * "Bloqueado" é incluído propositalmente: pela regra de negócio, um
     * cenário bloqueado com severidade bloqueante/crítica já conta como
     * falha efetiva no Teste, não é neutro para fins de atenção do QA.
     *
     * @return Collection<int, Cenario>
     */
    private function cenariosBloqueantesEmAberto(): Collection
    {
        return Cenario::query()
            ->whereIn('severidade', [Severidade::Bloqueante, Severidade::Critica])
            ->where('status', '!=', CenarioStatus::Passou)
            ->with('teste:id,repo_name,issue_number,titulo')
            ->latest('id')
            ->limit(10)
            ->get();
    }

    /**
     * @return array{total: int, naoUtilizados: int}
     */
    private function bibliotecaCasosDeTeste(): array
    {
        return [
            'total' => CasoDeTeste::query()->count(),
            'naoUtilizados' => CasoDeTeste::query()->whereDoesntHave('cenarios')->count(),
        ];
    }
}
