<?php

namespace App\Http\Resources;

use App\Support\Helpers\SecurityMaskHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicianSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'client' => [
                'id' => (int) $this['client']->id,
                'name' => $this['client']->name ?? '',
                'email' => SecurityMaskHelper::conditionalMask($this['client']->email ?? '', 'email'),
                'phone' => SecurityMaskHelper::conditionalMask($this['client']->phone01 ?? '', 'phone'),
                'document' => SecurityMaskHelper::conditionalMask($this['client']->cpf ?? $this['client']->cnpj ?? '', 'document'),
            ],
            'vehicles' => $this['vehicles']->map(function ($vehicle) {
                return [
                    'id' => (int) $vehicle->id,
                    'brand' => $vehicle->brand ?? '',
                    'model' => $vehicle->model ?? '',
                    'year' => $vehicle->year ? (int) $vehicle->year : 0,
                    'license_plate' => $vehicle->license_plate ?? '',
                    'color' => $vehicle->color ?? '',
                    'mileage' => $vehicle->mileage ? (int) $vehicle->mileage : 0,
                ];
            }),
            'recent_services' => $this['recent_services']->map(function ($service) {
                return [
                    'id' => (int) $service->id,
                    'service_number' => $service->service_number ?? '',
                    'description' => $service->description ?? '',
                    'status' => $service->serviceStatus?->name ?? 'pending',
                    'total_amount' => $service->total_amount ? (float) $service->total_amount : 0,
                    'created_at' => $service->created_at ? $service->created_at->format('Y-m-d\TH:i:s') : '',
                    'observations' => $service->observations ?? '',
                    'notes' => $service->internal_notes ?? '',
                    'items' => $service->serviceItems->map(function ($item) {
                        return [
                            'id' => (int) $item->id,
                            'product_id' => (int) $item->product_id,
                            'quantity' => (int) $item->quantity,
                            'unit_price' => (float) $item->unit_price,
                            'total_price' => (float) $item->total_price,
                            'notes' => $item->notes ?? '',
                            'product' => $item->product ? [
                                'id' => (int) $item->product->id,
                                'name' => $item->product->name ?? '',
                                'sku' => $item->product->sku ?? '',
                                'price' => (float) $item->product->price ?? 0,
                                'stock_quantity' => (int) $item->product->current_stock ?? 0,
                                'category' => $item->product->category ? [
                                    'id' => (int) $item->product->category->id,
                                    'name' => $item->product->category->name ?? '',
                                ] : null,
                            ] : null,
                        ];
                    }),
                ];
            }),
            'found_by' => $this['found_by'],
        ];
    }
}
