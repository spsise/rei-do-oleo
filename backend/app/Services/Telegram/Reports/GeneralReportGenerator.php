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
            return $this->sendErrorMessage($chatId, $e->getMessage());
        }
    }

    /**
     * Generate today report (called by command system)
     */
    public function generateTodayReport(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->generate($chatId, ['period' => 'today']);
    }

    /**
     * Generate week report (called by command system)
     */
    public function generateWeekReport(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->generate($chatId, ['period' => 'week']);
    }

    /**
     * Generate month report (called by command system)
     */
    public function generateMonthReport(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->generate($chatId, ['period' => 'month']);
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

        // Ensure all required keys exist with default values
        $data = array_merge([
            'total_services' => 0,
            'scheduled' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'total_revenue' => 0,
            'average_ticket' => 0,
            'total_products' => 0,
            'low_stock_count' => 0,
            'average_service_time' => 0,
            'pending_services' => 0
        ], $data);

        $message = "📈 *Dashboard Geral - {$periodLabel}*\n\n" .
                   "🔧 *Serviços:*\n" .
                   "• Total: {$data['total_services']}\n" .
                   "• Agendados: {$data['scheduled']}\n" .
                   "• Em andamento: {$data['in_progress']}\n" .
                   "• Concluídos: {$data['completed']}\n\n" .
                   "💰 *Financeiro:*\n" .
                   "• Receita total: R$ " . number_format($data['total_revenue'], 2, ',', '.') . "\n" .
                   "• Ticket médio: R$ " . number_format($data['average_ticket'], 2, ',', '.') . "\n\n" .
                   "📦 *Produtos:*\n" .
                   "• Total: {$data['total_products']}\n" .
                   "• Estoque baixo: {$data['low_stock_count']}\n\n" .
                   "⏱️ *Performance:*\n" .
                   "• Tempo médio: " . $data['average_service_time'] . " min\n" .
                   "• Pendentes: {$data['pending_services']}\n\n" .
                   "📅 Gerado em: " . now()->format('d/m/Y H:i:s');

        return $message;
    }

    /**
     * Send error message
     */
    private function sendErrorMessage(int $chatId, string $errorMessage = ''): array
    {
        $message = "⚠️ *Erro no Sistema*\n\n" .
                   "Ocorreu um erro ao gerar o relatório.\n" .
                   "Tente novamente em alguns instantes.";

        if ($errorMessage) {
            $message .= "\n\n*Detalhes:* " . $errorMessage;
        }

        $keyboard = [
            [
                ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }
}
