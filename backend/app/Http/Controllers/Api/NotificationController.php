<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasWhatsAppNotifications;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    use HasWhatsAppNotifications;

    /**
     * Send custom WhatsApp message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendCustomMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'phone_number' => 'nullable|string'
        ]);

        $result = $this->sendWhatsAppNotification(
            $request->message,
            $request->phone_number
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
            'level' => 'nullable|string|in:info,warning,error,success'
        ]);

        $result = $this->sendSystemAlert(
            $request->title,
            $request->message,
            $request->level ?? 'info'
        );

        return response()->json($result);
    }

    /**
     * Send order notification
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendOrderNotification(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer',
            'customer_name' => 'required|string|max:100',
            'total' => 'required|numeric|min:0',
            'items_count' => 'required|integer|min:1'
        ]);

        $orderData = [
            'id' => (int) $request->order_id,
            'customer_name' => (string) $request->customer_name,
            'total' => (float) $request->total,
            'items_count' => (int) $request->items_count
        ];

        $result = $this->sendOrderWhatsAppNotification($orderData);

        return response()->json($result);
    }

    /**
     * Send stock alert
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendStockAlert(Request $request): JsonResponse
    {
        $request->validate([
            'product_name' => 'required|string|max:100',
            'current_quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'product_code' => 'required|string|max:50'
        ]);

        $stockData = [
            'product_name' => (string) $request->product_name,
            'current_quantity' => (int) $request->current_quantity,
            'min_quantity' => (int) $request->min_quantity,
            'product_code' => (string) $request->product_code
        ];

        $result = $this->sendStockWhatsAppAlert($stockData);

        return response()->json($result);
    }

    /**
     * Test WhatsApp connection
     *
     * @return JsonResponse
     */
    public function testConnection(): JsonResponse
    {
        $whatsappService = app(\App\Services\WhatsAppService::class);
        $result = $whatsappService->testConnection();

        return response()->json($result);
    }
}
