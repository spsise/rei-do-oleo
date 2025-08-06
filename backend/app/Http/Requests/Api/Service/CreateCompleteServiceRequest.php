<?php

namespace App\Http\Requests\Api\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CreateCompleteServiceRequest extends FormRequest
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
                'max:480'
            ],
            'priority' => [
                'nullable',
                Rule::in(['low', 'medium', 'high'])
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
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'observations' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'service_items' => [
                'nullable',
                'array'
            ],
            'service_items.*.product_id' => [
                'required_with:service_items',
                'integer',
                'exists:products,id'
            ],
            'service_items.*.quantity' => [
                'required_with:service_items',
                'integer',
                'min:1',
                'max:100'
            ],
            'service_items.*.unit_price' => [
                'required_with:service_items',
                'numeric',
                'min:0'
            ],
            'service_items.*.notes' => [
                'nullable',
                'string',
                'max:500'
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
            'scheduled_at.after' => 'O agendamento deve ser para uma data futura',
            'technician_id.exists' => 'Técnico não encontrado',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres',
            'observations.max' => 'As observações detalhadas não podem ter mais de 2000 caracteres',
            'service_items.*.product_id.required_with' => 'O produto é obrigatório para cada item',
            'service_items.*.product_id.exists' => 'Produto não encontrado',
            'service_items.*.quantity.required_with' => 'A quantidade é obrigatória para cada item',
            'service_items.*.quantity.min' => 'A quantidade mínima é 1',
            'service_items.*.quantity.max' => 'A quantidade máxima é 100',
            'service_items.*.unit_price.required_with' => 'O preço unitário é obrigatório para cada item',
            'service_items.*.unit_price.min' => 'O preço unitário não pode ser negativo',
            'service_items.*.notes.max' => 'As observações do item não podem ter mais de 500 caracteres',
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
            'scheduled_at' => 'data de agendamento',
            'technician_id' => 'técnico',
            'notes' => 'observações',
            'observations' => 'observações detalhadas',
            'service_items' => 'itens do serviço',
            'service_items.*.product_id' => 'produto',
            'service_items.*.quantity' => 'quantidade',
            'service_items.*.unit_price' => 'preço unitário',
            'service_items.*.notes' => 'observações do item',
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

            // Validate technician belongs to same service center
            if ($this->technician_id) {
                $technician = \App\Domain\User\Models\User::find($this->technician_id);
                if ($technician && $technician->service_center_id !== $user->service_center_id) {
                    $validator->errors()->add('technician_id', 'O técnico deve pertencer ao mesmo centro de serviço');
                }
            }

            // Validate service items have sufficient stock
            if ($this->service_items) {
                foreach ($this->service_items as $index => $item) {
                    if (isset($item['product_id']) && isset($item['quantity'])) {
                        $product = \App\Domain\Product\Models\Product::find($item['product_id']);
                        if ($product && $product->stock_quantity < $item['quantity']) {
                            $validator->errors()->add(
                                "service_items.{$index}.quantity",
                                "Estoque insuficiente para o produto {$product->name}. Disponível: {$product->stock_quantity}"
                            );
                        }
                    }
                }
            }
        });
    }
}
