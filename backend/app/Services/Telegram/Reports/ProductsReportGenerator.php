<?php

namespace App\Services\Telegram\Reports;

use App\Contracts\Telegram\TelegramReportGeneratorInterface;
use App\Domain\Product\Services\ProductService;
use App\Services\Channels\TelegramChannel;

class ProductsReportGenerator implements TelegramReportGeneratorInterface
{
    public function __construct(
        private ProductService $productService,
        private TelegramChannel $telegramChannel
    ) {}

    public function generate(int $chatId, array $params = []): array
    {
        try {
            $productsData = $this->productService->getDashboardStats();
            $message = $this->formatReport($productsData);

            $keyboard = [
                [
                    ['text' => '📦 Mais Produtos', 'callback_data' => 'products_menu'],
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);

        } catch (\Exception $e) {
            return $this->sendErrorMessage($chatId);
        }
    }

    public function getReportType(): string
    {
        return 'products';
    }

    public function getReportName(): string
    {
        return 'Relatório de Produtos';
    }

    public function getAvailablePeriods(): array
    {
        return ['today', 'week', 'month'];
    }

    /**
     * Format products report
     */
    private function formatReport(array $data): string
    {
        $message = "📦 *Relatório de Produtos*\n\n" .
                   "📊 *Resumo:*\n" .
                   "• Total de produtos: {$data['total_products']}\n" .
                   "• Com estoque baixo: {$data['low_stock_count']}\n\n" .
                   "🏆 *Top Produtos:*\n";

        if (!empty($data['top_products'])) {
            foreach (array_slice($data['top_products'], 0, 3) as $product) {
                $message .= "• {$product['name']} - {$product['sales_count']} vendas\n";
            }
        } else {
            $message .= "• Nenhum produto vendido recentemente\n";
        }

        $message .= "\n📅 Gerado em: " . now()->format('d/m/Y H:i:s');

        return $message;
    }

    /**
     * Send error message
     */
    private function sendErrorMessage(int $chatId): array
    {
        $message = "⚠️ *Erro no Sistema*\n\n" .
                   "Ocorreu um erro ao gerar o relatório.\n" .
                   "Tente novamente em alguns instantes.";

        $keyboard = [
            [
                ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }
}
