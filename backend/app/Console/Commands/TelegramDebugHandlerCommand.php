<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramCommandHandlerManager;
use App\Services\Telegram\TelegramCommandParser;
use Illuminate\Console\Command;

class TelegramDebugHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:debug-handler
                            {text : Text to process}
                            {--chat-id=123456789 : Chat ID for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug Telegram command handler processing';

    private TelegramCommandParser $parser;
    private TelegramCommandHandlerManager $handlerManager;

    public function __construct(
        TelegramCommandParser $parser,
        TelegramCommandHandlerManager $handlerManager
    ) {
        parent::__construct();
        $this->parser = $parser;
        $this->handlerManager = $handlerManager;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $text = $this->argument('text');
        $chatId = (int) $this->option('chat-id');

        $this->info('🔍 Debug do Handler de Comandos Telegram');
        $this->info('========================================');
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

            // Step 3: Process command
            $this->info('');
            $this->info('📋 PASSO 3: PROCESSAMENTO DO COMANDO');
            $this->info('=====================================');

            $commandType = $parsedCommand['type'];
            $params = $parsedCommand['params'] ?? [];

            $this->info("Tipo de comando: {$commandType}");
            $this->info("Parâmetros: " . json_encode($params, JSON_UNESCAPED_UNICODE));
            $this->info('');

            // Check if handler exists
            $handlerFound = false;
            foreach ($availableCommands as $cmd) {
                if ($cmd['name'] === $commandType) {
                    $handlerFound = true;
                    $this->info("✅ Handler encontrado: {$cmd['name']} - {$cmd['description']}");
                    break;
                }
            }

            if (!$handlerFound) {
                $this->warn("⚠️ Nenhum handler encontrado para o comando '{$commandType}'");
                $this->info("📋 Comandos disponíveis: " . implode(', ', array_column($availableCommands, 'name')));
            }

            // Step 4: Try to handle command
            $this->info('');
            $this->info('📋 PASSO 4: TENTATIVA DE EXECUÇÃO');
            $this->info('==================================');

            try {
                $result = $this->handlerManager->handleCommand($commandType, $chatId, $params);

                if (isset($result['success']) && $result['success']) {
                    $this->info('✅ Comando executado com sucesso!');
                    if (isset($result['message_id'])) {
                        $this->info("📝 ID da mensagem: {$result['message_id']}");
                    }
                } else {
                    $this->warn('⚠️ Comando executado, mas com resultado inesperado');
                    $this->line('📝 Resultado: ' . json_encode($result, JSON_UNESCAPED_UNICODE));
                }
            } catch (\Exception $e) {
                $this->error('❌ Erro ao executar comando: ' . $e->getMessage());
                $this->error('Stack trace: ' . $e->getTraceAsString());
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
