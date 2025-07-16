<?php

namespace App\Http\Requests\Api\Client;

use Illuminate\Foundation\Http\FormRequest;

class SearchClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => 'required|string|max:18',
            'phone' => 'required|string|max:20'
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'O documento é obrigatório',
            'phone.required' => 'O telefone é obrigatório'
        ];
    }
}
