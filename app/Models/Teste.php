<?php

namespace App\Models;

use App\Enums\TesteStatus;
use Database\Factories\TesteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $repo_name
 * @property int $issue_number
 * @property string|null $titulo
 * @property TesteStatus $status
 * @property string $percent_complete
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['repo_name', 'issue_number', 'titulo', 'status', 'percent_complete'])]
class Teste extends Model
{
    /** @use HasFactory<TesteFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'issue_number' => 'integer',
            'status' => TesteStatus::class,
            'percent_complete' => 'decimal:2',
        ];
    }

    public function cenarios(): HasMany
    {
        return $this->hasMany(Cenario::class);
    }
}
