<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Telegram\TelegramCommandHandlerManager;
use App\Services\Telegram\TelegramCommandParser;
use App\Services\SpeechToTextService;

class TestVoiceCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:voice-commands {--chat-id=123456789}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test hidden voice commands for Telegram bot';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chatId = (int) $this->option('chat-id');

        $this->info("🧪 Testando comandos de voz e texto...");
        $this->info("Chat ID: {$chatId}");
        $this->newLine();

        // Test basic commands
        $basicCommands = [
            'menu',
            'start',
            'ajuda',
            'help',
            'comandos'
        ];

        // Test hidden voice commands
        $hiddenCommands = [
            'testvoice',
            'enablevoice',
            'voice_status'
        ];

        $commandHandlerManager = app(TelegramCommandHandlerManager::class);
        $commandParser = app(TelegramCommandParser::class);

        $this->info("📝 Testando comandos básicos:");
        $this->newLine();

        foreach ($basicCommands as $command) {
            $this->info("🔍 Testando comando: '{$command}'");

            // Parse command
            $parsed = $commandParser->parseCommand($command);

            $this->info("   📋 Tipo detectado: {$parsed['type']}");
            $this->info("   📋 Parâmetros: " . json_encode($parsed['params']));

            // Handle command
            $result = $commandHandlerManager->handleCommand($parsed['type'], $chatId, $parsed['params']);

            if ($result['success'] ?? false) {
                $this->info("   ✅ Comando '{$command}' executado com sucesso");
            } else {
                $this->error("   ❌ Comando '{$command}' falhou");
                $this->error("   🚨 Erro: " . ($result['error'] ?? 'Erro desconhecido'));
            }

            $this->newLine();
        }

        $this->info("📝 Testando comandos ocultos:");
        $this->newLine();

        foreach ($hiddenCommands as $command) {
            $this->info("🔍 Testando comando: /{$command}");

            // Parse command
            $parsed = $commandParser->parseCommand("/{$command}");

            // Handle command
            $result = $commandHandlerManager->handleCommand($parsed['type'], $chatId, $parsed['params']);

            if ($result['success'] ?? false) {
                $this->info("   ✅ Comando /{$command} executado com sucesso");
            } else {
                $this->error("   ❌ Comando /{$command} falhou");
                $this->error("   🚨 Erro: " . ($result['error'] ?? 'Erro desconhecido'));
            }

            $this->newLine();
        }

        $this->info("🎉 Teste de comandos concluído!");

        return self::SUCCESS;
    }
}
