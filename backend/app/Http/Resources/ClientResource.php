<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'type_label' => $this->type === 'individual' ? 'Pessoa Física' : 'Pessoa Jurídica',
            'document' => $this->document,
            'document_formatted' => $this->getFormattedDocument(),
            'phone' => $this->phone,
            'phone_formatted' => $this->getFormattedPhone(),
            'whatsapp' => $this->whatsapp,
            'whatsapp_formatted' => $this->when($this->whatsapp, fn() => $this->formatPhone($this->whatsapp)),
            'email' => $this->email,
            'birth_date' => $this->birth_date?->format('d/m/Y'),
            'age' => $this->when($this->birth_date, fn() => $this->birth_date->age),
            'address' => [
                'address_line' => $this->address_line,
                'number' => $this->number,
                'complement' => $this->complement,
                'neighborhood' => $this->neighborhood,
                'city' => $this->city,
                'state' => $this->state,
                'zip_code' => $this->zip_code,
                'zip_code_formatted' => $this->when($this->zip_code, fn() => $this->formatZipCode($this->zip_code)),
                'full_address' => $this->getFullAddress()
            ],
            'active' => $this->active,
            'active_label' => $this->active ? 'Ativo' : 'Inativo',
            'observations' => $this->observations,
            'vehicles_count' => $this->whenCounted('vehicles'),
            'services_count' => $this->whenCounted('services'),
            'last_service_date' => $this->when($this->relationLoaded('services'), function () {
                return $this->services->first()?->created_at?->format('d/m/Y H:i');
            }),
            'vehicles' => VehicleResource::collection($this->whenLoaded('vehicles')),
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at->format('d/m/Y H:i')
        ];
    }

    private function getFormattedDocument(): ?string
    {
        if (!$this->document) return null;

        $clean = preg_replace('/\D/', '', $this->document);

        if (strlen($clean) === 11) {
            // CPF: 000.000.000-00
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $clean);
        } elseif (strlen($clean) === 14) {
            // CNPJ: 00.000.000/0000-00
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $clean);
        }

        return $this->document;
    }

    private function getFormattedPhone(): ?string
    {
        return $this->formatPhone($this->phone);
    }

    private function formatPhone(?string $phone): ?string
    {
        if (!$phone) return null;

        $clean = preg_replace('/\D/', '', $phone);

        if (strlen($clean) === 11) {
            // Celular: (00) 90000-0000
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $clean);
        } elseif (strlen($clean) === 10) {
            // Fixo: (00) 0000-0000
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $clean);
        }

        return $phone;
    }

    private function formatZipCode(string $zipCode): string
    {
        $clean = preg_replace('/\D/', '', $zipCode);

        if (strlen($clean) === 8) {
            return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $clean);
        }

        return $zipCode;
    }

    private function getFullAddress(): ?string
    {
        $parts = array_filter([
            $this->address_line,
            $this->number,
            $this->complement,
            $this->neighborhood,
            $this->city,
            $this->state
        ]);

        return !empty($parts) ? implode(', ', $parts) : null;
    }
}
