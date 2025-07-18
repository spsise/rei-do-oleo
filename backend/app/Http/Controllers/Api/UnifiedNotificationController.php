<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UnifiedNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UnifiedNotificationController extends Controller
{
    private UnifiedNotificationService $notificationService;

    public function __construct(UnifiedNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Send message to all channels
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'recipient' => 'nullable|string',
            'channels' => 'nullable|array',
            'channels.*' => 'string|in:whatsapp,telegram'
        ]);

        $result = $this->notificationService->sendMessage(
            $request->message,
            $request->recipient,
            $request->channels ?? []
        );

        return response()->json($result);
    }

    /**
     * Send system alert
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendSystemAlert(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:500',
            'level' => 'nullable|string|in:info,warning,error,success',
            'channels' => 'nullable|array',
            'channels.*' => 'string|in:whatsapp,telegram'
        ]);

        $result = $this->notificationService->sendSystemAlert(
            $request->title,
            $request->message,
            $request->level ?? 'info',
            $request->channels ?? []
        );

        return response()->json($result);
    }

    /**
     * Send deploy notification
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendDeployNotification(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:success,error,warning',
            'branch' => 'required|string',
            'commit' => 'required|string',
            'message' => 'required|string',
            'output' => 'nullable|string',
            'channels' => 'nullable|array',
            'channels.*' => 'string|in:whatsapp,telegram'
        ]);

        $deployData = [
            'status' => $request->status,
            'branch' => $request->branch,
            'commit' => $request->commit,
            'message' => $request->message,
            'timestamp' => now()->format('d/m/Y H:i:s'),
            'output' => $request->output ?? ''
        ];

        $result = $this->notificationService->sendDeployNotification(
            $deployData,
            $request->channels ?? []
        );

        return response()->json($result);
    }

    /**
     * Test all channels
     *
     * @return JsonResponse
     */
    public function testChannels(): JsonResponse
    {
        $result = $this->notificationService->testAllChannels();

        return response()->json($result);
    }

    /**
     * Get available channels
     *
     * @return JsonResponse
     */
    public function getChannels(): JsonResponse
    {
        $channels = $this->notificationService->getAvailableChannels();

        return response()->json($channels);
    }

    /**
     * Test specific channel
     *
     * @param string $channel
     * @return JsonResponse
     */
    public function testChannel(string $channel): JsonResponse
    {
        $channelInstance = $this->notificationService->getChannel($channel);

        if (!$channelInstance) {
            return response()->json([
                'success' => false,
                'error' => "Channel '{$channel}' not found"
            ], 404);
        }

        $result = $channelInstance->testConnection();

        return response()->json($result);
    }
}
