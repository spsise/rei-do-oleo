<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Telegram\TelegramCommandHandlerManager;
use App\Services\Telegram\TelegramCommandParser;

class TestAllTelegramCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:all-telegram-commands {--chat-id=123456789}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all Telegram bot commands including hidden voice commands';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chatId = (int) $this->option('chat-id');

        $this->info("🧪 Testando TODOS os comandos do Telegram...");
        $this->info("Chat ID: {$chatId}");
        $this->newLine();

        // Test all commands
        $commands = [
            // Standard commands
            'start',
            'menu',
            'services',
            'products',
            'dashboard',
            'report',
            'status',

            // Hidden voice commands
            'testvoice',
            'enablevoice',
            'voice_status',

            // Unknown command
            'unknown_command'
        ];

        $commandHandlerManager = app(TelegramCommandHandlerManager::class);
        $commandParser = app(TelegramCommandParser::class);

        $successCount = 0;
        $errorCount = 0;

        foreach ($commands as $command) {
            $this->info("📝 Testando comando: /{$command}");

            try {
                // Parse command
                $parsed = $commandParser->parseCommand("/{$command}");

                // Handle command
                $result = $commandHandlerManager->handleCommand($parsed['type'], $chatId, $parsed['params']);

                if (isset($result['success']) && $result['success']) {
                    $this->info("✅ Comando /{$command} executado com sucesso");
                    $successCount++;
                } elseif (isset($result['message_id']) || isset($result['response'])) {
                    $this->info("✅ Comando /{$command} retornou resposta do Telegram");
                    $successCount++;
                } else {
                    $this->warn("⚠️ Comando /{$command} não retornou resultado esperado");
                    $errorCount++;
                }

            } catch (\Exception $e) {
                $this->error("❌ Comando /{$command} falhou com erro: " . $e->getMessage());
                $errorCount++;
            }

            $this->newLine();
        }

        $this->info("📊 Resumo dos Testes:");
        $this->info("✅ Comandos com sucesso: {$successCount}");
        $this->info("❌ Comandos com erro: {$errorCount}");
        $this->info("📝 Total de comandos testados: " . count($commands));

        if ($errorCount === 0) {
            $this->info("🎉 Todos os comandos estão funcionando corretamente!");
            return self::SUCCESS;
        } else {
            $this->warn("⚠️ Alguns comandos apresentaram problemas.");
            return self::FAILURE;
        }
    }
}
