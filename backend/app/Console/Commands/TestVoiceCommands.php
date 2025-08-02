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

        $this->info("🧪 Testando comandos ocultos de voz...");
        $this->info("Chat ID: {$chatId}");
        $this->newLine();

        // Test commands
        $commands = [
            'testvoice',
            'enablevoice',
            'voice_status'
        ];

        $commandHandlerManager = app(TelegramCommandHandlerManager::class);
        $commandParser = app(TelegramCommandParser::class);

        foreach ($commands as $command) {
            $this->info("📝 Testando comando: /{$command}");

            // Parse command
            $parsed = $commandParser->parseCommand("/{$command}");

            // Handle command
            $result = $commandHandlerManager->handleCommand($parsed['type'], $chatId, $parsed['params']);

            if ($result['success'] ?? false) {
                $this->info("✅ Comando /{$command} executado com sucesso");
            } else {
                $this->error("❌ Comando /{$command} falhou");
                $this->error("Erro: " . ($result['error'] ?? 'Erro desconhecido'));
            }

            $this->newLine();
        }

        $this->info("🎉 Teste de comandos ocultos concluído!");

        return self::SUCCESS;
    }
}
