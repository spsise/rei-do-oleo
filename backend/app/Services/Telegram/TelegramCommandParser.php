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

        // Enhanced voice command patterns
        if (preg_match('/(enviar|quero|preciso|mostre|mostra).*(relatório|report)/i', $text)) {
            return [
                'type' => 'report',
                'params' => $this->extractPeriodFromText($text)
            ];
        }

        if (preg_match('/(como|está|status).*(sistema|serviços|tudo)/i', $text)) {
            return [
                'type' => 'status',
                'params' => []
            ];
        }

        if (preg_match('/(menu|ajuda|help|comandos|opções)/i', $text)) {
            return [
                'type' => 'start',
                'params' => []
            ];
        }

        if (preg_match('/(serviços|services).*(hoje|semana|mês|month)/i', $text)) {
            return [
                'type' => 'services',
                'params' => $this->extractPeriodFromText($text)
            ];
        }

        if (preg_match('/(produtos|products).*(hoje|semana|mês|month)/i', $text)) {
            return [
                'type' => 'products',
                'params' => $this->extractPeriodFromText($text)
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

    /**
     * Parse command from voice-converted text
     */
    public function parseVoiceCommand(string $text): array
    {
        $text = trim(strtolower($text));

        // Remove common voice recognition artifacts
        $text = $this->cleanVoiceText($text);

        // Parse as regular command
        return $this->parseCommand($text);
    }

    /**
     * Clean voice recognition text
     */
    private function cleanVoiceText(string $text): string
    {
        // Remove common voice recognition artifacts
        $replacements = [
            'ponto' => '.',
            'vírgula' => ',',
            'interrogação' => '?',
            'exclamação' => '!',
            'dois pontos' => ':',
            'ponto e vírgula' => ';',
            'aspas' => '"',
            'parênteses' => '()',
            'colchetes' => '[]',
            'chaves' => '{}',
            'hífen' => '-',
            'underscore' => '_',
            'arroba' => '@',
            'cerquilha' => '#',
            'porcentagem' => '%',
            'cifrão' => '$',
            'e comercial' => '&',
            'asterisco' => '*',
            'mais' => '+',
            'igual' => '=',
            'barra' => '/',
            'contra barra' => '\\',
            'pipe' => '|',
            'til' => '~',
            'acento' => '^',
            'menor que' => '<',
            'maior que' => '>',
            'espaço' => ' ',
            'nova linha' => "\n",
            'enter' => "\n",
            'tab' => "\t",
            'tabulação' => "\t"
        ];

        foreach ($replacements as $voice => $symbol) {
            $text = str_replace($voice, $symbol, $text);
        }

        // Remove extra spaces
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Extract intent from voice command
     */
    private function extractVoiceIntent(string $text): array
    {
        $intent = [
            'confidence' => 0.0,
            'command' => 'unknown',
            'params' => []
        ];

        // Simple confidence scoring based on keyword matches
        $keywords = [
            'relatório' => ['relatório', 'report', 'enviar', 'quero', 'preciso'],
            'serviços' => ['serviços', 'services', 'status'],
            'produtos' => ['produtos', 'products', 'estoque'],
            'status' => ['status', 'como', 'está', 'sistema'],
            'menu' => ['menu', 'ajuda', 'help', 'comandos']
        ];

        $textLower = strtolower($text);
        $maxScore = 0;
        $bestMatch = 'unknown';

        foreach ($keywords as $command => $commandKeywords) {
            $score = 0;
            foreach ($commandKeywords as $keyword) {
                if (str_contains($textLower, $keyword)) {
                    $score += 1;
                }
            }

            if ($score > $maxScore) {
                $maxScore = $score;
                $bestMatch = $command;
            }
        }

        $intent['confidence'] = $maxScore / max(array_map('count', $keywords));
        $intent['command'] = $bestMatch;
        $intent['params'] = $this->extractPeriodFromText($text);

        return $intent;
    }
}
