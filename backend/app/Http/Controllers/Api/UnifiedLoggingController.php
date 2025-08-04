<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\LoggingServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class UnifiedLoggingController extends Controller
{
    public function __construct(
        private LoggingServiceInterface $loggingService
    ) {}

    /**
     * Test all logging methods with the new unified system
     */
    public function testAllLogs(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        // Test API Request logging
        $this->loggingService->logApiRequest($request, [
            'test' => true,
            'operation' => 'test_all_logs'
        ]);

        // Test Business Operation logging
        $this->loggingService->logBusinessOperation(
            'test_operation',
            [
                'user_id' => $request->user()?->id,
                'test_data' => 'sample business data'
            ],
            'success',
            ['test' => true]
        );

        // Test Security Event logging
        $this->loggingService->logSecurityEvent(
            'test_security_event',
            [
                'endpoint' => $request->path(),
                'method' => $request->method()
            ],
            'info',
            ['test' => true]
        );

        // Test Performance logging
        $duration = (microtime(true) - $startTime) * 1000;
        $this->loggingService->logPerformance(
            'test_performance',
            $duration,
            ['memory_peak' => memory_get_peak_usage(true)],
            ['test' => true]
        );

        // Test Audit logging
        $this->loggingService->logAudit(
            'test_audit',
            'User',
            $request->user()?->id ?? 0,
            ['test_changes' => 'sample audit data'],
            ['test' => true]
        );

        // Test Telegram Event logging
        $this->loggingService->logTelegramEvent(
            'test_telegram_event',
            [
                'chat_id' => 123456,
                'message_type' => 'text',
                'test_data' => 'sample telegram data'
            ],
            'info',
            ['test' => true]
        );

        // Test WhatsApp Event logging
        $this->loggingService->logWhatsAppEvent(
            'test_whatsapp_event',
            [
                'phone' => '+5511999999999',
                'message_type' => 'text',
                'test_data' => 'sample whatsapp data'
            ],
            'info',
            ['test' => true]
        );

        // Test API Response logging
        $response = [
            'message' => 'All logs tested successfully',
            'timestamp' => now()->toISOString(),
            'test' => true
        ];

        $this->loggingService->logApiResponse(
            200,
            $response,
            $duration,
            ['test' => true]
        );

        return response()->json($response);
    }

    /**
     * Test specific log types based on configuration
     */
    public function testLogTypes(Request $request): JsonResponse
    {
        $logTypes = $request->get('types', []);
        $results = [];

        foreach ($logTypes as $type) {
            switch ($type) {
                case 'api_request':
                    $this->loggingService->logApiRequest($request, ['type' => $type]);
                    $results[$type] = 'logged';
                    break;

                case 'business':
                    $this->loggingService->logBusinessOperation($type, ['data' => 'test'], 'success');
                    $results[$type] = 'logged';
                    break;

                case 'security':
                    $this->loggingService->logSecurityEvent($type, ['data' => 'test'], 'info');
                    $results[$type] = 'logged';
                    break;

                case 'performance':
                    $this->loggingService->logPerformance($type, 100.5, ['test' => true]);
                    $results[$type] = 'logged';
                    break;

                case 'audit':
                    $this->loggingService->logAudit($type, 'TestModel', 1, ['changes' => 'test']);
                    $results[$type] = 'logged';
                    break;

                case 'telegram':
                    $this->loggingService->logTelegramEvent($type, ['chat_id' => 123, 'data' => 'test']);
                    $results[$type] = 'logged';
                    break;

                case 'whatsapp':
                    $this->loggingService->logWhatsAppEvent($type, ['phone' => '+123', 'data' => 'test']);
                    $results[$type] = 'logged';
                    break;

                case 'exception':
                    try {
                        throw new \Exception('Test exception for logging');
                    } catch (\Exception $e) {
                        $this->loggingService->logException($e, ['test' => true]);
                        $results[$type] = 'logged';
                    }
                    break;

                default:
                    $results[$type] = 'unknown type';
            }
        }

        return response()->json([
            'message' => 'Log types tested',
            'results' => $results,
            'config' => [
                'driver' => config('unified-logging.driver', 'activity'),
                'filters' => config('unified-logging.filters', []),
                'performance_thresholds' => config('unified-logging.performance', []),
                'retention' => config('unified-logging.retention', []),
            ]
        ]);
    }

    /**
     * Get log statistics from the unified system
     */
    public function getLogStats(): JsonResponse
    {
        $stats = $this->loggingService->getLogStats();

        return response()->json([
            'message' => 'Log statistics retrieved',
            'stats' => $stats,
            'config' => [
                'driver' => config('unified-logging.driver', 'activity'),
                'filters' => config('unified-logging.filters', []),
                'performance_thresholds' => config('unified-logging.performance', []),
                'retention' => config('unified-logging.retention', []),
            ]
        ]);
    }

    /**
     * Test creating a user with automatic logging
     */
    public function testUserCreation(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8'
        ]);

        // Log the business operation before creating user
        $this->loggingService->logBusinessOperation(
            'user_creation_started',
            [
                'email' => $request->email,
                'name' => $request->name
            ],
            'pending'
        );

        try {
            // Create user (this will trigger automatic Activity Log)
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            // Log successful creation
            $this->loggingService->logBusinessOperation(
                'user_creation_completed',
                [
                    'user_id' => $user->id,
                    'email' => $user->email
                ],
                'success'
            );

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
                'logs_created' => 'User creation logged automatically via Activity Log + manual business operation logs'
            ], 201);

        } catch (\Exception $e) {
            // Log the exception
            $this->loggingService->logException($e, [
                'operation' => 'user_creation',
                'email' => $request->email
            ]);

            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
