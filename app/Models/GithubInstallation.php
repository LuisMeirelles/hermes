<?php

namespace App\Models;

use Database\Factories\GithubInstallationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $installation_id
 * @property string $account_login
 * @property string $account_type
 * @property Carbon|null $suspended_at
 * @property Carbon|null $uninstalled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['installation_id', 'account_login', 'account_type', 'suspended_at', 'uninstalled_at'])]
class GithubInstallation extends Model
{
    /** @use HasFactory<GithubInstallationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'installation_id' => 'integer',
            'suspended_at' => 'datetime',
            'uninstalled_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNull('uninstalled_at')->latest('id');
    }
}
