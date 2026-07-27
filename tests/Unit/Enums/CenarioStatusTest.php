<?php

use App\Enums\CenarioStatus;

test('a_fazer can only move to em_andamento', function () {
    expect(CenarioStatus::AFazer->allowedNextStatuses())->toBe([CenarioStatus::EmAndamento]);
});

test('em_andamento can move to any terminal status', function () {
    expect(CenarioStatus::EmAndamento->allowedNextStatuses())->toBe([
        CenarioStatus::Passou,
        CenarioStatus::Falhou,
        CenarioStatus::Bloqueado,
    ]);
});

test('terminal statuses can only be reopened to em_andamento', function (CenarioStatus $status) {
    expect($status->allowedNextStatuses())->toBe([CenarioStatus::EmAndamento]);
})->with([CenarioStatus::Passou, CenarioStatus::Falhou, CenarioStatus::Bloqueado]);

test('isTerminal reports the terminal statuses correctly', function () {
    expect(CenarioStatus::AFazer->isTerminal())->toBeFalse();
    expect(CenarioStatus::EmAndamento->isTerminal())->toBeFalse();
    expect(CenarioStatus::Passou->isTerminal())->toBeTrue();
    expect(CenarioStatus::Falhou->isTerminal())->toBeTrue();
    expect(CenarioStatus::Bloqueado->isTerminal())->toBeTrue();
});
