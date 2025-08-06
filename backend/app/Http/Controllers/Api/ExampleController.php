<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\LoggingServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExampleController extends Controller
{
    public function __construct(
        private LoggingServiceInterface $loggingService
    ) {}

    /**
     * Example of using the new logging system
     */
    public function example(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // Log the API request
            $this->loggingService->logApiRequest($request, [
                'operation' => 'example_endpoint',
                'custom_data' => 'example_value'
            ]);

            // Simulate some business logic
            $result = $this->performBusinessOperation($request->all());

            // Log business operation
            $this->loggingService->logBusinessOperation(
                'example_operation',
                ['input' => $request->all(), 'output' => $result],
                'success'
            );

            $duration = (microtime(true) - $startTime) * 1000;

            // Log performance
            $this->loggingService->logPerformance(
                'example_operation',
                $duration,
                ['result_size' => strlen(json_encode($result))]
            );

            // Log the response
            $response = response()->json(['success' => true, 'data' => $result]);
            $this->loggingService->logApiResponse(
                $response->getStatusCode(),
                ['success' => true, 'data' => $result],
                $duration
            );

            return $response;

        } catch (\Exception $e) {
            // Log the exception
            $this->loggingService->logException($e, [
                'operation' => 'example_endpoint',
                'request_data' => $request->all()
            ]);

            // Log security event if it's an authentication error
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                $this->loggingService->logSecurityEvent(
                    'authentication_failed',
                    ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
                    'warning'
                );
            }

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Example of security logging
     */
    public function secureEndpoint(Request $request): JsonResponse
    {
        // Log security event for sensitive operations
        $this->loggingService->logSecurityEvent(
            'sensitive_operation_accessed',
            [
                'endpoint' => 'secure_endpoint',
                'user_id' => $request->user()?->id,
                'permissions' => $request->user()?->getAllPermissions()->pluck('name')->toArray()
            ],
            'info'
        );

        return response()->json(['message' => 'Secure operation completed']);
    }

    /**
     * Simulate business operation
     */
    private function performBusinessOperation(array $data): array
    {
        // Simulate processing time
        usleep(100000); // 100ms

        return [
            'processed' => true,
            'input_data' => $data,
            'timestamp' => now()->toISOString()
        ];
    }
}
