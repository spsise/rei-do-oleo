<?php

namespace App\Http\Requests\Api\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CreateQuickServiceRequest extends FormRequest
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
                'required',
                'integer',
                'exists:clients,id'
            ],
            'vehicle_id' => [
                'required',
                'integer',
                'exists:vehicles,id'
            ],
            'description' => [
                'required',
                'string',
                'min:3',
                'max:500'
            ],
            'estimated_duration' => [
                'nullable',
                'integer',
                'min:15',
                'max:480' // 8 hours max
            ],
            'priority' => [
                'nullable',
                Rule::in(['low', 'medium', 'high'])
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'template_id' => [
                'nullable',
                'integer',
                'exists:service_templates,id'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'O cliente é obrigatório',
            'client_id.exists' => 'Cliente não encontrado',
            'vehicle_id.required' => 'O veículo é obrigatório',
            'vehicle_id.exists' => 'Veículo não encontrado',
            'description.required' => 'A descrição do serviço é obrigatória',
            'description.min' => 'A descrição deve ter pelo menos 3 caracteres',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres',
            'estimated_duration.min' => 'A duração mínima é de 15 minutos',
            'estimated_duration.max' => 'A duração máxima é de 8 horas',
            'priority.in' => 'A prioridade deve ser: baixa, média ou alta',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres',
            'template_id.exists' => 'Template não encontrado',
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
            'estimated_duration' => 'duração estimada',
            'priority' => 'prioridade',
            'notes' => 'observações',
            'template_id' => 'template',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate client-vehicle relationship
            if ($this->client_id && $this->vehicle_id) {
                $vehicle = \App\Domain\Client\Models\Vehicle::find($this->vehicle_id);

                if ($vehicle && $vehicle->client_id !== (int) $this->client_id) {
                    $validator->errors()->add('vehicle_id', 'O veículo não pertence ao cliente informado');
                }
            }

            // Validate user has access to service center
            $user = Auth::user();
            if (!$user || !$user->service_center_id) {
                $validator->errors()->add('service_center', 'Usuário não está associado a um centro de serviço');
            }
        });
    }
}
