<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TelegramWebhookResource;
use App\Services\TelegramLoggingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramStatsController extends Controller
{
    public function __construct(
        private TelegramLoggingService $loggingService
    ) {}

    /**
     * Get webhook statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->loggingService->getWebhookStats();

            return TelegramWebhookResource::success('Statistics retrieved successfully', [
                'statistics' => $stats
            ])
            ->response()
            ->setStatusCode(200);

        } catch (\Exception $e) {
            return TelegramWebhookResource::error('Failed to retrieve statistics: ' . $e->getMessage())
                ->response()
                ->setStatusCode(500);
        }
    }

    /**
     * Get recent webhook logs
     */
    public function getRecentLogs(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 50);
            $limit = min(max((int) $limit, 1), 100); // Limit between 1 and 100

            $logs = $this->loggingService->getRecentLogs($limit);

            return TelegramWebhookResource::success('Recent logs retrieved successfully', [
                'logs' => $logs,
                'count' => count($logs),
                'limit' => $limit
            ])
            ->response()
            ->setStatusCode(200);

        } catch (\Exception $e) {
            return TelegramWebhookResource::error('Failed to retrieve logs: ' . $e->getMessage())
                ->response()
                ->setStatusCode(500);
        }
    }

    /**
     * Get webhook health status
     */
    public function getHealthStatus(): JsonResponse
    {
        try {
            $stats = $this->loggingService->getWebhookStats();

            // Calculate health metrics
            $totalRequests = $stats['total_requests'] ?? 0;
            $successfulRequests = $stats['successful_requests'] ?? 0;
            $failedRequests = $stats['failed_requests'] ?? 0;

            $successRate = $totalRequests > 0 ? ($successfulRequests / $totalRequests) * 100 : 0;
            $errorRate = $totalRequests > 0 ? ($failedRequests / $totalRequests) * 100 : 0;

            $healthStatus = [
                'status' => $successRate >= 90 ? 'healthy' : ($successRate >= 70 ? 'warning' : 'critical'),
                'success_rate' => round($successRate, 2),
                'error_rate' => round($errorRate, 2),
                'total_requests' => $totalRequests,
                'successful_requests' => $successfulRequests,
                'failed_requests' => $failedRequests,
                'ignored_requests' => $stats['ignored_requests'] ?? 0,
                'message_requests' => $stats['message_requests'] ?? 0,
                'callback_requests' => $stats['callback_requests'] ?? 0,
                'last_updated' => now()->toISOString(),
            ];

            return TelegramWebhookResource::success('Health status retrieved successfully', [
                'health' => $healthStatus
            ])
            ->response()
            ->setStatusCode(200);

        } catch (\Exception $e) {
            return TelegramWebhookResource::error('Failed to retrieve health status: ' . $e->getMessage())
                ->response()
                ->setStatusCode(500);
        }
    }
}
