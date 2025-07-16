<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\DocumentMaskHelper;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->getType(),
            'type_label' => $this->getTypeLabel(),
            'document' => DocumentMaskHelper::mask($this->getDocument()),
            'document_formatted' => $this->getFormattedDocument(),
            'phone' => $this->phone01,
            'phone_formatted' => $this->getFormattedPhone(),
            'phone02' => $this->phone02,
            'phone02_formatted' => $this->when($this->phone02, fn() => $this->formatPhone($this->phone02)),
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'zip_code_formatted' => $this->when($this->zip_code, fn() => $this->formatZipCode($this->zip_code)),
            'full_address' => $this->getFullAddress(),
            'notes' => $this->notes,
            'active' => $this->active,
            'active_label' => $this->active ? 'Ativo' : 'Inativo',
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

    private function getType(): string
    {
        return $this->cpf ? 'pessoa_fisica' : 'pessoa_juridica';
    }

    private function getTypeLabel(): string
    {
        return $this->cpf ? 'Pessoa Física' : 'Pessoa Jurídica';
    }

    private function getDocument(): ?string
    {
        return $this->cpf ?? $this->cnpj;
    }

    private function getFormattedDocument(): ?string
    {
        $document = $this->getDocument();
        if (!$document) return null;

        $clean = preg_replace('/\D/', '', $document);

        if (strlen($clean) === 11) {
            // CPF: 000.000.000-00
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $clean);
        } elseif (strlen($clean) === 14) {
            // CNPJ: 00.000.000/0000-00
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $clean);
        }

        return $document;
    }

    private function getFormattedPhone(): ?string
    {
        return $this->formatPhone($this->phone01);
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
            $this->address,
            $this->city,
            $this->state
        ]);

        return !empty($parts) ? implode(', ', $parts) : null;
    }
}
