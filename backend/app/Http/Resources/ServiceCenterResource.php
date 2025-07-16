<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceCenterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'cnpj' => $this->cnpj,
            'cnpj_formatted' => $this->when($this->cnpj, fn() => $this->formatCnpj($this->cnpj)),
            'state_registration' => $this->state_registration,
            'legal_name' => $this->legal_name,
            'trade_name' => $this->trade_name,
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
            'geolocation' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'coordinates' => $this->when($this->latitude && $this->longitude, function () {
                    return "{$this->latitude},{$this->longitude}";
                }),
                'google_maps_url' => $this->google_maps_url
            ],
            'contact' => [
                'phone' => $this->phone,
                'phone_formatted' => $this->when($this->phone, fn() => $this->formatPhone($this->phone)),
                'whatsapp' => $this->whatsapp,
                'whatsapp_formatted' => $this->when($this->whatsapp, fn() => $this->formatPhone($this->whatsapp)),
                'email' => $this->email,
                'website' => $this->website,
                'facebook_url' => $this->facebook_url,
                'instagram_url' => $this->instagram_url
            ],
            'management' => [
                'manager' => $this->when($this->manager, function () {
                    return [
                        'id' => $this->manager->id,
                        'name' => $this->manager->name,
                        'email' => $this->manager->email,
                        'phone' => $this->manager->phone
                    ];
                }),
                'technical_responsible' => $this->technical_responsible,
                'opening_date' => $this->opening_date?->format('d/m/Y'),
                'operating_hours' => $this->operating_hours
            ],
            'status' => [
                'is_main_branch' => $this->is_main_branch,
                'is_main_branch_label' => $this->is_main_branch ? 'Filial Principal' : 'Filial',
                'active' => $this->active,
                'active_label' => $this->active ? 'Ativo' : 'Inativo'
            ],
            'observations' => $this->observations,
            'users_count' => $this->whenCounted('users'),
            'services_count' => $this->whenCounted('services'),
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at->format('d/m/Y H:i')
        ];
    }

    private function formatCnpj(string $cnpj): string
    {
        $clean = preg_replace('/\D/', '', $cnpj);

        if (strlen($clean) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $clean);
        }

        return $cnpj;
    }

    private function formatPhone(?string $phone): ?string
    {
        if (!$phone) return null;

        $clean = preg_replace('/\D/', '', $phone);

        if (strlen($clean) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $clean);
        } elseif (strlen($clean) === 10) {
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
