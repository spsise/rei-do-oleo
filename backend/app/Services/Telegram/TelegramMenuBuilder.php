<?php

namespace App\Services\Telegram;

use App\Services\Channels\TelegramChannel;

class TelegramMenuBuilder
{
    public function __construct(
        private TelegramChannel $telegramChannel
    ) {}

    /**
     * Build main menu
     */
    public function buildMainMenu(int $chatId): array
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
     * Show main menu (called by command system)
     */
    public function showMainMenu(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->buildMainMenu($chatId);
    }

    /**
     * Build report menu
     */
    public function buildReportMenu(int $chatId): array
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
     * Build services menu
     */
    public function buildServicesMenu(int $chatId): array
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
     * Show services menu (called by command system)
     */
    public function showServicesMenu(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->buildServicesMenu($chatId);
    }

    /**
     * Build products menu
     */
    public function buildProductsMenu(int $chatId): array
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
     * Show reports menu (called by command system)
     */
    public function showReportsMenu(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->buildReportMenu($chatId);
    }

    /**
     * Show products menu (called by command system)
     */
    public function showProductsMenu(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->buildProductsMenu($chatId);
    }

    /**
     * Show dashboard menu (called by command system)
     */
    public function showDashboardMenu(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->buildDashboardMenu($chatId);
    }

    /**
     * Build dashboard menu
     */
    public function buildDashboardMenu(int $chatId): array
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
     * Build report period selection menu
     */
    public function buildReportPeriodMenu(int $chatId, string $reportType): array
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
     * Build navigation menu
     */
    public function buildNavigationMenu(int $chatId, string $from): array
    {
        return match($from) {
            'report_menu' => $this->buildReportMenu($chatId),
            'services_menu' => $this->buildServicesMenu($chatId),
            'products_menu' => $this->buildProductsMenu($chatId),
            'dashboard_menu' => $this->buildDashboardMenu($chatId),
            default => $this->buildMainMenu($chatId)
        };
    }

    /**
     * Build error message with navigation
     */
    public function buildErrorMessage(int $chatId): array
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

    /**
     * Build unauthorized message
     */
    public function buildUnauthorizedMessage(int $chatId): array
    {
        $message = "❌ *Acesso Negado*\n\n" .
                   "Você não está autorizado a usar este bot.\n" .
                   "Entre em contato com o administrador.";

        return $this->telegramChannel->sendTextMessage($message, $chatId);
    }
}
