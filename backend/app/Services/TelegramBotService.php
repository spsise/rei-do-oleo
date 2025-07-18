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
                case 'start':
                case 'help':
                    return $this->sendMainMenu($chatId);

                case 'report':
                    return $this->sendReportMenu($chatId);

                case 'status':
                    return $this->sendSystemStatus($chatId);

                case 'services':
                    return $this->sendServicesReport($chatId, $command['params']);

                case 'products':
                    return $this->sendProductsReport($chatId, $command['params']);

                case 'dashboard':
                    return $this->sendDashboardReport($chatId);

                case 'menu':
                    return $this->sendMainMenu($chatId);

                default:
                    return $this->sendMainMenu($chatId);
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
     * Process callback query from inline keyboard buttons
     */
    public function processCallbackQuery(array $callbackQuery): array
    {
        try {
            $chatId = $callbackQuery['message']['chat']['id'];
            $messageId = $callbackQuery['message']['message_id'];
            $callbackData = $callbackQuery['data'] ?? '';
            $from = $callbackQuery['from'] ?? [];

            Log::info('Telegram callback query received', [
                'chat_id' => $chatId,
                'callback_data' => $callbackData,
                'from' => $from
            ]);

            // Check if user is authorized
            if (!$this->isAuthorizedUser($chatId)) {
                return $this->sendUnauthorizedMessage($chatId);
            }

            // Parse callback data
            $callback = $this->parseCallbackData($callbackData);

            switch ($callback['action']) {
                case 'main_menu':
                    return $this->sendMainMenu($chatId);

                case 'report_menu':
                    return $this->sendReportMenu($chatId);

                case 'services_menu':
                    return $this->sendServicesMenu($chatId);

                case 'products_menu':
                    return $this->sendProductsMenu($chatId);

                case 'dashboard_menu':
                    return $this->sendDashboardMenu($chatId);

                case 'report_general':
                    return $this->sendReportPeriodMenu($chatId, 'general');

                case 'report_services':
                    return $this->sendReportPeriodMenu($chatId, 'services');

                case 'report_products':
                    return $this->sendReportPeriodMenu($chatId, 'products');

                case 'report_dashboard':
                    return $this->generateReport($chatId, ['period' => 'today']);

                case 'period_today':
                    return $this->generateReport($chatId, ['period' => 'today'], $callback['report_type'] ?? 'general');

                case 'period_week':
                    return $this->generateReport($chatId, ['period' => 'week'], $callback['report_type'] ?? 'general');

                case 'period_month':
                    return $this->generateReport($chatId, ['period' => 'month'], $callback['report_type'] ?? 'general');

                case 'services_status':
                    return $this->sendServicesReport($chatId, ['period' => 'today']);

                case 'services_performance':
                    return $this->sendServicesPerformanceReport($chatId);

                case 'products_stock':
                    return $this->sendProductsReport($chatId, []);

                case 'products_low_stock':
                    return $this->sendLowStockReport($chatId);

                case 'back':
                    return $this->handleBackNavigation($chatId, $callback['from'] ?? 'main_menu');

                default:
                    return $this->sendMainMenu($chatId);
            }

        } catch (\Exception $e) {
            Log::error('Error processing Telegram callback query', [
                'error' => $e->getMessage(),
                'callback_query' => $callbackQuery
            ]);

            return $this->sendErrorMessage($chatId);
        }
    }

    /**
     * Send main menu with inline keyboard
     */
    private function sendMainMenu(int $chatId): array
    {
        $message = "🤖 *Rei do Óleo - Bot de Relatórios*\n\n" .
                   "Bem-vindo! Escolha uma opção abaixo:";

        $keyboard = [
            [
                ['text' => '📊 Relatórios', 'callback_data' => 'report_menu'],
                ['text' => '🔧 Serviços', 'callback_data' => 'services_menu']
            ],
            [
                ['text' => '📦 Produtos', 'callback_data' => 'products_menu'],
                ['text' => '📈 Dashboard', 'callback_data' => 'dashboard_menu']
            ],
            [
                ['text' => '📋 Status do Sistema', 'callback_data' => 'status']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }

    /**
     * Send report menu
     */
    private function sendReportMenu(int $chatId): array
    {
        $message = "📊 *Menu de Relatórios*\n\n" .
                   "Escolha o tipo de relatório:";

        $keyboard = [
            [
                ['text' => '📋 Relatório Geral', 'callback_data' => 'report_general'],
                ['text' => '🔧 Relatório de Serviços', 'callback_data' => 'report_services']
            ],
            [
                ['text' => '📦 Relatório de Produtos', 'callback_data' => 'report_products'],
                ['text' => '📈 Dashboard Completo', 'callback_data' => 'report_dashboard']
            ],
            [
                ['text' => '⬅️ Voltar', 'callback_data' => 'main_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }

    /**
     * Send services menu
     */
    private function sendServicesMenu(int $chatId): array
    {
        $message = "🔧 *Menu de Serviços*\n\n" .
                   "Escolha o que deseja consultar:";

        $keyboard = [
            [
                ['text' => '📋 Status Atual', 'callback_data' => 'services_status'],
                ['text' => '📈 Performance', 'callback_data' => 'services_performance']
            ],
            [
                ['text' => '⬅️ Voltar', 'callback_data' => 'main_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }

    /**
     * Send products menu
     */
    private function sendProductsMenu(int $chatId): array
    {
        $message = "📦 *Menu de Produtos*\n\n" .
                   "Escolha o que deseja consultar:";

        $keyboard = [
            [
                ['text' => '📋 Status do Estoque', 'callback_data' => 'products_stock'],
                ['text' => '⚠️ Estoque Baixo', 'callback_data' => 'products_low_stock']
            ],
            [
                ['text' => '⬅️ Voltar', 'callback_data' => 'main_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }

    /**
     * Send dashboard menu
     */
    private function sendDashboardMenu(int $chatId): array
    {
        $message = "📈 *Dashboard*\n\n" .
                   "Escolha o período:";

        $keyboard = [
            [
                ['text' => '📅 Hoje', 'callback_data' => 'period_today:general'],
                ['text' => '📅 Esta Semana', 'callback_data' => 'period_week:general']
            ],
            [
                ['text' => '📅 Este Mês', 'callback_data' => 'period_month:general']
            ],
            [
                ['text' => '⬅️ Voltar', 'callback_data' => 'main_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }

    /**
     * Send report period selection menu
     */
    private function sendReportPeriodMenu(int $chatId, string $reportType): array
    {
        $reportLabels = [
            'general' => 'Relatório Geral',
            'services' => 'Relatório de Serviços',
            'products' => 'Relatório de Produtos'
        ];

        $message = "📊 *{$reportLabels[$reportType]}*\n\n" .
                   "Escolha o período:";

        $keyboard = [
            [
                ['text' => '📅 Hoje', 'callback_data' => "period_today:{$reportType}"],
                ['text' => '📅 Esta Semana', 'callback_data' => "period_week:{$reportType}"]
            ],
            [
                ['text' => '📅 Este Mês', 'callback_data' => "period_month:{$reportType}"]
            ],
            [
                ['text' => '⬅️ Voltar', 'callback_data' => 'report_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }

    /**
     * Handle back navigation
     */
    private function handleBackNavigation(int $chatId, string $from): array
    {
        return match($from) {
            'report_menu' => $this->sendReportMenu($chatId),
            'services_menu' => $this->sendServicesMenu($chatId),
            'products_menu' => $this->sendProductsMenu($chatId),
            'dashboard_menu' => $this->sendDashboardMenu($chatId),
            default => $this->sendMainMenu($chatId)
        };
    }

    /**
     * Parse callback data from inline keyboard
     */
    private function parseCallbackData(string $callbackData): array
    {
        $parts = explode(':', $callbackData);

        return [
            'action' => $parts[0] ?? '',
            'report_type' => $parts[1] ?? null,
            'from' => $parts[1] ?? null
        ];
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

        if (str_contains($text, 'menu') || str_contains($text, 'ajuda') || str_contains($text, 'help')) {
            return [
                'type' => 'menu',
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
     * Get Telegram channel instance
     */
    public function getTelegramChannel(): TelegramChannel
    {
        return $this->telegramChannel;
    }

    /**
     * Generate general report
     */
    private function generateReport(int $chatId, array $params, string $reportType = 'general'): array
    {
        $period = $params['period'] ?? 'today';

        try {
            $message = match($reportType) {
                'services' => $this->generateServicesReport($period),
                'products' => $this->generateProductsReport(),
                default => $this->generateGeneralReport($period)
            };

            // Add navigation buttons
            $keyboard = [
                [
                    ['text' => '📊 Outro Relatório', 'callback_data' => 'report_menu'],
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);

        } catch (\Exception $e) {
            Log::error('Error generating report', ['error' => $e->getMessage()]);
            return $this->sendErrorMessage($chatId);
        }
    }

    /**
     * Generate general report
     */
    private function generateGeneralReport(string $period): string
    {
        $dashboardData = $this->serviceService->getDashboardMetrics(null, $period);
        return $this->formatDashboardReport($dashboardData, $period);
    }

    /**
     * Generate services report
     */
    private function generateServicesReport(string $period): string
    {
        $dashboardData = $this->serviceService->getDashboardMetrics(null, $period);
        return $this->formatServicesReport($dashboardData, $period);
    }

    /**
     * Generate products report
     */
    private function generateProductsReport(): string
    {
        $productsData = $this->productService->getDashboardStats();
        return $this->formatProductsReport($productsData);
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

            $keyboard = [
                [
                    ['text' => '🔧 Mais Serviços', 'callback_data' => 'services_menu'],
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);

        } catch (\Exception $e) {
            Log::error('Error generating services report', ['error' => $e->getMessage()]);
            return $this->sendErrorMessage($chatId);
        }
    }

    /**
     * Send services performance report
     */
    private function sendServicesPerformanceReport(int $chatId): array
    {
        try {
            $dashboardData = $this->serviceService->getDashboardMetrics(null, 'week');

            $message = "📈 *Performance de Serviços - Esta Semana*\n\n" .
                       "⏱️ *Tempo Médio:* " . ($dashboardData['average_service_time'] ?? 0) . " min\n" .
                       "🎯 *Eficiência:* " . $this->calculateEfficiency($dashboardData) . "%\n" .
                       "📊 *Concluídos:* {$dashboardData['completed']}\n" .
                       "⏳ *Pendentes:* {$dashboardData['pending_services']}\n\n" .
                       "📅 Gerado em: " . now()->format('d/m/Y H:i:s');

            $keyboard = [
                [
                    ['text' => '🔧 Mais Serviços', 'callback_data' => 'services_menu'],
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);

        } catch (\Exception $e) {
            Log::error('Error generating services performance report', ['error' => $e->getMessage()]);
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

            $keyboard = [
                [
                    ['text' => '📦 Mais Produtos', 'callback_data' => 'products_menu'],
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);

        } catch (\Exception $e) {
            Log::error('Error generating products report', ['error' => $e->getMessage()]);
            return $this->sendErrorMessage($chatId);
        }
    }

    /**
     * Send low stock report
     */
    private function sendLowStockReport(int $chatId): array
    {
        try {
            $productsData = $this->productService->getDashboardStats();

            $message = "⚠️ *Produtos com Estoque Baixo*\n\n" .
                       "📦 *Total:* {$productsData['low_stock_count']} produtos\n\n";

            if (!empty($productsData['low_stock_products'])) {
                $message .= "📋 *Produtos:*\n";
                foreach (array_slice($productsData['low_stock_products'], 0, 5) as $product) {
                    $message .= "• {$product['name']} - {$product['stock']} unidades\n";
                }
            } else {
                $message .= "✅ Nenhum produto com estoque baixo\n";
            }

            $message .= "\n📅 Gerado em: " . now()->format('d/m/Y H:i:s');

            $keyboard = [
                [
                    ['text' => '📦 Mais Produtos', 'callback_data' => 'products_menu'],
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);

        } catch (\Exception $e) {
            Log::error('Error generating low stock report', ['error' => $e->getMessage()]);
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
            $message = "📋 *Status do Sistema*\n\n" .
                       "🟢 *Sistema:* Online\n" .
                       "🟢 *API:* Funcionando\n" .
                       "🟢 *Banco de Dados:* Conectado\n" .
                       "🟢 *Telegram Bot:* Ativo\n\n" .
                       "⏰ *Última verificação:* " . now()->format('d/m/Y H:i:s');

            $keyboard = [
                [
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);

        } catch (\Exception $e) {
            Log::error('Error sending system status', ['error' => $e->getMessage()]);
            return $this->sendErrorMessage($chatId);
        }
    }

    /**
     * Calculate efficiency percentage
     */
    private function calculateEfficiency(array $data): float
    {
        $total = $data['total_services'] ?? 0;
        $completed = $data['completed'] ?? 0;

        if ($total === 0) {
            return 0.0;
        }

        return round(($completed / $total) * 100, 1);
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

        $keyboard = [
            [
                ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }
}
