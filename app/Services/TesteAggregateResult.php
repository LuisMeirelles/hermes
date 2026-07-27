<?php

namespace App\Services;

use App\Enums\TesteStatus;

final readonly class TesteAggregateResult
{
    public function __construct(
        public TesteStatus $status,
        public float $percentComplete,
    ) {}
}
