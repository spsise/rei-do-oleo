<?php

namespace App\Http\Requests\Api\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StoreServiceRequest extends FormRequest
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
            'service_center_id' => [
                'nullable',
                'integer',
                'exists:service_centers,id'
            ],
            'technician_id' => [
                'nullable',
                'integer',
                'exists:users,id'
            ],
            'attendant_id' => [
                'nullable',
                'integer',
                'exists:users,id'
            ],
            'service_number' => [
                'nullable',
                'string',
                'max:20',
                'unique:services,service_number'
            ],
            'scheduled_at' => [
                'nullable',
                'date',
                'after:now'
            ],
            'started_at' => [
                'nullable',
                'date'
            ],
            'completed_at' => [
                'nullable',
                'date'
            ],
            'service_status_id' => [
                'nullable',
                'integer',
                'exists:service_statuses,id'
            ],
            'payment_method_id' => [
                'nullable',
                'integer',
                'exists:payment_methods,id'
            ],
            'mileage_at_service' => [
                'nullable',
                'integer',
                'min:0'
            ],
            'total_amount' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            'final_amount' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            'observations' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'active' => [
                'boolean'
            ],
            // Campos específicos para técnicos
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
                'max:480'
            ],
            // Validação para itens do serviço
            'items' => [
                'nullable',
                'array'
            ],
            'items.*.product_id' => [
                'required_with:items',
                'integer',
                'exists:products,id'
            ],
            'items.*.quantity' => [
                'required_with:items',
                'integer',
                'min:1'
            ],
            'items.*.unit_price' => [
                'required_with:items',
                'numeric',
                'min:0'
            ],
            'items.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100'
            ],
            'items.*.notes' => [
                'nullable',
                'string',
                'max:500'
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
            'service_center_id.exists' => 'Centro de serviço não encontrado',
            'technician_id.exists' => 'Técnico não encontrado',
            'attendant_id.exists' => 'Atendente não encontrado',
            'service_number.unique' => 'Este número de serviço já existe',
            'scheduled_at.after' => 'O agendamento deve ser para uma data futura',
            'service_status_id.exists' => 'Status de serviço não encontrado',
            'payment_method_id.exists' => 'Método de pagamento não encontrado',
            'mileage_at_service.min' => 'A quilometragem não pode ser negativa',
            'total_amount.min' => 'O valor total não pode ser negativo',
            'discount_amount.min' => 'O desconto não pode ser negativo',
            'final_amount.min' => 'O valor final não pode ser negativo',
            'observations.max' => 'As observações não podem ter mais de 2000 caracteres',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres',
            'description.required' => 'A descrição do serviço é obrigatória',
            'description.min' => 'A descrição deve ter pelo menos 3 caracteres',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres',
            'estimated_duration.min' => 'A duração mínima é de 15 minutos',
            'estimated_duration.max' => 'A duração máxima é de 8 horas',
            // Mensagens para itens
            'items.*.product_id.required_with' => 'O produto é obrigatório',
            'items.*.product_id.exists' => 'Produto não encontrado',
            'items.*.quantity.required_with' => 'A quantidade é obrigatória',
            'items.*.quantity.min' => 'A quantidade deve ser pelo menos 1',
            'items.*.unit_price.required_with' => 'O preço unitário é obrigatório',
            'items.*.unit_price.min' => 'O preço unitário não pode ser negativo',
            'items.*.discount.min' => 'O desconto não pode ser negativo',
            'items.*.discount.max' => 'O desconto não pode ser maior que 100%',
            'items.*.notes.max' => 'As observações do item não podem ter mais de 500 caracteres',

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
            'service_center_id' => 'centro de serviço',
            'technician_id' => 'técnico',
            'attendant_id' => 'atendente',
            'service_number' => 'número do serviço',
            'scheduled_at' => 'data de agendamento',
            'started_at' => 'data de início',
            'completed_at' => 'data de conclusão',
            'service_status_id' => 'status do serviço',
            'payment_method_id' => 'método de pagamento',
            'mileage_at_service' => 'quilometragem',
            'total_amount' => 'valor total',
            'discount_amount' => 'valor do desconto',
            'final_amount' => 'valor final',
            'observations' => 'observações',
            'notes' => 'observações',
            'description' => 'descrição',
            'estimated_duration' => 'duração estimada',
            // Atributos para itens
            'items' => 'itens do serviço',
            'items.*.product_id' => 'produto',
            'items.*.quantity' => 'quantidade',
            'items.*.unit_price' => 'preço unitário',
            'items.*.discount' => 'desconto',
            'items.*.notes' => 'observações do item',

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

            // Validate technician belongs to same service center
            if ($this->technician_id) {
                $technician = \App\Domain\User\Models\User::find($this->technician_id);
                if ($technician && $technician->service_center_id !== $user->service_center_id) {
                    $validator->errors()->add('technician_id', 'O técnico deve pertencer ao mesmo centro de serviço');
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $user = Auth::user();

        // Set default service center if not provided
        if (!$this->service_center_id && $user) {
            $this->merge([
                'service_center_id' => $user->service_center_id
            ]);
        }

        // Set default status if not provided (scheduled)
        if (!$this->service_status_id) {
            $scheduledStatus = \App\Domain\Service\Models\ServiceStatus::findByName('scheduled');
            if ($scheduledStatus) {
                $this->merge([
                    'service_status_id' => $scheduledStatus->id
                ]);
            }
        }

        // Set default active status
        if (!$this->has('active')) {
            $this->merge([
                'active' => true
            ]);
        }

        // Set user as attendant if not provided
        if (!$this->attendant_id && $user) {
            $this->merge([
                'attendant_id' => $user->id
            ]);
        }
    }
}
