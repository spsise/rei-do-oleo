<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramCommandHandlerManager;
use App\Services\Telegram\Handlers\ServicesCommandHandler;
use Illuminate\Console\Command;

class TelegramDebugInstancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:debug-instances
                            {--chat-id=123456789 : Chat ID for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug handler instances in TelegramCommandHandlerManager';

    private TelegramCommandHandlerManager $handlerManager;
    private ServicesCommandHandler $servicesHandler;

    public function __construct(
        TelegramCommandHandlerManager $handlerManager,
        ServicesCommandHandler $servicesHandler
    ) {
        parent::__construct();
        $this->handlerManager = $handlerManager;
        $this->servicesHandler = $servicesHandler;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chatId = (int) $this->option('chat-id');

        $this->info('🔍 Debug das Instâncias dos Handlers');
        $this->info('====================================');
        $this->info("Chat ID: {$chatId}");
        $this->info('');

        try {
            // Step 1: Check direct ServicesCommandHandler
            $this->info('📋 PASSO 1: SERVICESCOMMANDHANDLER DIRETO');
            $this->info('==========================================');

            $this->info("Nome do comando: {$this->servicesHandler->getCommandName()}");
            $this->info("Descrição: {$this->servicesHandler->getCommandDescription()}");
            $this->info("Pode lidar com 'services': " . ($this->servicesHandler->canHandle('services') ? '✅ Sim' : '❌ Não'));
            $this->info('');

            // Step 2: Check handler in manager
            $this->info('📋 PASSO 2: HANDLERS NO MANAGER');
            $this->info('================================');

            $availableCommands = $this->handlerManager->getAvailableCommands();

            $this->table(
                ['Comando', 'Descrição'],
                array_map(fn($cmd) => [$cmd['name'], $cmd['description']], $availableCommands)
            );

            $this->info('');

            // Step 3: Find services handler in manager
            $this->info('📋 PASSO 3: PROCURANDO HANDLER DE SERVIÇOS');
            $this->info('============================================');

            $servicesHandlerFound = false;
            foreach ($availableCommands as $cmd) {
                if ($cmd['name'] === 'services') {
                    $servicesHandlerFound = true;
                    $this->info("✅ Handler de serviços encontrado: {$cmd['name']} - {$cmd['description']}");
                    break;
                }
            }

            if (!$servicesHandlerFound) {
                $this->error('❌ Handler de serviços NÃO encontrado no manager!');
                return 1;
            }

            $this->info('');

            // Step 4: Test direct execution vs manager execution
            $this->info('📋 PASSO 4: COMPARAÇÃO DE EXECUÇÃO');
            $this->info('==================================');

            $params = ['period' => 'today'];

            // Test direct execution
            $this->info('🔍 Testando execução direta...');
            $directResult = $this->servicesHandler->handle($chatId, $params);

            if (isset($directResult['response']['result']['text'])) {
                $directText = $directResult['response']['result']['text'];
                $isDirectMenu = str_contains($directText, 'Rei do Óleo - Bot de Relatórios');
                $this->info("Execução direta → " . ($isDirectMenu ? '❌ Menu principal' : '✅ Relatório específico'));
            }

            $this->info('');

            // Test manager execution
            $this->info('🔍 Testando execução via manager...');
            $managerResult = $this->handlerManager->handleCommand('services', $chatId, $params);

            if (isset($managerResult['response']['result']['text'])) {
                $managerText = $managerResult['response']['result']['text'];
                $isManagerMenu = str_contains($managerText, 'Rei do Óleo - Bot de Relatórios');
                $this->info("Execução via manager → " . ($isManagerMenu ? '❌ Menu principal' : '✅ Relatório específico'));
            }

            $this->info('');

            // Step 5: Analysis
            $this->info('📋 PASSO 5: ANÁLISE');
            $this->info('====================');

            if (isset($directResult['response']['result']['text']) && isset($managerResult['response']['result']['text'])) {
                $directText = $directResult['response']['result']['text'];
                $managerText = $managerResult['response']['result']['text'];

                if ($directText === $managerText) {
                    $this->info('✅ Resultados idênticos - Handler funcionando corretamente');
                } else {
                    $this->warn('⚠️ Resultados diferentes!');
                    $this->warn('Execução direta: ' . substr($directText, 0, 50) . '...');
                    $this->warn('Execução via manager: ' . substr($managerText, 0, 50) . '...');
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        $this->info('');
        $this->info('🎯 Debug concluído!');
        return 0;
    }
}
