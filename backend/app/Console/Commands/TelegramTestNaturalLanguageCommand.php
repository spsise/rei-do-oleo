<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramCommandParser;
use App\Services\Telegram\TelegramCommandHandlerManager;
use App\Services\Telegram\TelegramMenuBuilder;
use Illuminate\Console\Command;

class TelegramTestNaturalLanguageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-natural-language
                            {text : Text to test}
                            {--chat-id=123 : Chat ID for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test natural language processing for Telegram bot';

    private TelegramCommandParser $parser;
    private TelegramCommandHandlerManager $handlerManager;
    private TelegramMenuBuilder $menuBuilder;

    public function __construct(
        TelegramCommandParser $parser,
        TelegramCommandHandlerManager $handlerManager,
        TelegramMenuBuilder $menuBuilder
    ) {
        parent::__construct();
        $this->parser = $parser;
        $this->handlerManager = $handlerManager;
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $text = $this->argument('text');
        $chatId = (int) $this->option('chat-id');

        $this->info('🧪 Teste de Linguagem Natural - Bot Telegram');
        $this->info('============================================');
        $this->info("Texto de entrada: '{$text}'");
        $this->info("Chat ID: {$chatId}");
        $this->info('');

        try {
            // Step 1: Parse command
            $this->info('📋 PASSO 1: PARSING DO COMANDO');
            $this->info('===============================');

            $parsedCommand = $this->parser->parseCommand($text);

            $this->table(
                ['Campo', 'Valor'],
                [
                    ['Tipo', $parsedCommand['type'] ?? 'N/A'],
                    ['Parâmetros', json_encode($parsedCommand['params'] ?? [], JSON_UNESCAPED_UNICODE)],
                ]
            );

            // Step 2: Check available handlers
            $this->info('');
            $this->info('📋 PASSO 2: HANDLERS DISPONÍVEIS');
            $this->info('=================================');

            $availableCommands = $this->handlerManager->getAvailableCommands();

            $this->table(
                ['Comando', 'Descrição'],
                array_map(fn($cmd) => [$cmd['name'], $cmd['description']], $availableCommands)
            );

            // Step 3: Find handler
            $this->info('');
            $this->info('📋 PASSO 3: BUSCA DO HANDLER');
            $this->info('=============================');

            $commandType = $parsedCommand['type'];
            $params = $parsedCommand['params'] ?? [];

            $this->info("Tipo de comando: {$commandType}");
            $this->info("Parâmetros: " . json_encode($params, JSON_UNESCAPED_UNICODE));
            $this->info('');

            // Find handler
            $handlerFound = false;
            foreach ($availableCommands as $cmd) {
                if ($cmd['name'] === $commandType) {
                    $this->info("✅ Handler encontrado: {$cmd['name']} - {$cmd['description']}");
                    $handlerFound = true;
                    break;
                }
            }

            if (!$handlerFound) {
                $this->warn("⚠️ Handler não encontrado para o comando: {$commandType}");
            }

            // Step 4: Simulate menu building
            $this->info('');
            $this->info('📋 PASSO 4: SIMULAÇÃO DO MENU');
            $this->info('===============================');

            if ($commandType === 'menu' && isset($params['menu_type'])) {
                $menuType = $params['menu_type'];
                $this->info("Tipo de menu solicitado: {$menuType}");

                switch ($menuType) {
                    case 'report':
                        $this->info('🎯 Resultado esperado: Menu de Relatórios');
                        $this->info('📊 Opções: Relatório Geral, Relatório de Serviços, Relatório de Produtos, Dashboard Completo');
                        break;
                    case 'services':
                        $this->info('🎯 Resultado esperado: Menu de Serviços');
                        $this->info('🔧 Opções: Status Atual, Performance');
                        break;
                    case 'products':
                        $this->info('🎯 Resultado esperado: Menu de Produtos');
                        $this->info('📦 Opções: Status do Estoque, Estoque Baixo');
                        break;
                    case 'dashboard':
                        $this->info('🎯 Resultado esperado: Menu Dashboard');
                        $this->info('📈 Opções: Hoje, Esta Semana, Este Mês');
                        break;
                    case 'main':
                        $this->info('🎯 Resultado esperado: Menu Principal');
                        $this->info('🏠 Opções: Relatórios, Serviços, Produtos, Dashboard, Status do Sistema');
                        break;
                    default:
                        $this->warn("⚠️ Tipo de menu desconhecido: {$menuType}");
                }
            } else {
                $this->info("Comando não é de menu ou não tem tipo especificado");
            }

            $this->info('');
            $this->info('✅ Teste concluído com sucesso!');

        } catch (\Exception $e) {
            $this->error('❌ Erro no teste: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
