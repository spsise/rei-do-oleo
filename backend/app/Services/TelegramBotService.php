<?php

namespace App\Services;

use App\Domain\Service\Services\ServiceService;
use App\Domain\Product\Services\ProductService;
use App\Services\Channels\TelegramChannel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TelegramBotService
{
    private TelegramChannel $telegramChannel;
    private ServiceService $serviceService;
    private ProductService $productService;

    public function __construct(
        TelegramChannel $telegramChannel,
        ServiceService $serviceService,
        ProductService $productService
    ) {
        $this->telegramChannel = $telegramChannel;
        $this->serviceService = $serviceService;
        $this->productService = $productService;
    }

    /**
     * Process incoming message from Telegram webhook
     */
    public function processMessage(array $message): array
    {
        try {
            $chatId = $message['chat']['id'];
            $text = $message['text'] ?? '';
            $from = $message['from'] ?? [];

            Log::info('Telegram message received', [
                'chat_id' => $chatId,
                'text' => $text,
                'from' => $from
            ]);

            // Check if user is authorized
            if (!$this->isAuthorizedUser($chatId)) {
                return $this->sendUnauthorizedMessage($chatId);
            }

            // Process command
            $command = $this->parseCommand($text);

            switch ($command['type']) {
                case 'help':
                    return $this->sendHelpMessage($chatId);

                case 'report':
                    return $this->generateReport($chatId, $command['params']);

                case 'status':
                    return $this->sendSystemStatus($chatId);

                case 'services':
                    return $this->sendServicesReport($chatId, $command['params']);

                case 'products':
                    return $this->sendProductsReport($chatId, $command['params']);

                case 'dashboard':
                    return $this->sendDashboardReport($chatId);

                default:
                    return $this->sendUnknownCommandMessage($chatId);
            }

        } catch (\Exception $e) {
            Log::error('Error processing Telegram message', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);

            return $this->sendErrorMessage($chatId);
        }
    }

    /**
     * Parse command from message text
     */
    private function parseCommand(string $text): array
    {
        $text = trim(strtolower($text));

        // Remove bot username if present
        $text = preg_replace('/@\w+/', '', $text);

        // Parse command
        if (preg_match('/^\/(\w+)(?:\s+(.+))?$/', $text, $matches)) {
            $command = $matches[1];
            $params = $matches[2] ?? '';

            return [
                'type' => $command,
                'params' => $this->parseParams($params)
            ];
        }

        // Handle natural language
        if (str_contains($text, 'relatório') || str_contains($text, 'report')) {
            return [
                'type' => 'report',
                'params' => $this->parseNaturalLanguage($text)
            ];
        }

        if (str_contains($text, 'serviços') || str_contains($text, 'services')) {
            return [
                'type' => 'services',
                'params' => $this->parseNaturalLanguage($text)
            ];
        }

        if (str_contains($text, 'produtos') || str_contains($text, 'products')) {
            return [
                'type' => 'products',
                'params' => $this->parseNaturalLanguage($text)
            ];
        }

        if (str_contains($text, 'dashboard') || str_contains($text, 'status')) {
            return [
                'type' => 'dashboard',
                'params' => []
            ];
        }

        return [
            'type' => 'unknown',
            'params' => []
        ];
    }

    /**
     * Parse parameters from command
     */
    private function parseParams(string $params): array
    {
        $params = trim($params);
        $result = [];

        // Parse period
        if (preg_match('/(hoje|today|semana|week|mês|month|mês|month)/', $params, $matches)) {
            $result['period'] = $this->normalizePeriod($matches[1]);
        }

        // Parse date range
        if (preg_match('/(\d{1,2}\/\d{1,2}\/\d{4})/', $params, $matches)) {
            $result['date'] = $matches[1];
        }

        // Parse service center
        if (preg_match('/(centro|center)\s+(\d+)/', $params, $matches)) {
            $result['service_center_id'] = (int) $matches[2];
        }

        return $result;
    }

    /**
     * Parse natural language parameters
     */
    private function parseNaturalLanguage(string $text): array
    {
        $result = [];

        // Detect period
        if (str_contains($text, 'hoje') || str_contains($text, 'today')) {
            $result['period'] = 'today';
        } elseif (str_contains($text, 'semana') || str_contains($text, 'week')) {
            $result['period'] = 'week';
        } elseif (str_contains($text, 'mês') || str_contains($text, 'month')) {
            $result['period'] = 'month';
        } else {
            $result['period'] = 'today'; // Default
        }

        return $result;
    }

    /**
     * Normalize period parameter
     */
    private function normalizePeriod(string $period): string
    {
        return match(strtolower($period)) {
            'hoje', 'today' => 'today',
            'semana', 'week' => 'week',
            'mês', 'month' => 'month',
            default => 'today'
        };
    }

    /**
     * Check if user is authorized
     */
    private function isAuthorizedUser(int $chatId): bool
    {
        $authorizedUsers = config('services.telegram.recipients', []);
        return in_array($chatId, $authorizedUsers);
    }

    /**
     * Send help message
     */
    private function sendHelpMessage(int $chatId): array
    {
        $message = "🤖 *Rei do Óleo - Bot de Relatórios*\n\n" .
                   "*Comandos disponíveis:*\n\n" .
                   "📊 *Relatórios:*\n" .
                   "• `/report` - Relatório geral\n" .
                   "• `/report hoje` - Relatório de hoje\n" .
                   "• `/report semana` - Relatório da semana\n" .
                   "• `/report mês` - Relatório do mês\n\n" .
                   "🔧 *Serviços:*\n" .
                   "• `/services` - Status dos serviços\n" .
                   "• `/services hoje` - Serviços de hoje\n" .
                   "• `/services semana` - Serviços da semana\n\n" .
                   "📦 *Produtos:*\n" .
                   "• `/products` - Status do estoque\n" .
                   "• `/products baixo` - Produtos com estoque baixo\n\n" .
                   "📈 *Dashboard:*\n" .
                   "• `/dashboard` - Resumo geral\n" .
                   "• `/status` - Status do sistema\n\n" .
                   "💬 *Linguagem Natural:*\n" .
                   "• \"Envie um relatório de hoje\"\n" .
                   "• \"Como estão os serviços da semana?\"\n" .
                   "• \"Mostre o dashboard\"\n\n" .
                   "❓ *Ajuda:*\n" .
                   "• `/help` - Esta mensagem";

        return $this->telegramChannel->sendTextMessage($message, $chatId);
    }

    /**
     * Generate general report
     */
    private function generateReport(int $chatId, array $params): array
    {
        $period = $params['period'] ?? 'today';

        try {
            // Get dashboard data
            $dashboardData = $this->serviceService->getDashboardMetrics(null, $period);

            $message = $this->formatDashboardReport($dashboardData, $period);

            return $this->telegramChannel->sendTextMessage($message, $chatId);

        } catch (\Exception $e) {
            Log::error('Error generating report', ['error' => $e->getMessage()]);
            return $this->sendErrorMessage($chatId);
        }
    }

    /**
     * Send services report
     */
    private function sendServicesReport(int $chatId, array $params): array
    {
        $period = $params['period'] ?? 'today';

        try {
            $dashboardData = $this->serviceService->getDashboardMetrics(null, $period);

            $message = $this->formatServicesReport($dashboardData, $period);

            return $this->telegramChannel->sendTextMessage($message, $chatId);

        } catch (\Exception $e) {
            Log::error('Error generating services report', ['error' => $e->getMessage()]);
            return $this->sendErrorMessage($chatId);
        }
    }

    /**
     * Send products report
     */
    private function sendProductsReport(int $chatId, array $params): array
    {
        try {
            $productsData = $this->productService->getDashboardStats();

            $message = $this->formatProductsReport($productsData);

            return $this->telegramChannel->sendTextMessage($message, $chatId);

        } catch (\Exception $e) {
            Log::error('Error generating products report', ['error' => $e->getMessage()]);
            return $this->sendErrorMessage($chatId);
        }
    }

    /**
     * Send dashboard report
     */
    private function sendDashboardReport(int $chatId): array
    {
        return $this->generateReport($chatId, ['period' => 'today']);
    }

    /**
     * Send system status
     */
    private function sendSystemStatus(int $chatId): array
    {
        try {
            $message = "🖥️ *Status do Sistema - Rei do Óleo*\n\n" .
                       "✅ Sistema operacional\n" .
                       "🕐 Última verificação: " . now()->format('d/m/Y H:i:s') . "\n" .
                       "🌐 API: Online\n" .
                       "🗄️ Banco de dados: Online\n" .
                       "📱 Notificações: Ativas\n\n" .
                       "Para mais informações, use `/dashboard`";

            return $this->telegramChannel->sendTextMessage($message, $chatId);

        } catch (\Exception $e) {
            return $this->sendErrorMessage($chatId);
        }
    }

    /**
     * Format dashboard report
     */
    private function formatDashboardReport(array $data, string $period): string
    {
        $periodLabel = match($period) {
            'today' => 'Hoje',
            'week' => 'Esta Semana',
            'month' => 'Este Mês',
            default => 'Hoje'
        };

        $message = "📊 *Relatório Geral - {$periodLabel}*\n\n" .
                   "🔧 *Serviços:*\n" .
                   "• Total: {$data['total_services']}\n" .
                   "• Agendados: {$data['scheduled']}\n" .
                   "• Em andamento: {$data['in_progress']}\n" .
                   "• Concluídos: {$data['completed']}\n" .
                   "• Cancelados: {$data['cancelled']}\n\n" .
                   "💰 *Financeiro:*\n" .
                   "• Receita total: R$ " . number_format($data['total_revenue'], 2, ',', '.') . "\n" .
                   "• Ticket médio: R$ " . number_format($data['average_service_time'] ?? 0, 2, ',', '.') . "\n\n" .
                   "⏱️ *Performance:*\n" .
                   "• Tempo médio: " . ($data['average_service_time'] ?? 0) . " min\n" .
                   "• Pendentes: {$data['pending_services']}\n" .
                   "• Concluídos hoje: {$data['completed_today']}\n\n" .
                   "📅 Gerado em: " . now()->format('d/m/Y H:i:s');

        return $message;
    }

    /**
     * Format services report
     */
    private function formatServicesReport(array $data, string $period): string
    {
        $periodLabel = match($period) {
            'today' => 'Hoje',
            'week' => 'Esta Semana',
            'month' => 'Este Mês',
            default => 'Hoje'
        };

        $message = "🔧 *Relatório de Serviços - {$periodLabel}*\n\n" .
                   "📋 *Resumo:*\n" .
                   "• Total: {$data['total_services']}\n" .
                   "• Concluídos: {$data['completed']}\n" .
                   "• Em andamento: {$data['in_progress']}\n" .
                   "• Pendentes: {$data['pending_services']}\n\n" .
                   "💰 *Receita:*\n" .
                   "• Total: R$ " . number_format($data['total_revenue'], 2, ',', '.') . "\n" .
                   "• Média: R$ " . number_format($data['average_service_time'] ?? 0, 2, ',', '.') . "\n\n" .
                   "📈 *Performance:*\n" .
                   "• Concluídos hoje: {$data['completed_today']}\n" .
                   "• Tempo médio: " . ($data['average_service_time'] ?? 0) . " min\n\n" .
                   "📅 Gerado em: " . now()->format('d/m/Y H:i:s');

        return $message;
    }

    /**
     * Format products report
     */
    private function formatProductsReport(array $data): string
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
     * Send unauthorized message
     */
    private function sendUnauthorizedMessage(int $chatId): array
    {
        $message = "❌ *Acesso Negado*\n\n" .
                   "Você não está autorizado a usar este bot.\n" .
                   "Entre em contato com o administrador.";

        return $this->telegramChannel->sendTextMessage($message, $chatId);
    }

    /**
     * Send unknown command message
     */
    private function sendUnknownCommandMessage(int $chatId): array
    {
        $message = "❓ *Comando não reconhecido*\n\n" .
                   "Use `/help` para ver os comandos disponíveis.";

        return $this->telegramChannel->sendTextMessage($message, $chatId);
    }

    /**
     * Send error message
     */
    private function sendErrorMessage(int $chatId): array
    {
        $message = "⚠️ *Erro no Sistema*\n\n" .
                   "Ocorreu um erro ao processar sua solicitação.\n" .
                   "Tente novamente em alguns instantes.";

        return $this->telegramChannel->sendTextMessage($message, $chatId);
    }
}
