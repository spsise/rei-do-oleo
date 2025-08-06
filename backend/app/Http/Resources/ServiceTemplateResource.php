<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'category_label' => $this->category_label,
            'estimated_duration' => $this->estimated_duration,
            'formatted_duration' => $this->formatted_duration,
            'priority' => $this->priority,
            'priority_label' => $this->priority_label,
            'notes' => $this->notes,
            'service_items' => $this->service_items,
            'active' => $this->active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
