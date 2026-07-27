<?php

namespace App\Models;

use App\Enums\PalavraChaveGherkin;
use Database\Factories\CasoDeTestePassoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $caso_de_teste_id
 * @property int $ordem
 * @property PalavraChaveGherkin $palavra_chave
 * @property string $texto
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['caso_de_teste_id', 'ordem', 'palavra_chave', 'texto'])]
class CasoDeTestePasso extends Model
{
    /** @use HasFactory<CasoDeTestePassoFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
            'palavra_chave' => PalavraChaveGherkin::class,
        ];
    }

    public function casoDeTeste(): BelongsTo
    {
        return $this->belongsTo(CasoDeTeste::class);
    }
}
