<?php

namespace App\Services\Telegram\Reports;

use App\Contracts\Telegram\TelegramReportGeneratorInterface;
use App\Domain\Service\Services\ServiceService;
use App\Services\Channels\TelegramChannel;

class ServicesReportGenerator implements TelegramReportGeneratorInterface
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
                    ['text' => '🔧 Mais Serviços', 'callback_data' => 'services_menu'],
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);

        } catch (\Exception $e) {
            return $this->sendErrorMessage($chatId, $e->getMessage());
        }
    }

    public function getReportType(): string
    {
        return 'services';
    }

    public function getReportName(): string
    {
        return 'Relatório de Serviços';
    }

    public function getAvailablePeriods(): array
    {
        return ['today', 'week', 'month'];
    }

    /**
     * Format services report
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
            'completed' => 0,
            'in_progress' => 0,
            'pending_services' => 0,
            'total_revenue' => 0,
            'average_service_time' => 0,
            'completed_today' => 0
        ], $data);

        $message = "🔧 *Relatório de Serviços - {$periodLabel}*\n\n" .
                   "📋 *Resumo:*\n" .
                   "• Total: {$data['total_services']}\n" .
                   "• Concluídos: {$data['completed']}\n" .
                   "• Em andamento: {$data['in_progress']}\n" .
                   "• Pendentes: {$data['pending_services']}\n\n" .
                   "💰 *Receita:*\n" .
                   "• Total: R$ " . number_format($data['total_revenue'], 2, ',', '.') . "\n" .
                   "• Média: R$ " . number_format($data['average_service_time'], 2, ',', '.') . "\n\n" .
                   "📈 *Performance:*\n" .
                   "• Concluídos hoje: {$data['completed_today']}\n" .
                   "• Tempo médio: " . $data['average_service_time'] . " min\n\n" .
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
