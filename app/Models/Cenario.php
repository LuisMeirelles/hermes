<?php

namespace App\Models;

use App\Enums\CenarioStatus;
use App\Enums\Severidade;
use Database\Factories\CenarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $teste_id
 * @property int|null $caso_de_teste_id
 * @property int|null $cloned_from_cenario_id
 * @property string $titulo
 * @property array<int, array{ordem: int, palavra_chave: string, texto: string}> $passos_snapshot
 * @property CenarioStatus $status
 * @property Severidade $severidade
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['teste_id', 'caso_de_teste_id', 'cloned_from_cenario_id', 'titulo', 'passos_snapshot', 'status', 'severidade'])]
class Cenario extends Model
{
    /** @use HasFactory<CenarioFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'passos_snapshot' => 'array',
            'status' => CenarioStatus::class,
            'severidade' => Severidade::class,
        ];
    }

    public function teste(): BelongsTo
    {
        return $this->belongsTo(Teste::class);
    }

    public function casoDeTeste(): BelongsTo
    {
        return $this->belongsTo(CasoDeTeste::class);
    }

    public function clonedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cloned_from_cenario_id');
    }
}
