<?php

namespace App\Services\Telegram;

class TelegramCommandParser
{
    /**
     * Parse command from message text
     */
    public function parseCommand(string $text): array
    {
        $text = trim(strtolower($text));

        // Remove bot username if present
        $text = preg_replace('/@\w+/', '', $text);

        // Parse command
        if (preg_match('/^\/(\w+)(?:\s+(.+))?$/', $text, $matches)) {
            $command = $matches[1];
            $params = $matches[2] ?? '';

            return [
                'type' => $command,
                'params' => $this->parseParams($params)
            ];
        }

        // Handle natural language
        return $this->parseNaturalLanguage($text);
    }

    /**
     * Parse parameters from command
     */
    private function parseParams(string $params): array
    {
        $params = trim($params);
        $result = [];

        // Parse period
        if (preg_match('/(hoje|today|semana|week|mês|month|mês|month)/', $params, $matches)) {
            $result['period'] = $this->normalizePeriod($matches[1]);
        }

        // Parse date range
        if (preg_match('/(\d{1,2}\/\d{1,2}\/\d{4})/', $params, $matches)) {
            $result['date'] = $matches[1];
        }

        // Parse service center
        if (preg_match('/(centro|center)\s+(\d+)/', $params, $matches)) {
            $result['service_center_id'] = (int) $matches[2];
        }

        return $result;
    }

    /**
     * Parse natural language parameters
     */
    private function parseNaturalLanguage(string $text): array
    {
        // Handle natural language
        if (str_contains($text, 'relatório') || str_contains($text, 'report')) {
            return [
                'type' => 'report',
                'params' => $this->extractPeriodFromText($text)
            ];
        }

        if (str_contains($text, 'serviços') || str_contains($text, 'services')) {
            return [
                'type' => 'services',
                'params' => $this->extractPeriodFromText($text)
            ];
        }

        if (str_contains($text, 'produtos') || str_contains($text, 'products')) {
            return [
                'type' => 'products',
                'params' => $this->extractPeriodFromText($text)
            ];
        }

        if (str_contains($text, 'dashboard') || str_contains($text, 'status')) {
            return [
                'type' => 'dashboard',
                'params' => []
            ];
        }

        if (str_contains($text, 'menu') || str_contains($text, 'ajuda') || str_contains($text, 'help')) {
            return [
                'type' => 'menu',
                'params' => []
            ];
        }

        return [
            'type' => 'unknown',
            'params' => []
        ];
    }

    /**
     * Extract period from text
     */
    private function extractPeriodFromText(string $text): array
    {
        $result = [];

        // Detect period
        if (str_contains($text, 'hoje') || str_contains($text, 'today')) {
            $result['period'] = 'today';
        } elseif (str_contains($text, 'semana') || str_contains($text, 'week')) {
            $result['period'] = 'week';
        } elseif (str_contains($text, 'mês') || str_contains($text, 'month')) {
            $result['period'] = 'month';
        } else {
            $result['period'] = 'today'; // Default
        }

        return $result;
    }

    /**
     * Normalize period parameter
     */
    private function normalizePeriod(string $period): string
    {
        return match(strtolower($period)) {
            'hoje', 'today' => 'today',
            'semana', 'week' => 'week',
            'mês', 'month' => 'month',
            default => 'today'
        };
    }

    /**
     * Parse callback data from inline keyboard
     */
    public function parseCallbackData(string $callbackData): array
    {
        $parts = explode(':', $callbackData);

        return [
            'action' => $parts[0] ?? '',
            'report_type' => $parts[1] ?? null,
            'from' => $parts[1] ?? null
        ];
    }
}
