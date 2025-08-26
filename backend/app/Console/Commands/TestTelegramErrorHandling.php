<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramBotService;
use App\Contracts\LoggingServiceInterface;

class TestTelegramErrorHandling extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'telegram:test-error-handling
                            {--chat-id= : Chat ID para enviar mensagens de teste}
                            {--error-type= : Tipo de erro para testar (validation, internal, timeout)}';

    /**
     * The console command description.
     */
    protected $description = 'Testa o sistema de tratamento de erros amigáveis do Telegram';

    /**
     * Execute the console command.
     */
    public function handle(TelegramBotService $telegramBotService, LoggingServiceInterface $loggingService): int
    {
        $chatId = $this->option('chat-id');
        $errorType = $this->option('error-type') ?? 'validation';

        if (!$chatId) {
            $this->error('❌ Chat ID é obrigatório. Use --chat-id=123456789');
            return 1;
        }

        $this->info("🧪 Testando tratamento de erros do Telegram...");
        $this->info("📱 Chat ID: {$chatId}");
        $this->info("🚨 Tipo de erro: {$errorType}");

        try {
            switch ($errorType) {
                case 'validation':
                    $this->testValidationError($telegramBotService, $chatId);
                    break;
                case 'internal':
                    $this->testInternalError($telegramBotService, $chatId);
                    break;
                case 'timeout':
                    $this->testTimeoutError($telegramBotService, $chatId);
                    break;
                case 'unauthorized':
                    $this->testUnauthorizedError($telegramBotService, $chatId);
                    break;
                case 'command_not_found':
                    $this->testCommandNotFoundError($telegramBotService, $chatId);
                    break;
                default:
                    $this->testAllErrorTypes($telegramBotService, $chatId);
                    break;
            }

            $this->info("✅ Teste concluído com sucesso!");
            $this->info("📱 Verifique o Telegram para ver as mensagens de erro amigáveis");

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro durante o teste: " . $e->getMessage());
            $loggingService->logException($e, [
                'operation' => 'test_telegram_error_handling',
                'chat_id' => $chatId,
                'error_type' => $errorType
            ]);
            return 1;
        }
    }

    /**
     * Test validation error
     */
    private function testValidationError(TelegramBotService $telegramBotService, int $chatId): void
    {
        $this->info("🔍 Testando erro de validação...");

        $result = $telegramBotService->sendGenericErrorMessage($chatId, 'validation_failed');

        if ($result['success']) {
            $this->info("✅ Mensagem de erro de validação enviada com sucesso");
        } else {
            $this->error("❌ Falha ao enviar mensagem de erro de validação");
        }
    }

    /**
     * Test internal error
     */
    private function testInternalError(TelegramBotService $telegramBotService, int $chatId): void
    {
        $this->info("💥 Testando erro interno...");

        $result = $telegramBotService->sendGenericErrorMessage($chatId, 'internal_error');

        if ($result['success']) {
            $this->info("✅ Mensagem de erro interno enviada com sucesso");
        } else {
            $this->error("❌ Falha ao enviar mensagem de erro interno");
        }
    }

    /**
     * Test timeout error
     */
    private function testTimeoutError(TelegramBotService $telegramBotService, int $chatId): void
    {
        $this->info("⏰ Testando erro de timeout...");

        $result = $telegramBotService->sendGenericErrorMessage($chatId, 'timeout');

        if ($result['success']) {
            $this->info("✅ Mensagem de erro de timeout enviada com sucesso");
        } else {
            $this->error("❌ Falha ao enviar mensagem de erro de timeout");
        }
    }

    /**
     * Test unauthorized error
     */
    private function testUnauthorizedError(TelegramBotService $telegramBotService, int $chatId): void
    {
        $this->info("🚫 Testando erro de não autorizado...");

        $result = $telegramBotService->sendGenericErrorMessage($chatId, 'unauthorized');

        if ($result['success']) {
            $this->info("✅ Mensagem de erro de não autorizado enviada com sucesso");
        } else {
            $this->error("❌ Falha ao enviar mensagem de erro de não autorizado");
        }
    }

    /**
     * Test command not found error
     */
    private function testCommandNotFoundError(TelegramBotService $telegramBotService, int $chatId): void
    {
        $this->info("🤔 Testando erro de comando não encontrado...");

        $result = $telegramBotService->sendGenericErrorMessage($chatId, 'command_not_found');

        if ($result['success']) {
            $this->info("✅ Mensagem de erro de comando não encontrado enviada com sucesso");
        } else {
            $this->error("❌ Falha ao enviar mensagem de erro de comando não encontrado");
        }
    }

    /**
     * Test all error types
     */
    private function testAllErrorTypes(TelegramBotService $telegramBotService, int $chatId): void
    {
        $this->info("🧪 Testando todos os tipos de erro...");

        $errorTypes = [
            'validation_failed' => 'Validação falhou',
            'unauthorized' => 'Não autorizado',
            'command_not_found' => 'Comando não encontrado',
            'timeout' => 'Timeout',
            'internal_error' => 'Erro interno',
            'webhook_error' => 'Erro de webhook',
            'unknown' => 'Erro desconhecido'
        ];

        foreach ($errorTypes as $type => $description) {
            $this->info("🔍 Testando: {$description}...");

            $result = $telegramBotService->sendGenericErrorMessage($chatId, $type);

            if ($result['success']) {
                $this->info("✅ {$description} - OK");
            } else {
                $this->error("❌ {$description} - FALHOU");
            }

            // Wait a bit between messages
            sleep(2);
        }
    }
}
