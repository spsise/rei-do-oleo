<?php

namespace App\Services\Telegram\Commands\Learning;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Contracts\Telegram\Commands\CommandMatch;

class CommandLearning
{
    private const LEARNING_PREFIX = 'telegram_command_learning';
    private const MAX_LEARNING_DATA = 10000;

    public function recordUsage(string $input, CommandMatch $result): void
    {
        try {
            $this->storeUsageData($input, $result);
            $this->updateCommandStats($result->getCommand()->getId());
            $this->updateInputPatterns($input, $result);

        } catch (\Exception $e) {
            Log::warning('Failed to record command usage', [
                'input' => $input,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function recordHit(string $input, CommandMatch $result): void
    {
        try {
            $this->updateHitStats($input, $result);
            $this->reinforcePattern($input, $result);

        } catch (\Exception $e) {
            Log::warning('Failed to record command hit', [
                'input' => $input,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function recordFeedback(string $input, bool $wasSuccessful, ?string $feedback = null): void
    {
        try {
            $this->storeFeedback($input, $wasSuccessful, $feedback);
            $this->adjustConfidence($input, $wasSuccessful);

        } catch (\Exception $e) {
            Log::warning('Failed to record command feedback', [
                'input' => $input,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getLearningStats(): array
    {
        try {
            $stats = Cache::get(self::LEARNING_PREFIX . '_stats', [
                'total_usage' => 0,
                'successful_usage' => 0,
                'failed_usage' => 0,
                'cache_hits' => 0,
                'cache_misses' => 0,
                'top_commands' => [],
                'top_inputs' => [],
                'confidence_trends' => []
            ]);

            return $stats;

        } catch (\Exception $e) {
            Log::warning('Failed to get learning stats', [
                'error' => $e->getMessage()
            ]);

            return [
                'error' => $e->getMessage()
            ];
        }
    }

    public function getCommandInsights(string $commandId): array
    {
        try {
            $insights = Cache::get(self::LEARNING_PREFIX . '_command_' . $commandId, [
                'usage_count' => 0,
                'success_rate' => 0.0,
                'avg_confidence' => 0.0,
                'common_inputs' => [],
                'failure_patterns' => [],
                'improvement_suggestions' => []
            ]);

            return $insights;

        } catch (\Exception $e) {
            Log::warning('Failed to get command insights', [
                'command_id' => $commandId,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => $e->getMessage()
            ];
        }
    }

    public function suggestImprovements(): array
    {
        try {
            $suggestions = [];
            $commandStats = $this->getAllCommandStats();

            foreach ($commandStats as $commandId => $stats) {
                if ($stats['success_rate'] < 0.8) {
                    $suggestions[] = [
                        'command_id' => $commandId,
                        'issue' => 'Low success rate',
                        'current_rate' => $stats['success_rate'],
                        'suggestion' => 'Consider adding more aliases or natural language patterns'
                    ];
                }

                if ($stats['avg_confidence'] < 0.7) {
                    $suggestions[] = [
                        'command_id' => $commandId,
                        'issue' => 'Low confidence',
                        'current_confidence' => $stats['avg_confidence'],
                        'suggestion' => 'Review and improve natural language patterns'
                    ];
                }
            }

            return $suggestions;

        } catch (\Exception $e) {
            Log::warning('Failed to generate improvement suggestions', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    public function trainModel(): void
    {
        try {
            $usageData = $this->getAllUsageData();

            if (empty($usageData)) {
                Log::info('No usage data available for training');
                return;
            }

            // Simple training: update confidence scores based on usage patterns
            foreach ($usageData as $input => $data) {
                $this->updateInputConfidence($input, $data);
            }

            // Generate insights
            $this->generateInsights();

            Log::info('Command learning model trained successfully', [
                'data_points' => count($usageData)
            ]);

        } catch (\Exception $e) {
            Log::warning('Failed to train learning model', [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function storeUsageData(string $input, CommandMatch $result): void
    {
        $usageData = Cache::get(self::LEARNING_PREFIX . '_usage', []);

        $usageData[$input] = [
            'command_id' => $result->getCommand()->getId(),
            'confidence' => $result->getConfidence(),
            'timestamp' => time(),
            'success' => true, // Will be updated later if feedback is provided
            'usage_count' => ($usageData[$input]['usage_count'] ?? 0) + 1
        ];

        // Limit data size
        if (count($usageData) > self::MAX_LEARNING_DATA) {
            $usageData = array_slice($usageData, -self::MAX_LEARNING_DATA, null, true);
        }

        Cache::put(self::LEARNING_PREFIX . '_usage', $usageData, 86400 * 30); // 30 days
    }

    private function updateCommandStats(string $commandId): void
    {
        $commandStats = Cache::get(self::LEARNING_PREFIX . '_command_stats', []);

        if (!isset($commandStats[$commandId])) {
            $commandStats[$commandId] = [
                'usage_count' => 0,
                'success_count' => 0,
                'total_confidence' => 0.0,
                'last_used' => 0
            ];
        }

        $commandStats[$commandId]['usage_count']++;
        $commandStats[$commandId]['last_used'] = time();

        Cache::put(self::LEARNING_PREFIX . '_command_stats', $commandStats, 86400 * 30);
    }

    private function updateInputPatterns(string $input, CommandMatch $result): void
    {
        $patterns = Cache::get(self::LEARNING_PREFIX . '_patterns', []);

        $inputHash = md5($input);
        if (!isset($patterns[$inputHash])) {
            $patterns[$inputHash] = [
                'input' => $input,
                'command_id' => $result->getCommand()->getId(),
                'usage_count' => 0,
                'avg_confidence' => 0.0,
                'last_used' => 0
            ];
        }

        $patterns[$inputHash]['usage_count']++;
        $patterns[$inputHash]['last_used'] = time();

        // Update average confidence
        $currentAvg = $patterns[$inputHash]['avg_confidence'];
        $currentCount = $patterns[$inputHash]['usage_count'];
        $newConfidence = $result->getConfidence();

        $patterns[$inputHash]['avg_confidence'] =
            (($currentAvg * ($currentCount - 1)) + $newConfidence) / $currentCount;

        Cache::put(self::LEARNING_PREFIX . '_patterns', $patterns, 86400 * 30);
    }

    private function updateHitStats(string $input, CommandMatch $result): void
    {
        $hitStats = Cache::get(self::LEARNING_PREFIX . '_hits', []);

        $inputHash = md5($input);
        if (!isset($hitStats[$inputHash])) {
            $hitStats[$inputHash] = [
                'input' => $input,
                'hit_count' => 0,
                'last_hit' => 0
            ];
        }

        $hitStats[$inputHash]['hit_count']++;
        $hitStats[$inputHash]['last_hit'] = time();

        Cache::put(self::LEARNING_PREFIX . '_hits', $hitStats, 86400 * 30);
    }

    private function reinforcePattern(string $input, CommandMatch $result): void
    {
        $patterns = Cache::get(self::LEARNING_PREFIX . '_patterns', []);
        $inputHash = md5($input);

        if (isset($patterns[$inputHash])) {
            // Increase confidence for successful cache hits
            $patterns[$inputHash]['avg_confidence'] = min(1.0,
                $patterns[$inputHash]['avg_confidence'] + 0.01);

            Cache::put(self::LEARNING_PREFIX . '_patterns', $patterns, 86400 * 30);
        }
    }

    private function storeFeedback(string $input, bool $wasSuccessful, ?string $feedback): void
    {
        $feedbackData = Cache::get(self::LEARNING_PREFIX . '_feedback', []);

        $inputHash = md5($input);
        if (!isset($feedbackData[$inputHash])) {
            $feedbackData[$inputHash] = [
                'input' => $input,
                'feedback_count' => 0,
                'success_count' => 0,
                'failure_count' => 0,
                'feedback_history' => []
            ];
        }

        $feedbackData[$inputHash]['feedback_count']++;

        if ($wasSuccessful) {
            $feedbackData[$inputHash]['success_count']++;
        } else {
            $feedbackData[$inputHash]['failure_count']++;
        }

        // Store feedback history
        $feedbackData[$inputHash]['feedback_history'][] = [
            'success' => $wasSuccessful,
            'feedback' => $feedback,
            'timestamp' => time()
        ];

        // Limit feedback history
        if (count($feedbackData[$inputHash]['feedback_history']) > 100) {
            $feedbackData[$inputHash]['feedback_history'] =
                array_slice($feedbackData[$inputHash]['feedback_history'], -100);
        }

        Cache::put(self::LEARNING_PREFIX . '_feedback', $feedbackData, 86400 * 30);
    }

    private function adjustConfidence(string $input, bool $wasSuccessful): void
    {
        $patterns = Cache::get(self::LEARNING_PREFIX . '_patterns', []);
        $inputHash = md5($input);

        if (isset($patterns[$inputHash])) {
            $adjustment = $wasSuccessful ? 0.02 : -0.05;

            $patterns[$inputHash]['avg_confidence'] = max(0.0, min(1.0,
                $patterns[$inputHash]['avg_confidence'] + $adjustment));

            Cache::put(self::LEARNING_PREFIX . '_patterns', $patterns, 86400 * 30);
        }
    }

    private function getAllCommandStats(): array
    {
        return Cache::get(self::LEARNING_PREFIX . '_command_stats', []);
    }

    private function getAllUsageData(): array
    {
        return Cache::get(self::LEARNING_PREFIX . '_usage', []);
    }

    private function updateInputConfidence(string $input, array $data): void
    {
        // This would implement more sophisticated confidence adjustment
        // based on usage patterns and feedback
    }

    private function generateInsights(): void
    {
        // This would generate insights based on collected data
        // and store them for quick access
    }
}
