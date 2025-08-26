<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Telegram\Commands\UnifiedCommandSystem;
use App\Services\Telegram\TelegramMessageProcessorService;
use App\Services\Channels\TelegramChannel;

class TestTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-webhook {--chat-id= : Chat ID para testar} {--message= : Mensagem para testar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o webhook do Telegram simulando uma mensagem real';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chatId = $this->option('chat-id');
        $testMessage = $this->option('message') ?? 'comando desconhecido';

        if (!$chatId) {
            $this->error('❌ Chat ID é obrigatório. Use --chat-id=123456789');
            return 1;
        }

        $this->info("🧪 Testando Webhook do Telegram...");
        $this->info("📱 Chat ID: {$chatId}");
        $this->info("💬 Mensagem: '{$testMessage}'");

        try {
            // Simular payload do webhook do Telegram
            $webhookPayload = [
                'update_id' => time(),
                'message' => [
                    'message_id' => time(),
                    'from' => [
                        'id' => 123456789,
                        'is_bot' => false,
                        'first_name' => 'Test',
                        'username' => 'testuser'
                    ],
                    'chat' => [
                        'id' => (int) $chatId,
                        'first_name' => 'Test',
                        'type' => 'private'
                    ],
                    'date' => time(),
                    'text' => $testMessage
                ]
            ];

            $this->info("\n📡 Payload do Webhook:");
            $this->line("   " . json_encode($webhookPayload, JSON_PRETTY_PRINT));

            // Test 1: TelegramMessageProcessorService (versão antiga)
            $this->info("\n🔧 Teste 1: TelegramMessageProcessorService (Versão Antiga)");
            $this->testOldMessageProcessor($webhookPayload);

            // Test 2: TelegramMessageProcessorService (versão nova)
            $this->info("\n🆕 Teste 2: TelegramMessageProcessorService (Versão Nova)");
            $this->testNewMessageProcessor($webhookPayload);

            // Test 3: UnifiedCommandSystem diretamente
            $this->info("\n🎯 Teste 3: UnifiedCommandSystem Direto");
            $this->testUnifiedCommandSystem($testMessage, $chatId);

            $this->info("\n✅ Todos os testes foram executados!");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro durante o teste: " . $e->getMessage());
            $this->error("📋 Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }

    private function testOldMessageProcessor(array $webhookPayload): void
    {
        try {
            $messageProcessor = app(\App\Services\TelegramMessageProcessorService::class);
            $result = $messageProcessor->processWebhookPayload($webhookPayload);

            $this->info("   Resultado: " . json_encode($result, JSON_PRETTY_PRINT));

            if (isset($result['type']) && $result['type'] === 'command_not_found') {
                $this->info("   ✅ Fallback detectado corretamente");
            } else {
                $this->warn("   ⚠️  Fallback não detectado");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Erro: " . $e->getMessage());
        }
    }

    private function testNewMessageProcessor(array $webhookPayload): void
    {
        try {
            $messageProcessor = app(\App\Services\Telegram\TelegramMessageProcessorService::class);
            $result = $messageProcessor->processMessage($webhookPayload);

            $this->info("   Resultado: " . json_encode($result, JSON_PRETTY_PRINT));

            if (isset($result['type']) && $result['type'] === 'command_not_found') {
                $this->info("   ✅ Fallback detectado corretamente");
            } else {
                $this->warn("   ⚠️  Fallback não detectado");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Erro: " . $e->getMessage());
        }
    }

    private function testUnifiedCommandSystem(string $testMessage, int $chatId): void
    {
        try {
            $commandSystem = app(UnifiedCommandSystem::class);
            $context = [
                'chat_id' => $chatId,
                'user_id' => 123456789,
                'type' => 'text',
                'timestamp' => time()
            ];

            $result = $commandSystem->processCommand($testMessage, $context);

            if ($result->isSuccess()) {
                $this->warn("   ⚠️  Comando foi reconhecido (não deveria ser)");
                $this->line("      Tipo: " . $result->getType());
            } else {
                $this->info("   ✅ Fallback funcionando corretamente");
                $this->line("      Tipo: " . $result->getType());

                $data = $result->getData();
                if (isset($data['fallback_message'])) {
                    $this->line("      Fallback: " . $data['fallback_message']);
                }
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Erro: " . $e->getMessage());
        }
    }
}
