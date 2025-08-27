<?php

namespace App\Contracts;

interface MessageFlowTrackerInterface
{
    // Core tracking methods
    public function trackMethod(string $className, string $methodName, array $inputData = [], array $outputData = []): void;
    public function endMethod(string $className, string $methodName, array $outputData = []): void;

    // Report generation
    public function generateFlowReport(array $payload): string;
    public function generateUserReport(array $payload): string;

    // Pipeline control
    public function startTracking(string $messageId): void;
    public function endTracking(): void;
    public function clearTrace(): void;

    // Data retrieval
    public function getExecutedMethods(): array;
    public function getExpectedMethods(): array;
    public function getMethodData(): array;

    // Utility methods
    public function identifyMessageType(array $payload): string;
    public function saveTrace(): void;
    public function isEnabled(): bool;
}
