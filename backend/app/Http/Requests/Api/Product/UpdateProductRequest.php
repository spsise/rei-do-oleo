<?php

namespace App\Http\Requests\Api\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
        if ($this->has('sku')) {
            $this->merge([
                'sku' => strtoupper(trim($this->sku))
            ]);
        }
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
            'sku' => 'required|string|max:50|unique:products,sku,' . $this->route('id'),
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:20',
            'active' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.string' => 'O nome deve ser uma string',
            'name.max' => 'O nome deve ter no máximo 200 caracteres',
            'sku.required' => 'O SKU é obrigatório',
            'sku.string' => 'O SKU deve ser uma string',
            'sku.max' => 'O SKU deve ter no máximo 50 caracteres',
            'sku.unique' => 'O SKU já existe',
            'description.string' => 'A descrição deve ser uma string',
            'category_id.required' => 'A categoria é obrigatória',
            'category_id.exists' => 'A categoria não existe',
            'price.required' => 'O preço é obrigatório',
            'price.numeric' => 'O preço deve ser um número',
            'price.min' => 'O preço deve ser maior que 0',
            'stock_quantity.required' => 'A quantidade em estoque é obrigatória',
            'stock_quantity.integer' => 'A quantidade em estoque deve ser um número inteiro',
            'stock_quantity.min' => 'A quantidade em estoque deve ser maior que 0',
            'min_stock.integer' => 'O estoque mínimo deve ser um número inteiro',
            'min_stock.min' => 'O estoque mínimo deve ser maior que 0',
            'unit.required' => 'A unidade é obrigatória',
            'unit.string' => 'A unidade deve ser uma string',
            'unit.max' => 'A unidade deve ter no máximo 20 caracteres',
            'active.required' => 'O status é obrigatório',
            'active.boolean' => 'O status deve ser um booleano',
        ];
    }
}
