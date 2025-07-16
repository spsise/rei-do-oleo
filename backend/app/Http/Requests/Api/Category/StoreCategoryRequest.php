<?php

namespace App\Http\Requests\Api\Category;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.unique' => 'Esta categoria já existe',
            'name.max' => 'O nome não pode ter mais de 100 caracteres',
            'sort_order.min' => 'A ordem deve ser maior ou igual a 0'
        ];
    }
}
