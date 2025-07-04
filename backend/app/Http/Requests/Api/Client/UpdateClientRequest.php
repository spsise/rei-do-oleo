<?php

namespace App\Http\Requests\Api\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'name' => 'sometimes|required|string|max:200',
            'type' => ['sometimes', 'required', Rule::in(['pessoa_fisica', 'pessoa_juridica'])],
            'document' => 'sometimes|required|string|max:18',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|size:2',
            'zip_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'active' => 'boolean'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            $document = $this->input('document');
            $clientId = $this->route('id');

            if ($type && $document) {
                // Clean the document (remove dots, dashes, etc.)
                $cleanDocument = preg_replace('/\D/', '', $document);

                if ($type === 'pessoa_fisica') {
                    // Validate CPF
                    if (strlen($cleanDocument) !== 11) {
                        $validator->errors()->add('document', 'CPF deve ter 11 dígitos');
                    }

                    // Check if CPF already exists (excluding the current client)
                    $existingClient = \App\Domain\Client\Models\Client::where('cpf', $cleanDocument)
                        ->where('id', '!=', $clientId)
                        ->first();
                    if ($existingClient) {
                        $validator->errors()->add('document', 'Este CPF já está cadastrado');
                    }
                } elseif ($type === 'pessoa_juridica') {
                    // Validate CNPJ
                    if (strlen($cleanDocument) !== 14) {
                        $validator->errors()->add('document', 'CNPJ deve ter 14 dígitos');
                    }

                    // Check if CNPJ already exists (excluding the current client)
                    $existingClient = \App\Domain\Client\Models\Client::where('cnpj', $cleanDocument)
                        ->where('id', '!=', $clientId)
                        ->first();
                    if ($existingClient) {
                        $validator->errors()->add('document', 'Este CNPJ já está cadastrado');
                    }
                }
            }
        });
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
            'type.in' => 'O tipo deve ser pessoa_fisica ou pessoa_juridica',
            'document.required' => 'O documento é obrigatório',
            'phone.max' => 'O telefone não pode ter mais de 20 caracteres',
            'email.email' => 'Email deve ter formato válido',
            'state.size' => 'Estado deve ter 2 caracteres',
            'zip_code.max' => 'CEP não pode ter mais de 10 caracteres'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Clean the document before validation
        if ($this->has('document')) {
            $this->merge([
                'document' => preg_replace('/\D/', '', $this->document)
            ]);
        }
    }
}
