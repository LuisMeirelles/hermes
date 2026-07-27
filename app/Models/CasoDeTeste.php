<?php

namespace App\Models;

use Database\Factories\CasoDeTesteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $titulo
 * @property string|null $descricao
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['titulo', 'descricao'])]
class CasoDeTeste extends Model
{
    /** @use HasFactory<CasoDeTesteFactory> */
    use HasFactory;

    protected $table = 'casos_de_teste';

    public function passos(): HasMany
    {
        return $this->hasMany(CasoDeTestePasso::class)->orderBy('ordem');
    }

    public function cenarios(): HasMany
    {
        return $this->hasMany(Cenario::class);
    }
}
