<?php

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;

class TestTelegramReportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-reports
                            {--chat-id= : Chat ID to test with}
                            {--type=all : Type of test (all, general, services, products)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram report generation with different commands';

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
        $chatId = $this->option('chat-id');
        $type = $this->option('type');

        if (!$chatId) {
            $this->error('❌ Chat ID é obrigatório! Use --chat-id=123456789');
            return 1;
        }

        $this->info('🧪 Testando Geração de Relatórios Telegram');
        $this->info('==========================================');
        $this->info("Chat ID: {$chatId}");
        $this->info("Tipo: {$type}");
        $this->info('');

        $testCases = $this->getTestCases($type);

        foreach ($testCases as $testCase) {
            $this->info("🔍 Testando: {$testCase['description']}");
            $this->info("Comando: {$testCase['command']}");
            $this->info('');

            try {
                $result = $this->telegramBotService->processMessage([
                    'chat' => ['id' => (int) $chatId],
                    'text' => $testCase['command'],
                    'from' => ['id' => 123, 'first_name' => 'Test User']
                ]);

                if (isset($result['ok']) && $result['ok']) {
                    $this->info('✅ Sucesso! Relatório enviado.');
                    if (isset($result['result']['text'])) {
                        $this->line('📝 Resposta: ' . substr($result['result']['text'], 0, 100) . '...');
                    }
                } else {
                    $this->warn('⚠️ Resposta inesperada: ' . json_encode($result));
                }
            } catch (\Exception $e) {
                $this->error('❌ Erro: ' . $e->getMessage());
            }

            $this->info('---');
            $this->info('');
        }

        $this->info('🎯 Teste concluído!');
        return 0;
    }

    /**
     * Get test cases based on type
     */
    private function getTestCases(string $type): array
    {
        $allTests = [
            [
                'description' => 'Relatório Geral - Hoje',
                'command' => 'Relatório de hoje'
            ],
            [
                'description' => 'Relatório Geral - Semana',
                'command' => 'Relatório da semana'
            ],
            [
                'description' => 'Relatório de Serviços - Hoje',
                'command' => 'Serviços de hoje'
            ],
            [
                'description' => 'Relatório de Serviços - Semana',
                'command' => 'Serviços da semana'
            ],
            [
                'description' => 'Relatório de Produtos - Hoje',
                'command' => 'Produtos de hoje'
            ],
            [
                'description' => 'Relatório de Produtos - Mês',
                'command' => 'Produtos do mês'
            ]
        ];

        if ($type === 'general') {
            return array_filter($allTests, fn($test) => str_contains($test['command'], 'Relatório'));
        } elseif ($type === 'services') {
            return array_filter($allTests, fn($test) => str_contains($test['command'], 'Serviços'));
        } elseif ($type === 'products') {
            return array_filter($allTests, fn($test) => str_contains($test['command'], 'Produtos'));
        }

        return $allTests;
    }
}
