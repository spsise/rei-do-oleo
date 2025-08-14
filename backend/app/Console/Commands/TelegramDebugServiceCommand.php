<?php

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;

class TelegramDebugServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:debug-service
                            {text : Text to process}
                            {--chat-id=123456789 : Chat ID for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug TelegramBotService complete flow';

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
        $text = $this->argument('text');
        $chatId = (int) $this->option('chat-id');

        $this->info('🔍 Debug do TelegramBotService Completo');
        $this->info('======================================');
        $this->info("Texto de entrada: '{$text}'");
        $this->info("Chat ID: {$chatId}");
        $this->info('');

        try {
            $this->info('📋 PASSO 1: PREPARANDO MENSAGEM');
            $this->info('===============================');

            $message = [
                'chat' => ['id' => $chatId],
                'text' => $text,
                'from' => ['id' => 123, 'first_name' => 'Test User']
            ];

            $this->info('Mensagem preparada: ' . json_encode($message, JSON_UNESCAPED_UNICODE));
            $this->info('');

            $this->info('📋 PASSO 2: PROCESSANDO MENSAGEM');
            $this->info('================================');

            $result = $this->telegramBotService->processMessage($message);

            $this->info('✅ Mensagem processada!');
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
                        $this->warn('⚠️ ATENÇÃO: Esta é a mensagem do menu principal!');
                        $this->warn('O comando deveria ter gerado um relatório específico.');
                        $this->warn('');
                    }
                }
            } else {
                $this->warn('⚠️ Sucesso: Não');
                $this->line('📝 Resultado: ' . json_encode($result, JSON_UNESCAPED_UNICODE));
            }

        } catch (\Exception $e) {
            $this->error('❌ Erro ao processar mensagem: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        $this->info('');
        $this->info('🎯 Debug concluído!');
        return 0;
    }
}
