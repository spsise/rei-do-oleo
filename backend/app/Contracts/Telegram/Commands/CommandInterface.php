<?php

namespace App\Contracts\Telegram\Commands;

interface CommandInterface
{
    public function getId(): string;
    public function getAliases(): array;
    public function getDescription(): string;
    public function getAction(): array;
    public function getPermissions(): array;
    public function getCategory(): string;
    public function getVoiceSettings(): array;
    public function getNaturalLanguage(): array;
    public function getFallback(): array;
    public function canHandle(string $input): bool;
    public function getConfidence(string $input): float;
}
