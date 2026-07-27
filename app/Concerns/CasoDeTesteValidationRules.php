<?php

namespace App\Concerns;

use App\Enums\PalavraChaveGherkin;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait CasoDeTesteValidationRules
{
    /**
     * Get the validation rules used to validate a Caso de Teste and its passos.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function casoDeTesteRules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'passos' => ['required', 'array', 'min:1'],
            'passos.*.palavra_chave' => ['required', Rule::enum(PalavraChaveGherkin::class)],
            'passos.*.texto' => ['required', 'string'],
        ];
    }

    /**
     * Ensure the passos follow the Dado -> Quando -> Então progression, with
     * E/Mas only ever continuing a fase that has already started.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $passos = $this->input('passos');

            if (! is_array($passos)) {
                return;
            }

            $faseAtual = null;
            $fasesPresentes = [];

            foreach ($passos as $index => $passo) {
                if ($validator->errors()->has("passos.{$index}.palavra_chave")) {
                    continue;
                }

                $palavraChave = PalavraChaveGherkin::tryFrom($passo['palavra_chave'] ?? '');

                if (! $palavraChave instanceof PalavraChaveGherkin) {
                    continue;
                }

                $ordinal = $palavraChave->faseOrdinal();

                if ($ordinal === null) {
                    if ($faseAtual === null) {
                        $validator->errors()->add(
                            "passos.{$index}.palavra_chave",
                            __('Este passo não pode ser o primeiro nem vir antes de Dado, Quando ou Então.'),
                        );
                    }

                    continue;
                }

                if ($faseAtual !== null && $ordinal < $faseAtual) {
                    $validator->errors()->add(
                        "passos.{$index}.palavra_chave",
                        __('Os passos devem seguir a ordem Dado, Quando, Então.'),
                    );
                }

                $faseAtual = $ordinal;
                $fasesPresentes[$ordinal] = true;
            }

            if (! $validator->errors()->has('passos') && count($fasesPresentes) < 3) {
                $validator->errors()->add(
                    'passos',
                    __('É necessário pelo menos um passo em Dado, Quando e Então.'),
                );
            }
        });
    }
}
