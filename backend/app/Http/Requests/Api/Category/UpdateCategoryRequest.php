<?php

namespace App\Http\Requests\Api\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('id');

        return [
            'name' => 'sometimes|string|max:100|unique:categories,name,' . $categoryId,
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'boolean'
        ];
    }
}
