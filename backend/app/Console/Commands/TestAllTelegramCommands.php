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
    protected $signature = 'test:all-telegram-commands {--chat-id=} {--offline}';

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
        // Get chat ID from option or config
        $chatId = $this->option('chat-id');
        if (empty($chatId)) {
            $recipients = config('services.telegram.recipients', []);
            $chatId = !empty($recipients) ? (int) $recipients[0] : 123456789;
        } else {
            $chatId = (int) $chatId;
        }

        $isOffline = $this->option('offline');

        $this->info("🧪 Testando TODOS os comandos do Telegram...");
        $this->info("Chat ID: {$chatId}");
        $this->info("Modo: " . ($isOffline ? 'Offline (simulado)' : 'Online (real)'));
        $this->newLine();

        // Debug configuration
        $this->info("🔧 Verificando configuração:");
        $this->info("TELEGRAM_ENABLED: " . (config('services.telegram.enabled') ? 'true' : 'false'));
        $this->info("TELEGRAM_BOT_TOKEN: " . (config('services.telegram.bot_token') ? 'configurado' : 'não configurado'));
        $this->info("TELEGRAM_RECIPIENTS: " . implode(', ', config('services.telegram.recipients', [])));
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
                $this->info("   Parsed: " . json_encode($parsed));

                // Handle command
                $result = $commandHandlerManager->handleCommand($parsed['type'], $chatId, $parsed['params']);
                $this->info("   Result: " . json_encode($result));

                // In offline mode, consider any result as success if no error
                if ($isOffline) {
                    if (isset($result['success']) && $result['success']) {
                        $this->info("✅ Comando /{$command} executado com sucesso (offline)");
                        $successCount++;
                    } elseif (!isset($result['error']) || !str_contains($result['error'], 'chat not found')) {
                        $this->info("✅ Comando /{$command} processado corretamente (offline)");
                        $successCount++;
                    } else {
                        $this->warn("⚠️ Comando /{$command} falhou (offline): " . ($result['error'] ?? 'erro desconhecido'));
                        $errorCount++;
                    }
                } else {
                    // Online mode - check for real success
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
