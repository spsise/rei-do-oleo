<?php

namespace App\Http\Requests\Api\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'license_plate' => 'required|string|max:8|unique:vehicles,license_plate',
            'brand' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'year' => 'required|integer|min:1950|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:30',
            'fuel_type' => 'nullable|string|max:20',
            'engine' => 'nullable|string|max:50',
            'chassis' => 'nullable|string|max:17',
            'renavam' => 'nullable|string|max:11',
            'mileage' => 'nullable|integer|min:0',
            'active' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'O cliente é obrigatório',
            'client_id.exists' => 'Cliente não encontrado',
            'license_plate.required' => 'A placa é obrigatória',
            'license_plate.unique' => 'Esta placa já está cadastrada',
            'brand.required' => 'A marca é obrigatória',
            'model.required' => 'O modelo é obrigatório',
            'year.required' => 'O ano é obrigatório',
            'year.min' => 'Ano deve ser maior que 1950',
            'year.max' => 'Ano não pode ser maior que ' . (date('Y') + 1)
        ];
    }
}
