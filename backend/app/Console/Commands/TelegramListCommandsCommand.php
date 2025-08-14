<?php

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;

class TelegramListCommandsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:commands
                            {--detailed : Show detailed information about each command}
                            {--format=table : Output format (table, json, list)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available Telegram bot commands and features';

    private TelegramBotService $telegramBotService;

    public function __construct(TelegramBotService $telegramBotService)
    {
        parent::__construct();
        $this->telegramBotService = $telegramBotService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 Telegram Bot - Comandos Disponíveis');
        $this->info('=====================================');
        $this->info('');

        // Get available commands
        $commands = $this->telegramBotService->getAvailableCommands();
        $reports = $this->telegramBotService->getAvailableReports();

        $format = $this->option('format');
        $detailed = $this->option('detailed');

        if ($format === 'json') {
            $this->outputJson($commands, $reports);
        } elseif ($format === 'list') {
            $this->outputList($commands, $reports, $detailed);
        } else {
            $this->outputTable($commands, $reports, $detailed);
        }

        // Show usage examples
        $this->showUsageExamples();

        return 0;
    }

    /**
     * Output as JSON
     */
    private function outputJson(array $commands, array $reports): void
    {
        $data = [
            'commands' => $commands,
            'reports' => $reports,
            'generated_at' => now()->toISOString()
        ];

        $this->line(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Output as list
     */
    private function outputList(array $commands, array $reports, bool $detailed): void
    {
        $this->info('📋 COMANDOS DISPONÍVEIS:');
        $this->info('');

        foreach ($commands as $command) {
            $this->line("• {$command['name']} - {$command['description']}");
        }

        $this->info('');
        $this->info('📊 RELATÓRIOS DISPONÍVEIS:');
        $this->info('');

        foreach ($reports as $report) {
            $this->line("• {$report['name']} ({$report['type']})");
            if ($detailed && !empty($report['periods'])) {
                $periods = implode(', ', $report['periods']);
                $this->line("  Períodos: {$periods}");
            }
        }
    }

    /**
     * Output as table
     */
    private function outputTable(array $commands, array $reports, bool $detailed): void
    {
        // Commands table
        if (!empty($commands)) {
            $this->info('📋 COMANDOS DISPONÍVEIS:');
            $this->info('');

            $headers = ['Comando', 'Descrição'];
            if ($detailed) {
                $headers[] = 'Detalhes';
            }

            $rows = [];
            foreach ($commands as $command) {
                $row = [$command['name'], $command['description']];
                if ($detailed) {
                    $row[] = $this->getCommandDetails($command['name']);
                }
                $rows[] = $row;
            }

            $this->table($headers, $rows);
            $this->info('');
        }

        // Reports table
        if (!empty($reports)) {
            $this->info('📊 RELATÓRIOS DISPONÍVEIS:');
            $this->info('');

            $headers = ['Tipo', 'Nome', 'Períodos'];
            if ($detailed) {
                $headers[] = 'Descrição';
            }

            $rows = [];
            foreach ($reports as $report) {
                $row = [
                    $report['type'],
                    $report['name'],
                    implode(', ', $report['periods'] ?? [])
                ];
                if ($detailed) {
                    $row[] = $this->getReportDescription($report['type']);
                }
                $rows[] = $row;
            }

            $this->table($headers, $rows);
        }
    }

    /**
     * Get command details
     */
    private function getCommandDetails(string $commandName): string
    {
        return match($commandName) {
            'start' => 'Comando inicial, mostra menu principal',
            'menu' => 'Navegação pelos menus do sistema',
            'report' => 'Acesso aos relatórios disponíveis',
            'status' => 'Status atual do sistema',
            'voice' => 'Processamento de comandos de voz',
            default => 'Comando personalizado'
        };
    }

    /**
     * Get report description
     */
    private function getReportDescription(string $reportType): string
    {
        return match($reportType) {
            'general' => 'Relatório geral com visão completa do sistema',
            'services' => 'Relatório focado em serviços e performance',
            'products' => 'Relatório de produtos e estoque',
            default => 'Relatório personalizado'
        };
    }

    /**
     * Show usage examples
     */
    private function showUsageExamples(): void
    {
        $this->info('');
        $this->info('💡 EXEMPLOS DE USO:');
        $this->info('===================');
        $this->info('');

        $this->info('📱 Comandos de Texto:');
        $this->line('• /start - Iniciar bot e mostrar menu principal');
        $this->line('• /menu - Navegar pelos menus');
        $this->line('• /report - Acessar relatórios');
        $this->line('• /status - Ver status do sistema');
        $this->info('');

        $this->info('🎯 Linguagem Natural:');
        $this->line('• "Menu" → Mostra menu principal');
        $this->line('• "Relatório" → Menu de relatórios');
        $this->line('• "Serviços" → Menu de serviços');
        $this->line('• "Produtos" → Menu de produtos');
        $this->line('• "Dashboard" → Menu de dashboard');
        $this->info('');

        $this->info('📊 Relatórios com Períodos:');
        $this->line('• "Relatório de hoje" → Relatório diário');
        $this->line('• "Serviços da semana" → Relatório semanal');
        $this->line('• "Produtos do mês" → Relatório mensal');
        $this->info('');

        $this->info('🔧 Comandos Artisan Relacionados:');
        $this->line('• php artisan telegram:debug --get-updates');
        $this->line('• php artisan telegram:report --type=general --period=today');
        $this->line('• php artisan telegram:bot-setup --set-webhook');
        $this->info('');

        $this->info('📖 Para mais informações, consulte:');
        $this->line('• backend/docs/TELEGRAM_BOT_REPORTS.md');
        $this->line('• backend/docs/TELEGRAM_INTERACTIVE_MENUS.md');
    }
}
