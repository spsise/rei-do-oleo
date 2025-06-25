<?php

namespace App\Http\Requests\Api\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $clientId = $this->route('id');

        return [
            'name' => 'sometimes|string|max:200',
            'type' => ['sometimes', Rule::in(['individual', 'company'])],
            'document' => ['sometimes', 'string', 'max:18', Rule::unique('clients')->ignore($clientId)],
            'phone' => 'sometimes|string|max:20',
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
            'name.max' => 'O nome não pode ter mais de 200 caracteres',
            'type.in' => 'O tipo deve ser individual ou company',
            'document.unique' => 'Este documento já está cadastrado',
            'email.email' => 'Email deve ter formato válido',
            'state.size' => 'Estado deve ter 2 caracteres',
            'birth_date.date' => 'Data de nascimento deve ser uma data válida'
        ];
    }
}
