<?php

namespace App\Services\Telegram\Commands;

use App\Contracts\Telegram\Commands\CommandInterface;
use App\Contracts\Telegram\Commands\CommandMatch;
use App\Services\Telegram\Commands\Repositories\CommandConfigRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CommandRegistry implements \App\Contracts\Telegram\Commands\CommandRegistryInterface
{
    private Collection $commands;
    private array $aliases = [];
    private array $naturalLanguage = [];
    private array $voiceCommands = [];

    public function __construct(
        private CommandConfigRepository $configRepo
    ) {
        $this->loadCommands();
    }

    public function findCommand(string $input, array $context = []): ?CommandMatch
    {
        $input = $this->normalizeText($input);

        // 1. Check exact aliases first (highest priority)
        if (isset($this->aliases[$input])) {
            $command = $this->aliases[$input];
            return $this->createMatch($command, 1.0, $input, $context);
        }

        // 2. Check natural language patterns
        $bestMatch = $this->findByNaturalLanguage($input, $context);
        if ($bestMatch && $bestMatch->getConfidence() > 0.8) { // Relaxed from 0.85 to 0.8
            return $bestMatch;
        }

        // 3. Check voice commands (if context indicates voice input)
        if (isset($context['type']) && $context['type'] === 'voice') {
            $voiceMatch = $this->findByVoiceSimilarity($input, $context);
            if ($voiceMatch && $voiceMatch->getConfidence() > 0.8) { // Aumentado de 0.7 para 0.8
                return $voiceMatch;
            }
        }

        // 4. Fuzzy matching for low confidence cases
        $fuzzyMatch = $this->findByFuzzyMatch($input, $context);
        if ($fuzzyMatch && $fuzzyMatch->getConfidence() > 0.75) { // Aumentado de 0.6 para 0.75
            return $fuzzyMatch;
        }

        return null;
    }

    public function getAllCommands(): array
    {
        return $this->commands->all();
    }

    public function getCommandsByCategory(string $category): array
    {
        return $this->commands
            ->filter(fn($command) => $command->getCategory() === $category)
            ->all();
    }

    public function reloadCommands(): void
    {
        $this->loadCommands();
    }

    public function addCommand(array $commandConfig): void
    {
        $this->configRepo->addCommand($commandConfig);
        $this->loadCommands();
    }

    public function removeCommand(string $commandId): void
    {
        $this->configRepo->removeCommand($commandId);
        $this->loadCommands();
    }

    private function loadCommands(): void
    {
        try {
            $this->commands = $this->configRepo->getAllCommands();
            $this->buildIndexes();

            Log::info('Command registry loaded successfully', [
                'total_commands' => $this->commands->count(),
                'categories' => $this->commands->pluck('category')->unique()->values()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load commands', [
                'error' => $e->getMessage()
            ]);

            $this->commands = collect([]);
            $this->aliases = [];
            $this->naturalLanguage = [];
            $this->voiceCommands = [];
        }
    }

    private function buildIndexes(): void
    {
        $this->aliases = [];
        $this->naturalLanguage = [];
        $this->voiceCommands = [];

        foreach ($this->commands as $command) {
            // Build aliases index
            foreach ($command->getAliases() as $alias) {
                $normalized = $this->normalizeText($alias);
                $this->aliases[$normalized] = $command;

                // Simple singular/plural handling: also index without trailing 's'
                if (str_ends_with($normalized, 's')) {
                    $singular = rtrim($normalized, 's');
                    $this->aliases[$singular] = $command;
                }
            }

            // Build natural language index
            foreach ($command->getNaturalLanguage() as $pattern) {
                $normalizedPattern = $this->normalizeText($pattern);
                $this->naturalLanguage[$normalizedPattern] = $command;

                // Also index simplified singular without trailing 's'
                if (str_ends_with($normalizedPattern, 's')) {
                    $singular = rtrim($normalizedPattern, 's');
                    $this->naturalLanguage[$singular] = $command;
                }
            }

            // Build voice commands index
            $voiceSettings = $command->getVoiceSettings();
            if (isset($voiceSettings['enabled']) && $voiceSettings['enabled']) {
                $this->voiceCommands[$command->getId()] = $command;
            }
        }
    }

    private function findByNaturalLanguage(string $input, array $context): ?CommandMatch
    {
        $bestMatch = null;
        $highestScore = 0.0;

        foreach ($this->naturalLanguage as $pattern => $command) {
            $score = $this->calculateSimilarity($input, $pattern);

            if ($score > $highestScore && $score > 0.8) { // Relaxed from 0.85 to 0.8
                $highestScore = $score;
                $bestMatch = $command;
            }
        }

        if ($bestMatch) {
            return $this->createMatch($bestMatch, $highestScore, $input, $context);
        }

        return null;
    }

    private function findByVoiceSimilarity(string $input, array $context): ?CommandMatch
    {
        $bestMatch = null;
        $highestScore = 0.0;

        foreach ($this->voiceCommands as $command) {
            $score = $this->calculateVoiceSimilarity($input, $command, $context);

            if ($score > $highestScore && $score > 0.6) {
                $highestScore = $score;
                $bestMatch = $command;
            }
        }

        if ($bestMatch) {
            return $this->createMatch($bestMatch, $highestScore, $input, $context);
        }

        return null;
    }

    private function findByFuzzyMatch(string $input, array $context): ?CommandMatch
    {
        $bestMatch = null;
        $highestScore = 0.0;

        // Check all commands for fuzzy matching
        foreach ($this->commands as $command) {
            $score = $this->calculateFuzzyScore($input, $command);

            if ($score > $highestScore && $score > 0.5) {
                $highestScore = $score;
                $bestMatch = $command;
            }
        }

        if ($bestMatch) {
            return $this->createMatch($bestMatch, $highestScore, $input, $context);
        }

        return null;
    }

    private function calculateSimilarity(string $input, string $pattern): float
    {
        // Normalize to be accent-insensitive
        $input = $this->normalizeText($input);
        $pattern = $this->normalizeText($pattern);

        // Levenshtein distance for similarity
        $levenshtein = levenshtein($input, $pattern);
        $maxLength = max(strlen($input), strlen($pattern));

        if ($maxLength === 0) return 1.0;

        $levenshteinScore = 1 - ($levenshtein / $maxLength);

        // Jaro-Winkler for better precision
        $jaroScore = $this->jaroWinkler($input, $pattern);

        // Weighted combination
        return ($levenshteinScore * 0.4) + ($jaroScore * 0.6);
    }

    private function calculateVoiceSimilarity(string $input, CommandInterface $command, array $context): float
    {
        $maxScore = 0.0;

        // Check aliases with voice-specific adjustments
        foreach ($command->getAliases() as $alias) {
            $baseScore = $this->calculateSimilarity($input, $alias);

            // Apply voice-specific adjustments
            $voiceSettings = $command->getVoiceSettings();
            if (isset($voiceSettings['noise_reduction']) && $voiceSettings['noise_reduction']) {
                $baseScore *= 1.1; // Boost score for noise-reduced commands
            }

            $maxScore = max($maxScore, $baseScore);
        }

        // Check natural language patterns
        foreach ($command->getNaturalLanguage() as $pattern) {
            $score = $this->calculateSimilarity($input, $pattern);
            $maxScore = max($maxScore, $score);
        }

        return $maxScore;
    }

    private function calculateFuzzyScore(string $input, CommandInterface $command): float
    {
        $maxScore = 0.0;

        // Check command ID
        $score = $this->calculateSimilarity($input, $command->getId());
        $maxScore = max($maxScore, $score);

        // Check aliases
        foreach ($command->getAliases() as $alias) {
            $score = $this->calculateSimilarity($input, $alias);
            $maxScore = max($maxScore, $score);
        }

        // Check description
        $score = $this->calculateSimilarity($input, $command->getDescription());
        $maxScore = max($maxScore, $score * 0.8); // Lower weight for description

        return $maxScore;
    }

    /**
     * Normalize text: lowercase, trim, remove diacritics
     */
    private function normalizeText(string $text): string
    {
        $text = trim(strtolower($text));
        // Remove diacritics (accents)
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        if ($normalized !== false) {
            $text = $normalized;
        }
        // Remove any remaining non-spacing marks
        $text = preg_replace('/[^\p{L}\p{Nd}\s]/u', '', $text) ?? $text;
        return $text;
    }

    private function jaroWinkler(string $str1, string $str2): float
    {
        $str1 = strtolower($str1);
        $str2 = strtolower($str2);

        if ($str1 === $str2) {
            return 1.0;
        }

        $len1 = strlen($str1);
        $len2 = strlen($str2);

        if ($len1 === 0 || $len2 === 0) {
            return 0.0;
        }

        $matchDistance = (int) (max($len1, $len2) / 2) - 1;

        if ($matchDistance < 0) {
            $matchDistance = 0;
        }

        $str1Matches = array_fill(0, $len1, false);
        $str2Matches = array_fill(0, $len2, false);

        $matches = 0;
        $transpositions = 0;

        for ($i = 0; $i < $len1; $i++) {
            $start = max(0, $i - $matchDistance);
            $end = min($i + $matchDistance + 1, $len2);

            for ($j = $start; $j < $end; $j++) {
                if ($str2Matches[$j] || $str1[$i] !== $str2[$j]) {
                    continue;
                }

                $str1Matches[$i] = true;
                $str2Matches[$j] = true;
                $matches++;
                break;
            }
        }

        if ($matches === 0) {
            return 0.0;
        }

        $k = 0;
        for ($i = 0; $i < $len1; $i++) {
            if (!$str1Matches[$i]) {
                continue;
            }

            while (!$str2Matches[$k]) {
                $k++;
            }

            if ($str1[$i] !== $str2[$k]) {
                $transpositions++;
            }
            $k++;
        }

        $transpositions /= 2;

        $jaro = (($matches / $len1) + ($matches / $len2) + (($matches - $transpositions) / $matches)) / 3;

        // Jaro-Winkler modification
        $prefix = 0;
        $maxPrefix = min(4, min($len1, $len2));

        for ($i = 0; $i < $maxPrefix; $i++) {
            if ($str1[$i] === $str2[$i]) {
                $prefix++;
            } else {
                break;
            }
        }

        $jaroWinkler = $jaro + ($prefix * 0.1 * (1 - $jaro));

        return $jaroWinkler;
    }

    private function createMatch(CommandInterface $command, float $confidence, string $input, array $context): CommandMatch
    {
        return new \App\Contracts\Telegram\Commands\CommandMatch(
            $command,
            $confidence,
            $input,
            $context
        );
    }

    public function getCommandsByPermission(array $userPermissions): array
    {
        return $this->commands
            ->filter(function ($command) use ($userPermissions) {
                $commandPermissions = $command->getPermissions();

                // Check if command allows all users
                if (in_array('all', $commandPermissions)) {
                    return true;
                }

                // Check if userPermissions is an array
                if (!is_array($userPermissions)) {
                    return false;
                }

                // Check if user has any of the required permissions
                return !empty(array_intersect($userPermissions, $commandPermissions));
            })
            ->all();
    }

    public function searchCommands(string $query): array
    {
        $query = strtolower(trim($query));

        return $this->commands
            ->filter(function ($command) use ($query) {
                // Search in command ID
                if (str_contains(strtolower($command->getId()), $query)) {
                    return true;
                }

                // Search in aliases
                foreach ($command->getAliases() as $alias) {
                    if (str_contains(strtolower($alias), $query)) {
                        return true;
                    }
                }

                // Search in description
                if (str_contains(strtolower($command->getDescription()), $query)) {
                    return true;
                }

                // Search in natural language patterns
                foreach ($command->getNaturalLanguage() as $pattern) {
                    if (str_contains(strtolower($pattern), $query)) {
                        return true;
                    }
                }

                return false;
            })
            ->all();
    }
}
