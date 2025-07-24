<?php

namespace App\Services\Telegram\Reports;

use App\Contracts\Telegram\TelegramReportGeneratorInterface;
use App\Domain\Service\Services\ServiceService;
use App\Services\Channels\TelegramChannel;

class GeneralReportGenerator implements TelegramReportGeneratorInterface
{
    public function __construct(
        private ServiceService $serviceService,
        private TelegramChannel $telegramChannel
    ) {}

    public function generate(int $chatId, array $params = []): array
    {
        $period = $params['period'] ?? 'today';

        try {
            $dashboardData = $this->serviceService->getDashboardMetrics(null, $period);
            $message = $this->formatReport($dashboardData, $period);

            $keyboard = [
                [
                    ['text' => '📊 Outro Relatório', 'callback_data' => 'report_menu'],
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
        return 'general';
    }

    public function getReportName(): string
    {
        return 'Relatório Geral';
    }

    public function getAvailablePeriods(): array
    {
        return ['today', 'week', 'month'];
    }

    /**
     * Format general report
     */
    private function formatReport(array $data, string $period): string
    {
        $periodLabel = match($period) {
            'today' => 'Hoje',
            'week' => 'Esta Semana',
            'month' => 'Este Mês',
            default => 'Hoje'
        };

        $message = "📈 *Dashboard Geral - {$periodLabel}*\n\n" .
                   "🔧 *Serviços:*\n" .
                   "• Total: {$data['total_services']}\n" .
                   "• Agendados: {$data['scheduled']}\n" .
                   "• Em andamento: {$data['in_progress']}\n" .
                   "• Concluídos: {$data['completed']}\n\n" .
                   "💰 *Financeiro:*\n" .
                   "• Receita total: R$ " . number_format($data['total_revenue'], 2, ',', '.') . "\n" .
                   "• Ticket médio: R$ " . number_format($data['average_ticket'] ?? 0, 2, ',', '.') . "\n\n" .
                   "📦 *Produtos:*\n" .
                   "• Total: {$data['total_products']}\n" .
                   "• Estoque baixo: {$data['low_stock_count']}\n\n" .
                   "⏱️ *Performance:*\n" .
                   "• Tempo médio: " . ($data['average_service_time'] ?? 0) . " min\n" .
                   "• Pendentes: {$data['pending_services']}\n\n" .
                   "📅 Gerado em: " . now()->format('d/m/Y H:i:s');

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
