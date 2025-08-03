<?php

namespace App\Services\Telegram;

use App\Contracts\Telegram\TelegramCommandHandlerInterface;
use App\Services\Telegram\Handlers\StartCommandHandler;
use App\Services\Telegram\Handlers\ReportCommandHandler;
use App\Services\Telegram\Handlers\StatusCommandHandler;
use App\Services\Telegram\Handlers\VoiceCommandHandler;
use App\Services\Telegram\Reports\GeneralReportGenerator;
use App\Services\Telegram\Reports\ServicesReportGenerator;
use App\Services\Telegram\Reports\ProductsReportGenerator;
use App\Services\Telegram\TelegramMenuBuilder;
use App\Services\Channels\TelegramChannel;
use App\Services\SpeechToTextService;
use Illuminate\Support\Facades\Log;

class TelegramCommandHandlerManager
{
    private array $commandHandlers = [];
    private array $reportGenerators = [];

    public function __construct(
        private TelegramMenuBuilder $menuBuilder,
        private TelegramChannel $telegramChannel
    ) {
        $this->registerCommandHandlers();
        $this->registerReportGenerators();
    }

    /**
     * Register command handlers
     */
    private function registerCommandHandlers(): void
    {
        $this->commandHandlers = [
            new StartCommandHandler($this->menuBuilder),
            new ReportCommandHandler($this->menuBuilder),
            new StatusCommandHandler($this->telegramChannel),
        ];

        // Add voice command handler
        $this->commandHandlers[] = new VoiceCommandHandler(
            app(SpeechToTextService::class),
            $this->telegramChannel,
            $this->menuBuilder
        );
    }

    /**
     * Register report generators
     */
    private function registerReportGenerators(): void
    {
        $this->reportGenerators = [
            'general' => app(GeneralReportGenerator::class),
            'services' => app(ServicesReportGenerator::class),
            'products' => app(ProductsReportGenerator::class),
        ];
    }

    /**
     * Handle command
     */
    public function handleCommand(string $command, int $chatId, array $params = []): array
    {
        // Find command handler
        foreach ($this->commandHandlers as $handler) {
            if ($handler->canHandle($command)) {
                return $handler->handle($chatId, $params);
            }
        }

        // Handle menu commands
        if ($this->isMenuCommand($command)) {
            return $this->handleMenuCommand($command, $chatId);
        }

        // Handle report commands
        if ($this->isReportCommand($command)) {
            return $this->handleReportCommand($command, $chatId, $params);
        }

        // Default to main menu
        return $this->menuBuilder->buildMainMenu($chatId);
    }

    /**
     * Handle callback query
     */
    public function handleCallbackQuery(string $action, int $chatId, array $params = []): array
    {
        // Handle menu actions
        if ($this->isMenuAction($action)) {
            return $this->handleMenuAction($action, $chatId);
        }

        // Handle report actions
        if ($this->isReportAction($action)) {
            return $this->handleReportAction($action, $chatId, $params);
        }

        // Handle period actions
        if ($this->isPeriodAction($action)) {
            return $this->handlePeriodAction($action, $chatId, $params);
        }

        // Default to main menu
        return $this->menuBuilder->buildMainMenu($chatId);
    }

    /**
     * Check if command is menu command
     */
    private function isMenuCommand(string $command): bool
    {
        return in_array($command, ['menu', 'services', 'products', 'dashboard']);
    }

    /**
     * Handle menu command
     */
    private function handleMenuCommand(string $command, int $chatId): array
    {
        return match($command) {
            'menu' => $this->menuBuilder->buildMainMenu($chatId),
            'services' => $this->menuBuilder->buildServicesMenu($chatId),
            'products' => $this->menuBuilder->buildProductsMenu($chatId),
            'dashboard' => $this->menuBuilder->buildDashboardMenu($chatId),
            default => $this->menuBuilder->buildMainMenu($chatId)
        };
    }

    /**
     * Check if command is report command
     */
    private function isReportCommand(string $command): bool
    {
        return in_array($command, ['report']);
    }

    /**
     * Handle report command
     */
    private function handleReportCommand(string $command, int $chatId, array $params): array
    {
        $reportType = $params['type'] ?? 'general';

        if (isset($this->reportGenerators[$reportType])) {
            return $this->reportGenerators[$reportType]->generate($chatId, $params);
        }

        return $this->menuBuilder->buildReportMenu($chatId);
    }

    /**
     * Check if action is menu action
     */
    private function isMenuAction(string $action): bool
    {
        return str_ends_with($action, '_menu') || $action === 'main_menu';
    }

    /**
     * Handle menu action
     */
    private function handleMenuAction(string $action, int $chatId): array
    {
        return match($action) {
            'main_menu' => $this->menuBuilder->buildMainMenu($chatId),
            'report_menu' => $this->menuBuilder->buildReportMenu($chatId),
            'services_menu' => $this->menuBuilder->buildServicesMenu($chatId),
            'products_menu' => $this->menuBuilder->buildProductsMenu($chatId),
            'dashboard_menu' => $this->menuBuilder->buildDashboardMenu($chatId),
            default => $this->menuBuilder->buildMainMenu($chatId)
        };
    }

    /**
     * Check if action is report action
     */
    private function isReportAction(string $action): bool
    {
        return str_starts_with($action, 'report_');
    }

    /**
     * Handle report action
     */
    private function handleReportAction(string $action, int $chatId, array $params): array
    {
        $reportType = str_replace('report_', '', $action);

        if (isset($this->reportGenerators[$reportType])) {
            return $this->reportGenerators[$reportType]->generate($chatId, $params);
        }

        return $this->menuBuilder->buildReportMenu($chatId);
    }

    /**
     * Check if action is period action
     */
    private function isPeriodAction(string $action): bool
    {
        return str_starts_with($action, 'period_');
    }

    /**
     * Handle period action
     */
    private function handlePeriodAction(string $action, int $chatId, array $params): array
    {
        $parts = explode(':', $action);
        $period = str_replace('period_', '', $parts[0]);
        $reportType = $parts[1] ?? 'general';

        $params['period'] = $period;

        if (isset($this->reportGenerators[$reportType])) {
            return $this->reportGenerators[$reportType]->generate($chatId, $params);
        }

        return $this->menuBuilder->buildMainMenu($chatId);
    }

    /**
     * Get available commands
     */
    public function getAvailableCommands(): array
    {
        $commands = [];

        foreach ($this->commandHandlers as $handler) {
            $commands[] = [
                'name' => $handler->getCommandName(),
                'description' => $handler->getCommandDescription()
            ];
        }

        return $commands;
    }

    /**
     * Get available reports
     */
    public function getAvailableReports(): array
    {
        $reports = [];

        foreach ($this->reportGenerators as $type => $generator) {
            $reports[] = [
                'type' => $generator->getReportType(),
                'name' => $generator->getReportName(),
                'periods' => $generator->getAvailablePeriods()
            ];
        }

        return $reports;
    }
}
