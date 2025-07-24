<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'sometimes|exists:products,id',
            'quantity' => 'sometimes|integer|min:1',
            'unit_price' => 'sometimes|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => 'O produto selecionado não existe',
            'quantity.integer' => 'A quantidade deve ser um número inteiro',
            'quantity.min' => 'A quantidade deve ser pelo menos 1',
            'unit_price.numeric' => 'O preço unitário deve ser um número',
            'unit_price.min' => 'O preço unitário deve ser maior que zero',
            'discount.numeric' => 'O desconto deve ser um número',
            'discount.min' => 'O desconto não pode ser negativo',
            'discount.max' => 'O desconto não pode ser maior que 100%',
            'notes.string' => 'As observações devem ser um texto',
            'notes.max' => 'As observações não podem ter mais de 500 caracteres'
        ];
    }
}
