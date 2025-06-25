<?php

namespace App\Http\Requests\Api\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Implementar autorização conforme necessário
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'type' => ['required', Rule::in(['individual', 'company'])],
            'document' => 'required|string|max:18|unique:clients,document',
            'phone' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address_line' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|size:2',
            'zip_code' => 'nullable|string|max:10',
            'birth_date' => 'nullable|date',
            'observations' => 'nullable|string',
            'active' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.max' => 'O nome não pode ter mais de 200 caracteres',
            'type.required' => 'O tipo é obrigatório',
            'type.in' => 'O tipo deve ser individual ou company',
            'document.required' => 'O documento é obrigatório',
            'document.unique' => 'Este documento já está cadastrado',
            'phone.required' => 'O telefone é obrigatório',
            'email.email' => 'Email deve ter formato válido',
            'state.size' => 'Estado deve ter 2 caracteres',
            'birth_date.date' => 'Data de nascimento deve ser uma data válida'
        ];
    }
}
