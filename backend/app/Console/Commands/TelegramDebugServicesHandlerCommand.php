<?php

namespace App\Console\Commands;

use App\Services\Telegram\Handlers\ServicesCommandHandler;
use Illuminate\Console\Command;

class TelegramDebugServicesHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:debug-services-handler
                            {--chat-id=123456789 : Chat ID for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug ServicesCommandHandler directly';

    private ServicesCommandHandler $servicesHandler;

    public function __construct(ServicesCommandHandler $servicesHandler)
    {
        parent::__construct();
        $this->servicesHandler = $servicesHandler;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chatId = (int) $this->option('chat-id');

        $this->info('🔍 Debug do ServicesCommandHandler');
        $this->info('==================================');
        $this->info("Chat ID: {$chatId}");
        $this->info('');

        try {
            // Step 1: Check handler info
            $this->info('📋 PASSO 1: INFORMAÇÕES DO HANDLER');
            $this->info('====================================');

            $this->info("Nome do comando: {$this->servicesHandler->getCommandName()}");
            $this->info("Descrição: {$this->servicesHandler->getCommandDescription()}");
            $this->info('');

            // Step 2: Test canHandle method
            $this->info('📋 PASSO 2: TESTE DO MÉTODO canHandle');
            $this->info('========================================');

            $testCommands = ['services', 'report', 'start', 'menu'];

            foreach ($testCommands as $command) {
                $canHandle = $this->servicesHandler->canHandle($command);
                $this->info("'{$command}' → " . ($canHandle ? '✅ Sim' : '❌ Não'));
            }

            $this->info('');

            // Step 3: Test handler execution
            $this->info('📋 PASSO 3: TESTE DE EXECUÇÃO');
            $this->info('==============================');

            $params = ['period' => 'today'];
            $this->info("Parâmetros: " . json_encode($params, JSON_UNESCAPED_UNICODE));
            $this->info('');

            $this->info('🔍 Executando handler...');
            $result = $this->servicesHandler->handle($chatId, $params);

            $this->info('✅ Handler executado!');
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
                        $this->warn('⚠️ PROBLEMA IDENTIFICADO!');
                        $this->warn('O ServicesCommandHandler está retornando o menu principal!');
                        $this->warn('');
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
            $this->error('❌ Erro ao executar handler: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        $this->info('');
        $this->info('🎯 Debug concluído!');
        return 0;
    }
}
