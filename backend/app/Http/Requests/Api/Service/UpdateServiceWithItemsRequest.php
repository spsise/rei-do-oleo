<?php

namespace App\Http\Requests\Api\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceWithItemsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $serviceData = $this->input('service', []);

        // Remove undefined values and convert to null
        $cleanedServiceData = [];
        foreach ($serviceData as $key => $value) {
            if ($value !== 'undefined' && $value !== null) {
                $cleanedServiceData[$key] = $value;
            }
        }

        $this->merge(['service' => $cleanedServiceData]);

    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $serviceId = $this->route('id');

        return [
            // Service data validation
            'service' => 'required|array',
            'service.service_center_id' => 'sometimes|exists:service_centers,id',
            'service.client_id' => 'sometimes|exists:clients,id',
            'service.vehicle_id' => 'sometimes|exists:vehicles,id',
            'service.service_number' => ['sometimes', 'string', 'max:20', Rule::unique('services')->ignore($serviceId)],
            'service.description' => 'sometimes|string|min:3|max:500',
            'service.complaint' => 'nullable|string|max:1000',
            'service.diagnosis' => 'nullable|string|max:1000',
            'service.solution' => 'nullable|string|max:1000',
            'service.scheduled_at' => 'nullable|date',
            'service.started_at' => 'nullable|date',
            'service.completed_at' => 'nullable|date',
            'service.technician_id' => 'nullable|exists:users,id',
            'service.attendant_id' => 'nullable|exists:users,id',
            'service.service_status_id' => 'sometimes|exists:service_statuses,id',
            'service.payment_method_id' => 'nullable|exists:payment_methods,id',
            'service.mileage_at_service' => 'nullable|integer|min:0',
            'service.total_amount' => 'nullable|numeric|min:0',
            'service.discount_amount' => 'nullable|numeric|min:0',
            'service.final_amount' => 'nullable|numeric|min:0',
            'service.observations' => 'nullable|string|max:2000',
            'service.notes' => 'nullable|string|max:1000',
            'service.active' => 'sometimes|boolean',
            'service.estimated_duration' => 'nullable|integer|min:15|max:480',
            'service.priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],

            // Items operation validation
            'items' => 'required|array',
            'items.operation' => 'required|string|in:replace,update,merge',
            'items.remove_unsent' => 'sometimes|boolean',
            'items.data' => 'required|array',
            'items.data.*.product_id' => 'required|integer|exists:products,id',
            'items.data.*.quantity' => 'required|integer|min:1|max:999',
            'items.data.*.unit_price' => 'required|numeric|min:0',
            'items.data.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.data.*.notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'service.required' => 'Os dados do serviço são obrigatórios',
            'service.service_center_id.exists' => 'Centro de serviço não encontrado',
            'service.client_id.exists' => 'Cliente não encontrado',
            'service.vehicle_id.exists' => 'Veículo não encontrado',
            'service.service_number.unique' => 'Este número de serviço já existe',
            'service.description.min' => 'A descrição deve ter pelo menos 3 caracteres',
            'service.description.max' => 'A descrição não pode ter mais de 500 caracteres',
            'service.complaint.max' => 'A reclamação não pode ter mais de 1000 caracteres',
            'service.diagnosis.max' => 'O diagnóstico não pode ter mais de 1000 caracteres',
            'service.solution.max' => 'A solução não pode ter mais de 1000 caracteres',
            'service.technician_id.exists' => 'Técnico não encontrado',
            'service.attendant_id.exists' => 'Atendente não encontrado',
            'service.service_status_id.exists' => 'Status de serviço não encontrado',
            'service.payment_method_id.exists' => 'Método de pagamento não encontrado',
            'service.mileage_at_service.min' => 'A quilometragem não pode ser negativa',
            'service.total_amount.min' => 'O valor total não pode ser negativo',
            'service.discount_amount.min' => 'O desconto não pode ser negativo',
            'service.final_amount.min' => 'O valor final não pode ser negativo',
            'service.observations.max' => 'As observações não podem ter mais de 2000 caracteres',
            'service.notes.max' => 'As observações não podem ter mais de 1000 caracteres',
            'service.estimated_duration.min' => 'A duração mínima é de 15 minutos',
            'service.estimated_duration.max' => 'A duração máxima é de 8 horas',
            'service.priority.in' => 'A prioridade deve ser: baixa, normal, alta ou urgente',

            'items.required' => 'Os dados dos itens são obrigatórios',
            'items.operation.required' => 'A operação dos itens é obrigatória',
            'items.operation.in' => 'A operação deve ser: replace, update ou merge',
            'items.data.required' => 'Os dados dos itens são obrigatórios',
            'items.data.*.product_id.required' => 'O produto é obrigatório',
            'items.data.*.product_id.exists' => 'Produto não encontrado',
            'items.data.*.quantity.required' => 'A quantidade é obrigatória',
            'items.data.*.quantity.min' => 'A quantidade deve ser pelo menos 1',
            'items.data.*.quantity.max' => 'A quantidade não pode ser maior que 999',
            'items.data.*.unit_price.required' => 'O preço unitário é obrigatório',
            'items.data.*.unit_price.min' => 'O preço unitário não pode ser negativo',
            'items.data.*.discount.min' => 'O desconto não pode ser negativo',
            'items.data.*.discount.max' => 'O desconto não pode ser maior que 100%',
            'items.data.*.notes.max' => 'As observações do item não podem ter mais de 500 caracteres',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'service' => 'dados do serviço',
            'service.service_center_id' => 'centro de serviço',
            'service.client_id' => 'cliente',
            'service.vehicle_id' => 'veículo',
            'service.service_number' => 'número do serviço',
            'service.description' => 'descrição',
            'service.complaint' => 'reclamação',
            'service.diagnosis' => 'diagnóstico',
            'service.solution' => 'solução',
            'service.scheduled_at' => 'data de agendamento',
            'service.started_at' => 'data de início',
            'service.completed_at' => 'data de conclusão',
            'service.technician_id' => 'técnico',
            'service.attendant_id' => 'atendente',
            'service.service_status_id' => 'status do serviço',
            'service.payment_method_id' => 'método de pagamento',
            'service.mileage_at_service' => 'quilometragem',
            'service.total_amount' => 'valor total',
            'service.discount_amount' => 'valor do desconto',
            'service.final_amount' => 'valor final',
            'service.observations' => 'observações',
            'service.notes' => 'observações',
            'service.estimated_duration' => 'duração estimada',
            'service.priority' => 'prioridade',

            'items' => 'itens do serviço',
            'items.operation' => 'operação dos itens',
            'items.data' => 'dados dos itens',
            'items.data.*.product_id' => 'produto',
            'items.data.*.quantity' => 'quantidade',
            'items.data.*.unit_price' => 'preço unitário',
            'items.data.*.discount' => 'desconto',
            'items.data.*.notes' => 'observações do item',
        ];
    }
}
