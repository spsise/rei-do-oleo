<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Contracts\Telegram\Commands\CommandInterface;

class TelegramCommand extends Model implements CommandInterface
{
    use HasFactory;

    protected $fillable = [
        'command_id',
        'aliases',
        'description',
        'action_handler',
        'action_method',
        'action_parameters',
        'permissions',
        'category',
        'voice_settings',
        'natural_language',
        'fallback',
        'is_active',
        'priority'
    ];

    protected $casts = [
        'aliases' => 'array',
        'action_parameters' => 'array',
        'permissions' => 'array',
        'voice_settings' => 'array',
        'natural_language' => 'array',
        'fallback' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer'
    ];

    public function getId(): string
    {
        return $this->command_id;
    }

    public function getAliases(): array
    {
        return $this->aliases ?? [];
    }

    public function getDescription(): string
    {
        return $this->description ?? '';
    }

    public function getAction(): array
    {
        return [
            'handler' => $this->action_handler,
            'method' => $this->action_method,
            'parameters' => $this->action_parameters ?? []
        ];
    }

    public function getPermissions(): array
    {
        return $this->permissions ?? ['all'];
    }

    public function getCategory(): string
    {
        return $this->category ?? 'general';
    }

    public function getVoiceSettings(): array
    {
        return $this->voice_settings ?? [
            'enabled' => false,
            'priority' => 1,
            'noise_reduction' => false,
            'language' => ['pt']
        ];
    }

    public function getNaturalLanguage(): array
    {
        return $this->natural_language ?? [];
    }

    public function getFallback(): array
    {
        return $this->fallback ?? [
            'message' => 'Desculpe, não entendi esse comando.',
            'suggestions' => []
        ];
    }

    public function canHandle(string $input): bool
    {
        $input = strtolower(trim($input));

        // Check exact aliases
        foreach ($this->getAliases() as $alias) {
            if (strtolower($alias) === $input) {
                return true;
            }
        }

        // Check natural language patterns
        foreach ($this->getNaturalLanguage() as $pattern) {
            if (str_contains(strtolower($pattern), $input) ||
                str_contains($input, strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }

    public function getConfidence(string $input): float
    {
        $input = strtolower(trim($input));
        $maxConfidence = 0.0;

        // Exact match - highest confidence
        foreach ($this->getAliases() as $alias) {
            if (strtolower($alias) === $input) {
                return 1.0;
            }
        }

        // Natural language match - calculate similarity
        foreach ($this->getNaturalLanguage() as $pattern) {
            $similarity = $this->calculateSimilarity($input, strtolower($pattern));
            $maxConfidence = max($maxConfidence, $similarity);
        }

        return $maxConfidence;
    }

    private function calculateSimilarity(string $input, string $pattern): float
    {
        // Simple similarity calculation using Levenshtein distance
        $levenshtein = levenshtein($input, $pattern);
        $maxLength = max(strlen($input), strlen($pattern));

        if ($maxLength === 0) return 1.0;

        return 1 - ($levenshtein / $maxLength);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPermission($query, array $userPermissions)
    {
        return $query->where(function ($q) use ($userPermissions) {
            $q->whereJsonContains('permissions', 'all')
              ->orWhereJsonContains('permissions', $userPermissions);
        });
    }
}
