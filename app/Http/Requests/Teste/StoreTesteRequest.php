<?php

namespace App\Http\Requests\Teste;

use App\Services\GithubApp;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Validation\Validator;

class StoreTesteRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'repo_name' => ['required', 'string', 'max:255'],
            'issue_number' => ['required', 'integer', 'min:1'],
            'titulo' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            try {
                app(GithubApp::class)->getIssue(
                    (string) $this->input('repo_name'),
                    (int) $this->input('issue_number'),
                );
            } catch (RequestException) {
                $validator->errors()->add('issue_number', __('Issue não encontrada.'));
            }
        });
    }
}
