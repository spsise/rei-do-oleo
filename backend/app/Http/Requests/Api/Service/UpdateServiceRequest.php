<?php

namespace App\Http\Requests\Api\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
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
        $serviceId = $this->route('id');

        return [
            'service_center_id' => 'sometimes|exists:service_centers,id',
            'client_id' => 'sometimes|exists:clients,id',
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'service_number' => ['sometimes', 'string', 'max:20', Rule::unique('services')->ignore($serviceId)],
            'description' => 'sometimes|string|min:3|max:500',
            'complaint' => 'nullable|string|max:1000',
            'diagnosis' => 'nullable|string|max:1000',
            'solution' => 'nullable|string|max:1000',
            'scheduled_at' => 'nullable|date',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'technician_id' => 'nullable|exists:users,id',
            'attendant_id' => 'nullable|exists:users,id',
            'service_status_id' => 'sometimes|exists:service_statuses,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'mileage_at_service' => 'nullable|integer|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'final_amount' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:1000',
            'active' => 'sometimes|boolean',
            'estimated_duration' => 'nullable|integer|min:15|max:480',
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|integer|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'service_center_id.exists' => 'Centro de serviço não encontrado',
            'client_id.exists' => 'Cliente não encontrado',
            'vehicle_id.exists' => 'Veículo não encontrado',
            'service_number.unique' => 'Este número de serviço já existe',
            'description.min' => 'A descrição deve ter pelo menos 3 caracteres',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres',
            'complaint.max' => 'A reclamação não pode ter mais de 1000 caracteres',
            'diagnosis.max' => 'O diagnóstico não pode ter mais de 1000 caracteres',
            'solution.max' => 'A solução não pode ter mais de 1000 caracteres',
            'technician_id.exists' => 'Técnico não encontrado',
            'attendant_id.exists' => 'Atendente não encontrado',
            'service_status_id.exists' => 'Status de serviço não encontrado',
            'payment_method_id.exists' => 'Método de pagamento não encontrado',
            'mileage_at_service.min' => 'A quilometragem não pode ser negativa',
            'total_amount.min' => 'O valor total não pode ser negativo',
            'discount_amount.min' => 'O desconto não pode ser negativo',
            'final_amount.min' => 'O valor final não pode ser negativo',
            'observations.max' => 'As observações não podem ter mais de 2000 caracteres',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres',
            'estimated_duration.min' => 'A duração mínima é de 15 minutos',
            'estimated_duration.max' => 'A duração máxima é de 8 horas',
            'priority.in' => 'A prioridade deve ser: baixa, normal, alta ou urgente',
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
            'service_center_id' => 'centro de serviço',
            'client_id' => 'cliente',
            'vehicle_id' => 'veículo',
            'service_number' => 'número do serviço',
            'description' => 'descrição',
            'complaint' => 'reclamação',
            'diagnosis' => 'diagnóstico',
            'solution' => 'solução',
            'scheduled_at' => 'data de agendamento',
            'started_at' => 'data de início',
            'completed_at' => 'data de conclusão',
            'technician_id' => 'técnico',
            'attendant_id' => 'atendente',
            'service_status_id' => 'status do serviço',
            'payment_method_id' => 'método de pagamento',
            'mileage_at_service' => 'quilometragem',
            'total_amount' => 'valor total',
            'discount_amount' => 'valor do desconto',
            'final_amount' => 'valor final',
            'observations' => 'observações',
            'notes' => 'observações',
            'estimated_duration' => 'duração estimada',
            'priority' => 'prioridade',
            'items' => 'itens do serviço',
            'items.*.product_id' => 'produto',
            'items.*.quantity' => 'quantidade',
            'items.*.unit_price' => 'preço unitário',
            'items.*.discount' => 'desconto',
            'items.*.notes' => 'observações do item',
        ];
    }
}
