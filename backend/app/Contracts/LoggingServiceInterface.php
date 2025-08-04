<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface LoggingServiceInterface
{
    /**
     * Log API requests and responses
     */
    public function logApiRequest(Request $request, array $context = []): void;

    /**
     * Log API responses
     */
    public function logApiResponse(int $statusCode, array $response, float $duration, array $context = []): void;

    /**
     * Log business operations
     */
    public function logBusinessOperation(string $operation, array $data, string $status = 'success', array $context = []): void;

    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, array $data, string $level = 'warning', array $context = []): void;

    /**
     * Log performance metrics
     */
    public function logPerformance(string $operation, float $duration, array $metrics = [], array $context = []): void;

    /**
     * Log audit trail
     */
    public function logAudit(string $action, string $model, int $modelId, array $changes = [], array $context = []): void;

    /**
     * Log Telegram bot events
     */
    public function logTelegramEvent(string $event, array $data, string $level = 'info', array $context = []): void;

    /**
     * Log WhatsApp events
     */
    public function logWhatsAppEvent(string $event, array $data, string $level = 'info', array $context = []): void;

    /**
     * Log exceptions with context
     */
    public function logException(\Throwable $exception, array $context = []): void;

    /**
     * Get log statistics
     */
    public function getLogStats(): array;
}
