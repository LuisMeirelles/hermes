<?php

use App\Enums\CenarioStatus;
use App\Enums\Severidade;
use App\Enums\TesteStatus;
use App\Services\TesteAggregateCalculator;

/**
 * @return array{status: CenarioStatus, severidade: Severidade}
 */
function cenarioPair(CenarioStatus $status, Severidade $severidade = Severidade::Maior): array
{
    return ['status' => $status, 'severidade' => $severidade];
}

test('an empty teste with no previous status defaults to nao_iniciado at 100%', function () {
    $result = (new TesteAggregateCalculator)->calculate([]);

    expect($result->status)->toBe(TesteStatus::NaoIniciado);
    expect($result->percentComplete)->toBe(100.0);
});

test('a teste where every cenario is bloqueado with a non-bloqueante severidade keeps the previous teste status at 100%', function (TesteStatus $statusAnterior) {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::Bloqueado, Severidade::Maior),
        cenarioPair(CenarioStatus::Bloqueado, Severidade::Menor),
    ], $statusAnterior);

    expect($result->status)->toBe($statusAnterior);
    expect($result->percentComplete)->toBe(100.0);
})->with([
    TesteStatus::NaoIniciado,
    TesteStatus::EmAndamento,
    TesteStatus::Passou,
    TesteStatus::Falhou,
    TesteStatus::Parcial,
]);

test('a bloqueado cenario with bloqueante/critica severidade counts as a failure', function (Severidade $severidade) {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::Bloqueado, $severidade),
    ]);

    expect($result->status)->toBe(TesteStatus::Falhou);
    expect($result->percentComplete)->toBe(100.0);
})->with([Severidade::Bloqueante, Severidade::Critica]);

test('a bloqueado cenario with bloqueante severidade forces falhou even alongside passed cenarios', function () {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::Bloqueado, Severidade::Critica),
        cenarioPair(CenarioStatus::Passou),
        cenarioPair(CenarioStatus::Passou),
    ]);

    expect($result->status)->toBe(TesteStatus::Falhou);
    expect($result->percentComplete)->toBe(100.0);
});

test('a teste where every cenario is still a_fazer is nao_iniciado at 0%', function () {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::AFazer),
        cenarioPair(CenarioStatus::AFazer),
    ]);

    expect($result->status)->toBe(TesteStatus::NaoIniciado);
    expect($result->percentComplete)->toBe(0.0);
});

test('a mix of a_fazer and em_andamento with no terminal cenarios is em_andamento', function () {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::AFazer),
        cenarioPair(CenarioStatus::EmAndamento),
    ]);

    expect($result->status)->toBe(TesteStatus::EmAndamento);
    expect($result->percentComplete)->toBe(0.0);
});

test('a mix of passou and a_fazer with no failures is em_andamento with a partial percent', function () {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::Passou),
        cenarioPair(CenarioStatus::AFazer),
    ]);

    expect($result->status)->toBe(TesteStatus::EmAndamento);
    expect($result->percentComplete)->toBe(50.0);
});

test('a teste where every cenario passou is passou at 100%', function () {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::Passou),
        cenarioPair(CenarioStatus::Passou),
    ]);

    expect($result->status)->toBe(TesteStatus::Passou);
    expect($result->percentComplete)->toBe(100.0);
});

test('a minority of maior/menor failures among otherwise passed cenarios is parcial', function (Severidade $severidade) {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::Falhou, $severidade),
        cenarioPair(CenarioStatus::Passou),
        cenarioPair(CenarioStatus::Passou),
    ]);

    expect($result->status)->toBe(TesteStatus::Parcial);
    expect($result->percentComplete)->toBe(100.0);
})->with([Severidade::Maior, Severidade::Menor]);

test('exactly half of the efetivos failing with maior/menor severidade is still parcial', function () {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::Falhou, Severidade::Menor),
        cenarioPair(CenarioStatus::Passou),
    ]);

    expect($result->status)->toBe(TesteStatus::Parcial);
    expect($result->percentComplete)->toBe(100.0);
});

test('a majority of maior/menor failures among the efetivos is falhou, not parcial', function () {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::Falhou, Severidade::Menor),
        cenarioPair(CenarioStatus::Falhou, Severidade::Maior),
    ]);

    expect($result->status)->toBe(TesteStatus::Falhou);
    expect($result->percentComplete)->toBe(100.0);
});

test('a majority of maior/menor failures is falhou even while other efetivos are still pending', function () {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::Falhou, Severidade::Menor),
        cenarioPair(CenarioStatus::Falhou, Severidade::Maior),
        cenarioPair(CenarioStatus::Falhou, Severidade::Menor),
        cenarioPair(CenarioStatus::EmAndamento),
        cenarioPair(CenarioStatus::AFazer),
    ]);

    expect($result->status)->toBe(TesteStatus::Falhou);
    expect($result->percentComplete)->toBe(60.0);
});

test('a failure with bloqueante/critica severidade is falhou even while other cenarios are still pending', function (Severidade $severidade) {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::Falhou, $severidade),
        cenarioPair(CenarioStatus::AFazer),
        cenarioPair(CenarioStatus::EmAndamento),
    ]);

    expect($result->status)->toBe(TesteStatus::Falhou);
})->with([Severidade::Bloqueante, Severidade::Critica]);

test('bloqueado cenarios are excluded from both the failure check and the percent denominator', function () {
    $result = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::Bloqueado),
        cenarioPair(CenarioStatus::Falhou, Severidade::Critica),
        cenarioPair(CenarioStatus::Passou),
    ]);

    expect($result->status)->toBe(TesteStatus::Falhou);
    expect($result->percentComplete)->toBe(100.0);
});

test('reopening a terminal cenario back to em_andamento is reflected purely from current state', function () {
    $beforeReopen = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::Falhou, Severidade::Menor),
        cenarioPair(CenarioStatus::Passou),
    ]);

    expect($beforeReopen->status)->toBe(TesteStatus::Parcial);

    $afterReopen = (new TesteAggregateCalculator)->calculate([
        cenarioPair(CenarioStatus::EmAndamento),
    ]);

    expect($afterReopen->status)->toBe(TesteStatus::EmAndamento);
});
