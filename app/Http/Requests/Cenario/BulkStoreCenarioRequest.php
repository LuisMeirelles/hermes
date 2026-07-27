<?php

namespace App\Http\Requests\Cenario;

use App\Enums\Severidade;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreCenarioRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'casos' => ['required', 'array', 'min:1'],
            'casos.*.caso_de_teste_id' => ['required', 'integer', 'exists:casos_de_teste,id'],
            'casos.*.severidade' => ['required', Rule::enum(Severidade::class)],
        ];
    }
}
