<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'price' => $this->price,
            'price_formatted' => 'R$ ' . number_format($this->price, 2, ',', '.'),
            'cost_price' => $this->cost_price,
            'cost_price_formatted' => $this->when($this->cost_price, fn() => 'R$ ' . number_format($this->cost_price, 2, ',', '.')),
            'profit_margin' => $this->when($this->cost_price && $this->cost_price > 0, function () {
                return round((($this->price - $this->cost_price) / $this->cost_price) * 100, 2);
            }),
            'stock_quantity' => $this->stock_quantity,
            'min_stock' => $this->min_stock,
            'stock_status' => $this->getStockStatus(),
            'stock_status_label' => $this->getStockStatusLabel(),
            'unit' => $this->unit,
            'brand' => $this->brand,
            'supplier' => $this->supplier,
            'location' => $this->location,
            'weight' => $this->weight,
            'weight_formatted' => $this->when($this->weight, fn() => number_format($this->weight, 2, ',', '.') . ' kg'),
            'dimensions' => $this->dimensions,
            'warranty_months' => $this->warranty_months,
            'warranty_label' => $this->when($this->warranty_months, function () {
                return $this->warranty_months === 1 ? '1 mês' : "{$this->warranty_months} meses";
            }),
            'active' => $this->active,
            'active_label' => $this->active ? 'Ativo' : 'Inativo',
            'featured' => $this->featured,
            'observations' => $this->observations,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'category_id' => $this->category_id,
            'usage_count' => $this->whenCounted('serviceItems'),
            'total_sold' => $this->when($this->relationLoaded('serviceItems'), function () {
                return $this->serviceItems->sum('quantity');
            }),
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at->format('d/m/Y H:i')
        ];
    }

    private function getStockStatus(): string
    {
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->min_stock && $this->stock_quantity <= $this->min_stock) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    private function getStockStatusLabel(): string
    {
        return match($this->getStockStatus()) {
            'out_of_stock' => 'Sem Estoque',
            'low_stock' => 'Estoque Baixo',
            'in_stock' => 'Em Estoque',
            default => 'Indefinido'
        };
    }
}
