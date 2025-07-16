<?php

namespace App\Support\Helpers;

use Illuminate\Support\Facades\Auth;

class SecurityMaskHelper
{
    /**
     * Apply mask to email address
     * Example: john.doe@example.com -> j***.d**@e******.com
     */
    public static function maskEmail(string $email): string
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '';
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '';
        }

        $localPart = $parts[0];
        $domain = $parts[1];

        // Mask local part
        if (strpos($localPart, '.') !== false) {
            $localParts = explode('.', $localPart);
            $maskedLocalParts = [];
            foreach ($localParts as $part) {
                if (strlen($part) <= 2) {
                    $maskedLocalParts[] = $part;
                } else {
                    $maskedLocalParts[] = substr($part, 0, 1) . str_repeat('*', strlen($part) - 1);
                }
            }
            $maskedLocal = implode('.', $maskedLocalParts);
        } else {
            if (strlen($localPart) <= 2) {
                $maskedLocal = $localPart;
            } else {
                $maskedLocal = substr($localPart, 0, 1) . str_repeat('*', strlen($localPart) - 1);
            }
        }

        // Mask domain
        $domainParts = explode('.', $domain);
        if (count($domainParts) >= 2) {
            $mainDomain = $domainParts[0];
            $extension = end($domainParts);
            $maskedMainDomain = substr($mainDomain, 0, 1) . str_repeat('*', strlen($mainDomain) - 1);
            $maskedDomain = $maskedMainDomain . '.' . $extension;
        } else {
            $maskedDomain = $domain;
        }

        return $maskedLocal . '@' . $maskedDomain;
    }

    /**
     * Apply mask to phone number
     * Example: (11) 99999-9999 -> (11) 9****-9999
     */
    public static function maskPhone(string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Remove all non-numeric characters
        $numbers = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($numbers) < 10) {
            return $phone; // Return original if too short
        }

        // Brazilian phone format
        if (strlen($numbers) === 11) {
            // Mobile: (11) 99999-9999 -> (11) 9****-9999
            $ddd = substr($numbers, 0, 2);
            $prefix = substr($numbers, 2, 1);
            $middle = str_repeat('*', 4);
            $suffix = substr($numbers, 7, 4);

            return "({$ddd}) {$prefix}{$middle}-{$suffix}";
        } elseif (strlen($numbers) === 10) {
            // Landline: (11) 3333-3333 -> (11) 3***-3333
            $ddd = substr($numbers, 0, 2);
            $prefix = substr($numbers, 2, 1);
            $middle = str_repeat('*', 3);
            $suffix = substr($numbers, 6, 4);

            return "({$ddd}) {$prefix}{$middle}-{$suffix}";
        }

        return $phone; // Return original if format not recognized
    }

    /**
     * Apply mask to document (CPF or CNPJ)
     * CPF: 123.456.789-01 -> 123.[*].[*]-01
     * CNPJ: 12.345.678/0001-90 -> 12.[*].[*]/[*]-90
     */
    public static function maskDocument(string $document): string
    {
        if (empty($document)) {
            return '';
        }

        // Remove all non-numeric characters
        $numbers = preg_replace('/[^0-9]/', '', $document);

        if (strlen($numbers) === 11) {
            // CPF: 123.456.789-01 -> 123.***.***-01
            $first = substr($numbers, 0, 3);
            $middle1 = '***';
            $middle2 = '***';
            $last = substr($numbers, 9, 2);
            return "$first.$middle1.$middle2-$last";
        } elseif (strlen($numbers) === 14) {
            // CNPJ: 12.345.678/0001-90 -> 12.***.***/****-90
            $first = substr($numbers, 0, 2);
            $middle1 = '***';
            $middle2 = '***';
            $middle3 = '****';
            $last = substr($numbers, 12, 2);
            return "$first.$middle1.$middle2/$middle3-$last";
        }

        return $document; // Return original if format not recognized
    }

    /**
     * Apply conditional mask based on user permissions
     */
    public static function conditionalMask(string $value, string $type): string
    {
        return match ($type) {
            'email' => self::maskEmail($value),
            'phone' => self::maskPhone($value),
            'document' => self::maskDocument($value),
            default => $value,
        };
    }

    /**
     * Check if current user can see full sensitive data
     */
    public static function canSeeFullData(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        // For now, return true for authenticated users
        // This can be enhanced later with proper role checking
        return true;
    }
}
