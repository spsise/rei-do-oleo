<?php

namespace App\Helpers;

class DocumentMaskHelper
{
    /**
     * Aplica máscara de compliance ao documento (CPF/CNPJ)
     * Exemplo CPF: 123.456.789-00 => 123.[asteriscos].[asteriscos]-00
     * Exemplo CNPJ: 12.345.678/0001-99 => 12.[asteriscos].[asteriscos]/[asteriscos]-99
     */
    public static function mask(?string $document): ?string
    {
        if (!$document) return null;
        $clean = preg_replace('/\D/', '', $document);
        if (strlen($clean) === 11) {
            // CPF
            return preg_replace('/(\d{3})\d{3}\d{3}(\d{2})/', '$1.***.***-$2', $clean);
        } elseif (strlen($clean) === 14) {
            // CNPJ
            return preg_replace('/(\d{2})\d{3}\d{3}\d{4}(\d{2})/', '$1.***.***/****-$2', $clean);
        }
        return $document;
    }
}
