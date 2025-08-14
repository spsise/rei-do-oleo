<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramCommandParser;
use App\Services\Telegram\TelegramCommandHandlerManager;
use App\Services\Telegram\TelegramAuthorizationService;
use Illuminate\Console\Command;

class TelegramDebugAuthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:debug-auth
                            {text : Text to process}
                            {--chat-id=123456789 : Chat ID for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug Telegram authorization and complete flow step by step';

    private TelegramCommandParser $parser;
    private TelegramCommandHandlerManager $handlerManager;
    private TelegramAuthorizationService $authService;

    public function __construct(
        TelegramCommandParser $parser,
        TelegramCommandHandlerManager $handlerManager,
        TelegramAuthorizationService $authService
    ) {
        parent::__construct();
        $this->parser = $parser;
        $this->handlerManager = $handlerManager;
        $this->authService = $authService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $text = $this->argument('text');
        $chatId = (int) $this->option('chat-id');

        $this->info('🔍 Debug Completo - Autorização e Fluxo');
        $this->info('=======================================');
        $this->info("Texto de entrada: '{$text}'");
        $this->info("Chat ID: {$chatId}");
        $this->info('');

        try {
            // Step 1: Check authorization
            $this->info('📋 PASSO 1: VERIFICAÇÃO DE AUTORIZAÇÃO');
            $this->info('=======================================');

            $isAuthorized = $this->authService->isAuthorizedUser($chatId);
            $this->info("Usuário autorizado: " . ($isAuthorized ? '✅ Sim' : '❌ Não'));

            if (!$isAuthorized) {
                $this->error('❌ Usuário não autorizado! Processo interrompido.');
                return 1;
            }

            $this->info('');

            // Step 2: Parse command
            $this->info('📋 PASSO 2: PARSING DO COMANDO');
            $this->info('===============================');

            $parsedCommand = $this->parser->parseCommand($text);

            $this->table(
                ['Campo', 'Valor'],
                [
                    ['Tipo', $parsedCommand['type'] ?? 'N/A'],
                    ['Parâmetros', json_encode($parsedCommand['params'] ?? [], JSON_UNESCAPED_UNICODE)],
                ]
            );

            $this->info('');

            // Step 3: Check available handlers
            $this->info('📋 PASSO 3: HANDLERS DISPONÍVEIS');
            $this->info('=================================');

            $availableCommands = $this->handlerManager->getAvailableCommands();

            $this->table(
                ['Comando', 'Descrição'],
                array_map(fn($cmd) => [$cmd['name'], $cmd['description']], $availableCommands)
            );

            $this->info('');

            // Step 4: Process command
            $this->info('📋 PASSO 4: PROCESSAMENTO DO COMANDO');
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
                return 1;
            }

            $this->info('');

            // Step 5: Execute handler
            $this->info('📋 PASSO 5: EXECUÇÃO DO HANDLER');
            $this->info('================================');

            $result = $this->handlerManager->handleCommand($commandType, $chatId, $params);

            if (isset($result['success']) && $result['success']) {
                $this->info('✅ Handler executado com sucesso!');
                $this->info("📝 ID da mensagem: {$result['message_id']}");

                if (isset($result['response']['result']['text'])) {
                    $text = $result['response']['result']['text'];
                    $this->info('');
                    $this->info('📄 CONTEÚDO DA MENSAGEM:');
                    $this->info('========================');
                    $this->line($text);

                    // Check if it's the main menu
                    if (str_contains($text, 'Rei do Óleo - Bot de Relatórios')) {
                        $this->warn('');
                        $this->warn('⚠️ ATENÇÃO: Esta é a mensagem do menu principal!');
                        $this->warn('O comando deveria ter gerado um relatório específico.');
                        $this->warn('');

                        $this->info('🔍 INVESTIGANDO PROBLEMA...');
                        $this->info('==========================');
                        $this->info('O handler está sendo executado, mas retornando menu principal.');
                        $this->info('Isso pode indicar um problema no handler específico.');
                    }
                }
            } else {
                $this->warn('⚠️ Handler executado, mas com resultado inesperado');
                $this->line('📝 Resultado: ' . json_encode($result, JSON_UNESCAPED_UNICODE));
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
