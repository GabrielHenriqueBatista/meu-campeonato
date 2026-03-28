<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InscricaoTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do time é obrigatório.',
            'nome.string'   => 'O nome do time deve ser um texto.',
            'nome.max'      => 'O nome do time deve ter no máximo 255 caracteres.',
        ];
    }
}
