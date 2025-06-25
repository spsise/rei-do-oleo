<?php

namespace App\Http\Requests\Api\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'name' => 'required|string|max:200',
            'sku' => 'required|string|max:50|unique:products,sku',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:20',
            'brand' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'active' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'sku.required' => 'O SKU é obrigatório',
            'sku.unique' => 'Este SKU já existe',
            'category_id.required' => 'A categoria é obrigatória',
            'category_id.exists' => 'Categoria não encontrada',
            'cost_price.required' => 'O preço de custo é obrigatório',
            'sale_price.required' => 'O preço de venda é obrigatório',
            'stock_quantity.required' => 'A quantidade em estoque é obrigatória',
            'unit.required' => 'A unidade é obrigatória'
        ];
    }
}
