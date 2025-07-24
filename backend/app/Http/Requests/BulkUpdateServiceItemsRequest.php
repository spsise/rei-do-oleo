<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateServiceItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'A lista de itens é obrigatória',
            'items.array' => 'A lista de itens deve ser um array',
            'items.min' => 'Pelo menos um item deve ser fornecido',
            'items.*.product_id.required' => 'O ID do produto é obrigatório',
            'items.*.product_id.exists' => 'O produto selecionado não existe',
            'items.*.quantity.required' => 'A quantidade é obrigatória',
            'items.*.quantity.integer' => 'A quantidade deve ser um número inteiro',
            'items.*.quantity.min' => 'A quantidade deve ser pelo menos 1',
            'items.*.unit_price.required' => 'O preço unitário é obrigatório',
            'items.*.unit_price.numeric' => 'O preço unitário deve ser um número',
            'items.*.unit_price.min' => 'O preço unitário deve ser maior que zero',
            'items.*.discount.numeric' => 'O desconto deve ser um número',
            'items.*.discount.min' => 'O desconto não pode ser negativo',
            'items.*.discount.max' => 'O desconto não pode ser maior que 100%',
            'items.*.notes.string' => 'As observações devem ser um texto',
            'items.*.notes.max' => 'As observações não podem ter mais de 500 caracteres'
        ];
    }

    public function validated($key = null, $default = null): array
    {

        $validated = parent::validated($key, $default);

        // Ensure discount is set to 0 if not provided
        foreach ($validated['items'] as &$item) {
            if (!isset($item['discount'])) {
                $item['discount'] = 0;
            }
        }

        return $validated;
    }
}
