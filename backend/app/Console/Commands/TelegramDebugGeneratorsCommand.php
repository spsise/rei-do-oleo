<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramCommandHandlerManager;
use App\Services\Telegram\Reports\ServicesReportGenerator;
use App\Services\Telegram\Reports\ProductsReportGenerator;
use App\Services\Telegram\Reports\GeneralReportGenerator;
use Illuminate\Console\Command;

class TelegramDebugGeneratorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:debug-generators
                            {--chat-id=123456789 : Chat ID for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug report generators and their dependencies';

    private TelegramCommandHandlerManager $handlerManager;
    private ServicesReportGenerator $servicesReportGenerator;
    private ProductsReportGenerator $productsReportGenerator;
    private GeneralReportGenerator $generalReportGenerator;

    public function __construct(
        TelegramCommandHandlerManager $handlerManager,
        ServicesReportGenerator $servicesReportGenerator,
        ProductsReportGenerator $productsReportGenerator,
        GeneralReportGenerator $generalReportGenerator
    ) {
        parent::__construct();
        $this->handlerManager = $handlerManager;
        $this->servicesReportGenerator = $servicesReportGenerator;
        $this->productsReportGenerator = $productsReportGenerator;
        $this->generalReportGenerator = $generalReportGenerator;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chatId = (int) $this->option('chat-id');

        $this->info('🔍 Debug dos Geradores de Relatório');
        $this->info('==================================');
        $this->info("Chat ID: {$chatId}");
        $this->info('');

        try {
            // Step 1: Check report generators
            $this->info('📋 PASSO 1: GERADORES DE RELATÓRIO');
            $this->info('====================================');

            $this->info("ServicesReportGenerator: " . get_class($this->servicesReportGenerator));
            $this->info("ProductsReportGenerator: " . get_class($this->productsReportGenerator));
            $this->info("GeneralReportGenerator: " . get_class($this->generalReportGenerator));
            $this->info('');

            // Step 2: Test ServicesReportGenerator directly
            $this->info('📋 PASSO 2: TESTE DIRETO DO SERVICESREPORTGENERATOR');
            $this->info('==================================================');

            $this->info('🔍 Gerando relatório de serviços...');
            $result = $this->servicesReportGenerator->generate($chatId, ['period' => 'today']);

            if (isset($result['success']) && $result['success']) {
                $this->info('✅ Relatório gerado com sucesso!');
                $this->info("📝 ID da mensagem: {$result['message_id']}");

                if (isset($result['response']['result']['text'])) {
                    $text = $result['response']['result']['text'];
                    $this->info('');
                    $this->info('📄 CONTEÚDO:');
                    $this->info('============');
                    $this->line($text);

                    $isMenu = str_contains($text, 'Rei do Óleo - Bot de Relatórios');
                    $this->info('');
                    $this->info("Resultado: " . ($isMenu ? '❌ Menu principal' : '✅ Relatório específico'));
                }
            } else {
                $this->warn('⚠️ Relatório não gerado com sucesso');
                $this->line('📝 Resultado: ' . json_encode($result, JSON_UNESCAPED_UNICODE));
            }

            $this->info('');

            // Step 3: Check available reports in manager
            $this->info('📋 PASSO 3: RELATÓRIOS DISPONÍVEIS NO MANAGER');
            $this->info('================================================');

            $availableReports = $this->handlerManager->getAvailableReports();

            $this->table(
                ['Tipo', 'Nome', 'Períodos'],
                array_map(fn($report) => [
                    $report['type'],
                    $report['name'],
                    implode(', ', $report['periods'] ?? [])
                ], $availableReports)
            );

            $this->info('');

            // Step 4: Test handler creation
            $this->info('📋 PASSO 4: TESTE DE CRIAÇÃO DE HANDLER');
            $this->info('========================================');

            $this->info('🔍 Criando ServicesCommandHandler...');

            try {
                $handler = new \App\Services\Telegram\Handlers\ServicesCommandHandler($this->servicesReportGenerator);

                $this->info('✅ Handler criado com sucesso!');
                $this->info("Nome: {$handler->getCommandName()}");
                $this->info("Descrição: {$handler->getCommandDescription()}");
                $this->info("Pode lidar com 'services': " . ($handler->canHandle('services') ? '✅ Sim' : '❌ Não'));

                $this->info('');
                $this->info('🔍 Testando handler recém-criado...');
                $testResult = $handler->handle($chatId, ['period' => 'today']);

                if (isset($testResult['success']) && $testResult['success']) {
                    $this->info('✅ Handler funcionando!');
                    if (isset($testResult['response']['result']['text'])) {
                        $text = $testResult['response']['result']['text'];
                        $isMenu = str_contains($text, 'Rei do Óleo - Bot de Relatórios');
                        $this->info("Resultado: " . ($isMenu ? '❌ Menu principal' : '✅ Relatório específico'));
                    }
                }

            } catch (\Exception $e) {
                $this->error('❌ Erro ao criar handler: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            $this->error('❌ Erro geral: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        $this->info('');
        $this->info('🎯 Debug concluído!');
        return 0;
    }
}
