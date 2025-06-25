<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'active' => $this->active,
            'active_label' => $this->active ? 'Ativa' : 'Inativa',
            'products_count' => $this->whenCounted('products'),
            'active_products_count' => $this->whenCounted('activeProducts'),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at->format('d/m/Y H:i')
        ];
    }
}
