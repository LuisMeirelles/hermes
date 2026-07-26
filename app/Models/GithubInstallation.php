<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
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
    protected function casts(): array
    {
        return [
            'installation_id' => 'integer',
            'suspended_at' => 'datetime',
            'uninstalled_at' => 'datetime',
        ];
    }
}
