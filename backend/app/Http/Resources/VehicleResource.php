<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'license_plate' => $this->license_plate,
            'license_plate_formatted' => $this->getFormattedLicensePlate(),
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'fuel_type' => $this->fuel_type,
            'fuel_type_label' => $this->getFuelTypeLabel(),
            'engine' => $this->engine,
            'chassis' => $this->chassis,
            'renavam' => $this->renavam,
            'mileage' => $this->mileage,
            'mileage_formatted' => $this->when($this->mileage, fn() => number_format($this->mileage, 0, ',', '.') . ' km'),
            'observations' => $this->observations,
            'full_description' => $this->getFullDescription(),
            'client' => new ClientResource($this->whenLoaded('client')),
            'client_id' => $this->client_id,
            'services_count' => $this->whenCounted('services'),
            'last_service' => $this->when($this->relationLoaded('services'), function () {
                $lastService = $this->services->first();
                return $lastService ? [
                    'id' => $lastService->id,
                    'service_number' => $lastService->service_number,
                    'date' => $lastService->created_at->format('d/m/Y'),
                    'status' => $lastService->status->name ?? null,
                    'description' => $lastService->description,
                    'mileage' => $lastService->mileage
                ] : null;
            }),
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at->format('d/m/Y H:i')
        ];
    }

    private function getFormattedLicensePlate(): string
    {
        $plate = strtoupper($this->license_plate);

        // Mercosul format: ABC1D23
        if (preg_match('/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/', $plate)) {
            return $plate;
        }

        // Old format: ABC-1234 or ABC1234
        if (preg_match('/^[A-Z]{3}[0-9]{4}$/', $plate)) {
            return substr($plate, 0, 3) . '-' . substr($plate, 3);
        }

        return $plate;
    }

    private function getFuelTypeLabel(): ?string
    {
        return match($this->fuel_type) {
            'gasoline' => 'Gasolina',
            'ethanol' => 'Etanol',
            'diesel' => 'Diesel',
            'flex' => 'Flex',
            'electric' => 'Elétrico',
            'hybrid' => 'Híbrido',
            default => $this->fuel_type
        };
    }

    private function getFullDescription(): string
    {
        $parts = array_filter([
            $this->brand,
            $this->model,
            $this->year,
            $this->color
        ]);

        return implode(' ', $parts);
    }
}
