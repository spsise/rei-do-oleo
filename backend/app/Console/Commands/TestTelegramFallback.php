<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Telegram\Commands\UnifiedCommandSystem;
use App\Services\Channels\TelegramChannel;
use App\Services\TelegramBotService;

class TestTelegramFallback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-fallback {--chat-id= : Chat ID para testar} {--message= : Mensagem para testar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o sistema de fallback do Telegram para comandos não reconhecidos';

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

        $this->info("🧪 Testando fallback do Telegram...");
        $this->info("📱 Chat ID: {$chatId}");
        $this->info("💬 Mensagem: '{$testMessage}'");

        try {
            // Test 1: UnifiedCommandSystem fallback
            $this->info("\n🔍 Teste 1: UnifiedCommandSystem Fallback");
            $this->testUnifiedCommandSystemFallback($testMessage);

            // Test 2: TelegramBotService fallback
            $this->info("\n🤖 Teste 2: TelegramBotService Fallback");
            $this->testTelegramBotServiceFallback($chatId, $testMessage);

            // Test 3: Direct TelegramChannel message
            $this->info("\n📤 Teste 3: Envio Direto via TelegramChannel");
            $this->testDirectTelegramMessage($chatId, $testMessage);

            $this->info("\n✅ Todos os testes foram executados!");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro durante o teste: " . $e->getMessage());
            $this->error("📋 Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }

    private function testUnifiedCommandSystemFallback(string $testMessage): void
    {
        try {
            $commandSystem = app(UnifiedCommandSystem::class);
            $context = [
                'user_id' => 123,
                'type' => 'text',
                'timestamp' => time()
            ];

            $result = $commandSystem->processCommand($testMessage, $context);

            if ($result->isSuccess()) {
                $this->warn("⚠️  Comando foi reconhecido (não deveria ser)");
                $this->line("   Tipo: " . $result->getType());
                $this->line("   Dados: " . json_encode($result->getData()));

                // Debug: verificar o que foi retornado
                if ($result->getCommandMatch()) {
                    $command = $result->getCommandMatch()->getCommand();
                    $this->line("   Comando ID: " . $command->getId());
                    $this->line("   Aliases: " . json_encode($command->getAliases()));
                    $this->line("   Natural Language: " . json_encode($command->getNaturalLanguage()));
                }
            } else {
                $this->info("✅ Fallback funcionando corretamente");
                $this->line("   Tipo: " . $result->getType());

                $data = $result->getData();
                if (isset($data['fallback_message'])) {
                    $this->line("   Fallback: " . $data['fallback_message']);
                }
                if (isset($data['suggestions'])) {
                    $this->line("   Sugestões: " . json_encode($data['suggestions']));
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro no UnifiedCommandSystem: " . $e->getMessage());
        }
    }

    private function testTelegramBotServiceFallback(int $chatId, string $testMessage): void
    {
        try {
            $botService = app(TelegramBotService::class);

            $message = [
                'chat' => ['id' => $chatId],
                'from' => ['id' => 123],
                'text' => $testMessage
            ];

            $result = $botService->processMessage($message);

            if ($result['success']) {
                $this->warn("⚠️  Comando foi processado com sucesso (não deveria ser)");
                $this->line("   Tipo: " . $result['type']);
            } else {
                $this->info("✅ Fallback do TelegramBotService funcionando");
                $this->line("   Tipo: " . $result['type']);
                $this->line("   Mensagem: " . ($result['message'] ?? 'N/A'));

                if (isset($result['suggestions'])) {
                    $this->line("   Sugestões: " . json_encode($result['suggestions']));
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro no TelegramBotService: " . $e->getMessage());
        }
    }

    private function testDirectTelegramMessage(int $chatId, string $testMessage): void
    {
        try {
            $telegramChannel = app(TelegramChannel::class);

            $fallbackMessage = "🧪 **TESTE DE FALLBACK**\n\n";
            $fallbackMessage .= "❌ Comando não reconhecido: `{$testMessage}`\n\n";
            $fallbackMessage .= "💡 **Comandos disponíveis:**\n";
            $fallbackMessage .= "• `/start` - Iniciar o bot\n";
            $fallbackMessage .= "• `/help` - Ver ajuda\n";
            $fallbackMessage .= "• `/status` - Status do sistema\n";
            $fallbackMessage .= "• `/menu` - Menu principal\n\n";
            $fallbackMessage .= "🔄 **Tente novamente ou use um dos comandos acima!**";

            $result = $telegramChannel->sendTextMessage($fallbackMessage, (string) $chatId);

            if ($result['success']) {
                $this->info("✅ Mensagem enviada com sucesso via TelegramChannel");
                $this->line("   Message ID: " . ($result['message_id'] ?? 'N/A'));
            } else {
                $this->error("❌ Falha ao enviar mensagem: " . ($result['error'] ?? 'Erro desconhecido'));
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro no TelegramChannel: " . $e->getMessage());
        }
    }
}
