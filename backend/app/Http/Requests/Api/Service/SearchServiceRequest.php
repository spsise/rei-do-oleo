<?php

namespace App\Http\Requests\Api\Service;

use Illuminate\Foundation\Http\FormRequest;

class SearchServiceRequest extends FormRequest
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
            'search' => 'nullable|string|max:100',
            'service_center_id' => 'nullable|integer|exists:service_centers,id',
            'client_id' => 'nullable|integer|exists:clients,id',
            'vehicle_id' => 'nullable|integer|exists:vehicles,id',
            'status' => 'nullable|string',
            'technician_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:1|max:100',
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
            'technician_id.exists' => 'Técnico não encontrado',
            'date_to.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial',
            'per_page.min' => 'O número de itens por página deve ser pelo menos 1',
            'per_page.max' => 'O número de itens por página não pode ser maior que 100',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'search' => 'busca',
            'service_center_id' => 'centro de serviço',
            'client_id' => 'cliente',
            'vehicle_id' => 'veículo',
            'status' => 'status',
            'technician_id' => 'técnico',
            'date_from' => 'data inicial',
            'date_to' => 'data final',
            'per_page' => 'itens por página',
        ];
    }
}
