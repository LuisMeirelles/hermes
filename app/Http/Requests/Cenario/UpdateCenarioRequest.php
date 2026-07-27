<?php

namespace App\Http\Requests\Cenario;

use App\Enums\CenarioStatus;
use App\Enums\Severidade;
use App\Models\Cenario;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCenarioRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(CenarioStatus::class)],
            'severidade' => ['sometimes', Rule::enum(Severidade::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('status') || $validator->errors()->has('status')) {
                return;
            }

            /** @var Cenario $cenario */
            $cenario = $this->route('cenario');
            $requested = CenarioStatus::from($this->input('status'));

            if (! in_array($requested, $cenario->status->allowedNextStatuses(), true)) {
                $validator->errors()->add('status', __('Transição de status inválida.'));
            }
        });
    }
}
