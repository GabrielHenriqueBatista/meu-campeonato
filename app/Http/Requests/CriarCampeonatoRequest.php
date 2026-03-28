<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CriarCampeonatoRequest extends FormRequest
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
            'nome.required' => 'O nome do campeonato é obrigatório.',
            'nome.string'   => 'O nome do campeonato deve ser um texto.',
            'nome.max'      => 'O nome do campeonato deve ter no máximo 255 caracteres.',
        ];
    }
}
