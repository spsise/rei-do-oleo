<?php

namespace App\Services\Telegram\Handlers;

use App\Contracts\Telegram\TelegramCommandHandlerInterface;
use App\Services\Telegram\TelegramMenuBuilder;

class MenuCommandHandler implements TelegramCommandHandlerInterface
{
    public function __construct(
        private TelegramMenuBuilder $menuBuilder
    ) {}

    public function handle(int $chatId, array $params = []): array
    {
        $menuType = $params['menu_type'] ?? 'main';

        return match($menuType) {
            'services' => $this->menuBuilder->buildServicesMenu($chatId),
            'products' => $this->menuBuilder->buildProductsMenu($chatId),
            'dashboard' => $this->menuBuilder->buildDashboardMenu($chatId),
            'report' => $this->menuBuilder->buildReportMenu($chatId),
            default => $this->menuBuilder->buildMainMenu($chatId)
        };
    }

    public function getCommandName(): string
    {
        return 'menu';
    }

    public function getCommandDescription(): string
    {
        return 'Navegar pelos menus do sistema';
    }

    public function canHandle(string $command): bool
    {
        // Handle all menu-related commands including natural language
        $menuCommands = [
            // Specific menu commands
            'services', 'products', 'dashboard', 'report',
            'services_menu', 'products_menu', 'dashboard_menu', 'report_menu',

            // Natural language commands
            'menu', 'main_menu', 'voltar', 'back', 'menu_principal',

            // Portuguese natural language
            'relatório', 'relatórios', 'serviços', 'produtos', 'dashboard',

            // English natural language
            'reports', 'services', 'products', 'status'
        ];

        return in_array(strtolower($command), $menuCommands);
    }
}
