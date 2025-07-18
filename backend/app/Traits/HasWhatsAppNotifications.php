<?php

namespace App\Traits;

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

trait HasWhatsAppNotifications
{
    /**
     * Send WhatsApp notification
     *
     * @param string $message
     * @param string|null $phoneNumber
     * @return array
     */
    protected function sendWhatsAppNotification(string $message, ?string $phoneNumber = null): array
    {
        try {
            $whatsappService = app(WhatsAppService::class);

            if ($phoneNumber) {
                return $whatsappService->sendTextMessage($phoneNumber, $message);
            }

            // Send to configured recipients if no specific phone number
            $recipients = config('services.whatsapp.deploy_recipients', []);

            if (empty($recipients)) {
                Log::warning('No WhatsApp recipients configured');
                return ['success' => false, 'error' => 'No recipients configured'];
            }

            $results = [];
            foreach ($recipients as $recipient) {
                $results[$recipient] = $whatsappService->sendTextMessage($recipient, $message);
            }

            $successCount = count(array_filter($results, fn($r) => $r['success']));

            return [
                'success' => $successCount > 0,
                'sent_to' => $successCount,
                'total_recipients' => count($results),
                'results' => $results
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp notification failed', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send deploy notification
     *
     * @param array $deployData
     * @return array
     */
    protected function sendDeployNotification(array $deployData): array
    {
        try {
            $whatsappService = app(WhatsAppService::class);
            return $whatsappService->sendDeployNotification($deployData);
        } catch (\Exception $e) {
            Log::error('Deploy notification failed', [
                'error' => $e->getMessage(),
                'deploy_data' => $deployData
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send system alert notification
     *
     * @param string $title
     * @param string $message
     * @param string $level
     * @return array
     */
    protected function sendSystemAlert(string $title, string $message, string $level = 'info'): array
    {
        $emoji = match($level) {
            'error' => '❌',
            'warning' => '⚠️',
            'success' => '✅',
            default => 'ℹ️'
        };

        $formattedMessage = "🚨 *SYSTEM ALERT*\n\n" .
                           "{$emoji} *{$title}*\n" .
                           "💬 {$message}\n" .
                           "⏰ " . now()->format('d/m/Y H:i:s') . "\n\n" .
                           "Sistema: Rei do Óleo";

        return $this->sendWhatsAppNotification($formattedMessage);
    }

    /**
     * Send order notification
     *
     * @param array $orderData
     * @return array
     */
    protected function sendOrderWhatsAppNotification(array $orderData): array
    {
        $message = "🛒 *NOVO PEDIDO*\n\n" .
                   "📋 *Pedido #{$orderData['id']}*\n" .
                   "👤 *Cliente:* {$orderData['customer_name']}\n" .
                   "💰 *Total:* R$ " . number_format($orderData['total'], 2, ',', '.') . "\n" .
                   "📦 *Itens:* {$orderData['items_count']}\n" .
                   "⏰ " . now()->format('d/m/Y H:i:s') . "\n\n" .
                   "Sistema: Rei do Óleo";

        return $this->sendWhatsAppNotification($message);
    }

    /**
     * Send stock alert notification
     *
     * @param array $stockData
     * @return array
     */
    protected function sendStockWhatsAppAlert(array $stockData): array
    {
        $message = "📦 *ALERTA DE ESTOQUE*\n\n" .
                   "⚠️ *Produto:* {$stockData['product_name']}\n" .
                   "📊 *Quantidade Atual:* {$stockData['current_quantity']}\n" .
                   "🔴 *Quantidade Mínima:* {$stockData['min_quantity']}\n" .
                   "📋 *Código:* {$stockData['product_code']}\n" .
                   "⏰ " . now()->format('d/m/Y H:i:s') . "\n\n" .
                   "Sistema: Rei do Óleo";

        return $this->sendWhatsAppNotification($message);
    }
}
