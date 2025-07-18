<?php

namespace App\Http\Requests\Api\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidateServiceRequest extends FormRequest
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
        return [
            'client_id' => [
                'nullable',
                'integer',
                'exists:clients,id'
            ],
            'vehicle_id' => [
                'nullable',
                'integer',
                'exists:vehicles,id'
            ],
            'description' => [
                'nullable',
                'string',
                'min:3',
                'max:500'
            ],
            'scheduled_at' => [
                'nullable',
                'date',
                'after:now'
            ],
            'technician_id' => [
                'nullable',
                'integer',
                'exists:users,id'
            ],
            'estimated_duration' => [
                'nullable',
                'integer',
                'min:15',
                'max:480'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.exists' => 'Cliente não encontrado',
            'vehicle_id.exists' => 'Veículo não encontrado',
            'description.min' => 'A descrição deve ter pelo menos 3 caracteres',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres',
            'scheduled_at.after' => 'O agendamento deve ser para uma data futura',
            'technician_id.exists' => 'Técnico não encontrado',
            'estimated_duration.min' => 'A duração mínima é de 15 minutos',
            'estimated_duration.max' => 'A duração máxima é de 8 horas',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'client_id' => 'cliente',
            'vehicle_id' => 'veículo',
            'description' => 'descrição',
            'scheduled_at' => 'data de agendamento',
            'technician_id' => 'técnico',
            'estimated_duration' => 'duração estimada',
        ];
    }
}
