<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramCommandHandlerManager;
use Illuminate\Console\Command;

class TelegramTestSimpleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-simple
                            {--chat-id=123456789 : Chat ID for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simple test to check if the problem is in dependency injection';

    private TelegramCommandHandlerManager $handlerManager;

    public function __construct(TelegramCommandHandlerManager $handlerManager)
    {
        parent::__construct();
        $this->handlerManager = $handlerManager;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chatId = (int) $this->option('chat-id');

        $this->info('🧪 Teste Simples - Injeção de Dependências');
        $this->info('==========================================');
        $this->info("Chat ID: {$chatId}");
        $this->info('');

        try {
            // Step 1: Check available commands
            $this->info('📋 PASSO 1: COMANDOS DISPONÍVEIS');
            $this->info('==================================');

            $availableCommands = $this->handlerManager->getAvailableCommands();

            $this->table(
                ['Comando', 'Descrição'],
                array_map(fn($cmd) => [$cmd['name'], $cmd['description']], $availableCommands)
            );

            $this->info('');

            // Step 2: Test services command directly
            $this->info('📋 PASSO 2: TESTE DIRETO DO COMANDO SERVICES');
            $this->info('=============================================');

            $this->info('🔍 Executando comando "services" com parâmetros...');
            $result = $this->handlerManager->handleCommand('services', $chatId, ['period' => 'today']);

            $this->info('✅ Comando executado!');
            $this->info('');

            $this->info('📋 RESULTADO:');
            $this->info('============');

            if (isset($result['success']) && $result['success']) {
                $this->info('✅ Sucesso: Sim');
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
                        $this->warn('⚠️ PROBLEMA CONFIRMADO!');
                        $this->warn('O comando "services" está retornando o menu principal!');
                        $this->warn('');

                        $this->info('🔍 INVESTIGANDO...');
                        $this->info('================');
                        $this->info('1. O comando está sendo encontrado no manager');
                        $this->info('2. O handler está sendo executado');
                        $this->info('3. Mas está retornando menu principal em vez de relatório');
                        $this->info('4. Isso indica um problema no handler específico');
                        $this->info('');

                        $this->info('💡 POSSÍVEIS CAUSAS:');
                        $this->info('====================');
                        $this->info('• Handler não está sendo injetado corretamente');
                        $this->info('• Dependências do handler estão com problema');
                        $this->info('• Handler está sendo sobrescrito em algum lugar');
                        $this->info('• Problema na configuração do Laravel');
                    } else {
                        $this->info('');
                        $this->info('✅ SUCESSO! Relatório específico gerado.');
                    }
                }
            } else {
                $this->warn('⚠️ Sucesso: Não');
                $this->line('📝 Resultado: ' . json_encode($result, JSON_UNESCAPED_UNICODE));
            }

        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        $this->info('');
        $this->info('🎯 Teste concluído!');
        return 0;
    }
}
