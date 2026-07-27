<?php

use App\Enums\TesteStatus;

test('pendentes returns nao_iniciado and em_andamento', function () {
    expect(TesteStatus::pendentes())->toBe([
        TesteStatus::NaoIniciado,
        TesteStatus::EmAndamento,
    ]);
});
