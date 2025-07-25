<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_number' => $this->service_number,
            'description' => $this->description,
            'complaint' => $this->complaint,
            'diagnosis' => $this->diagnosis,
            'solution' => $this->solution,
            'scheduled_date' => $this->scheduled_date?->format('d/m/Y H:i'),
            'started_at' => $this->started_at?->format('d/m/Y H:i'),
            'finished_at' => $this->finished_at?->format('d/m/Y H:i'),
            'duration' => $this->getDuration(),
            'duration_formatted' => $this->getDurationFormatted(),
            'status' => [
                'id' => $this->status->id ?? null,
                'name' => $this->status->name ?? null,
                'label' => $this->status->label ?? null,
                'color' => $this->status->color ?? null,
            ],
            'priority' => $this->priority,
            'priority_label' => $this->getPriorityLabel(),
            'payment_method' => [
                'id' => $this->paymentMethod->id ?? null,
                'name' => $this->paymentMethod->name ?? null,
                'label' => $this->paymentMethod->label ?? null,
            ],
            'financial' => [
                'labor_cost' => $this->labor_cost,
                'labor_cost_formatted' => $this->when($this->labor_cost, fn() => 'R$ ' . number_format($this->labor_cost, 2, ',', '.')),
                'items_total' => $this->getItemsTotal(),
                'items_total_formatted' => 'R$ ' . number_format($this->getItemsTotal(), 2, ',', '.'),
                'discount' => $this->discount,
                'discount_formatted' => $this->when($this->discount, fn() => 'R$ ' . number_format($this->discount, 2, ',', '.')),
                'total_amount' => $this->total_amount,
                'total_amount_formatted' => $this->when($this->total_amount, fn() => 'R$ ' . number_format($this->total_amount, 2, ',', '.')),
            ],
            'vehicle' => [
                'id' => $this->vehicle->id ?? null,
                'license_plate' => $this->vehicle->license_plate ?? null,
                'brand' => $this->vehicle->brand ?? null,
                'model' => $this->vehicle->model ?? null,
                'year' => $this->vehicle->year ?? null,
                'mileage_at_service' => $this->mileage,
                'mileage_formatted' => $this->when($this->mileage, fn() => number_format($this->mileage, 0, ',', '.') . ' km'),
                'fuel_level' => $this->fuel_level,
                'fuel_level_label' => $this->getFuelLevelLabel(),
            ],
            'client' => [
                'id' => $this->client->id ?? null,
                'name' => $this->client->name ?? null,
                'phone' => $this->client->phone ?? null,
                'document' => $this->client->document ?? null,
            ],
            'service_center' => [
                'id' => $this->serviceCenter->id ?? null,
                'name' => $this->serviceCenter->name ?? null,
                'code' => $this->serviceCenter->code ?? null,
            ],
            'technician' => $this->when($this->technician, function () {
                return [
                    'id' => $this->technician->id,
                    'name' => $this->technician->name,
                    'specialties' => $this->technician->specialties,
                ];
            }),
            'attendant' => $this->when($this->attendant, function () {
                return [
                    'id' => $this->attendant->id,
                    'name' => $this->attendant->name,
                ];
            }),
            'warranty_months' => $this->warranty_months,
            'warranty_expires_at' => $this->when($this->finished_at && $this->warranty_months, function () {
                return $this->finished_at->addMonths($this->warranty_months)->format('d/m/Y');
            }),
            'observations' => $this->observations,
            'internal_notes' => $this->internal_notes,
            'items' => ServiceItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenCounted('items'),
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at->format('d/m/Y H:i')
        ];
    }

    private function getDuration(): ?int
    {
        if (!$this->started_at || !$this->finished_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->finished_at);
    }

    private function getDurationFormatted(): ?string
    {
        $duration = $this->getDuration();

        if (!$duration) {
            return null;
        }

        $hours = intval($duration / 60);
        $minutes = $duration % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }

        return "{$minutes}min";
    }

    private function getPriorityLabel(): ?string
    {
        return match($this->priority) {
            'low' => 'Baixa',
            'normal' => 'Normal',
            'high' => 'Alta',
            'urgent' => 'Urgente',
            default => $this->priority
        };
    }

    private function getFuelLevelLabel(): ?string
    {
        return match($this->fuel_level) {
            'empty' => 'Vazio',
            '1/4' => '1/4',
            '1/2' => '1/2',
            '3/4' => '3/4',
            'full' => 'Cheio',
            default => $this->fuel_level
        };
    }

    private function getItemsTotal(): float
    {
        if (!$this->relationLoaded('items')) {
            return 0;
        }

        return $this->items->sum('total_price');
    }
}
