<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'product' => [
                'id' => $this->product->id ?? null,
                'name' => $this->product->name ?? null,
                'sku' => $this->product->sku ?? null,
                'brand' => $this->product->brand ?? null,
                'category' => $this->product->category->name ?? null,
                'unit' => $this->product->unit ?? null,
                'current_stock' => $this->product->stock_quantity ?? null
            ],
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'unit_price_formatted' => 'R$ ' . number_format($this->unit_price, 2, ',', '.'),
            'discount' => $this->discount ?? 0,
            'discount_formatted' => $this->when($this->discount, fn() => number_format($this->discount, 2, ',', '.') . '%'),
            'discount_amount' => $this->getDiscountAmount(),
            'discount_amount_formatted' => 'R$ ' . number_format($this->getDiscountAmount(), 2, ',', '.'),
            'subtotal' => $this->getSubtotal(),
            'subtotal_formatted' => 'R$ ' . number_format($this->getSubtotal(), 2, ',', '.'),
            'total_price' => $this->total_price,
            'total_price_formatted' => 'R$ ' . number_format($this->total_price, 2, ',', '.'),
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at->format('d/m/Y H:i')
        ];
    }

    private function getSubtotal(): float
    {
        return $this->quantity * $this->unit_price;
    }

    private function getDiscountAmount(): float
    {
        if (!$this->discount) {
            return 0;
        }

        return $this->getSubtotal() * ($this->discount / 100);
    }
}
