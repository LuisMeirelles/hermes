<?php

namespace App\Http\Requests\CasoDeTeste;

use App\Concerns\CasoDeTesteValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCasoDeTesteRequest extends FormRequest
{
    use CasoDeTesteValidationRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->casoDeTesteRules();
    }
}
