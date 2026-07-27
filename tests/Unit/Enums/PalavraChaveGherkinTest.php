<?php

use App\Enums\PalavraChaveGherkin;

test('dado, quando and entao have an increasing fase ordinal', function () {
    expect(PalavraChaveGherkin::Dado->faseOrdinal())->toBe(0);
    expect(PalavraChaveGherkin::Quando->faseOrdinal())->toBe(1);
    expect(PalavraChaveGherkin::Entao->faseOrdinal())->toBe(2);
});

test('e and mas have no fase ordinal of their own', function (PalavraChaveGherkin $palavraChave) {
    expect($palavraChave->faseOrdinal())->toBeNull();
})->with([PalavraChaveGherkin::E, PalavraChaveGherkin::Mas]);
