<?php

namespace App\Contracts\Telegram\Commands;

class CommandMatch
{
    public function __construct(
        private CommandInterface $command,
        private float $confidence,
        private string $matchedInput,
        private array $context = []
    ) {}

    public function getCommand(): CommandInterface
    {
        return $this->command;
    }

    public function getConfidence(): float
    {
        return $this->confidence;
    }

    public function getMatchedInput(): string
    {
        return $this->matchedInput;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function isHighConfidence(): bool
    {
        return $this->confidence >= 0.8;
    }

    public function isMediumConfidence(): bool
    {
        return $this->confidence >= 0.6 && $this->confidence < 0.8;
    }

    public function isLowConfidence(): bool
    {
        return $this->confidence < 0.6;
    }
}
