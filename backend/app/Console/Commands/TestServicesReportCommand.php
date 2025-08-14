<?php

namespace App\Console\Commands;

use App\Services\Telegram\Reports\ServicesReportGenerator;
use Illuminate\Console\Command;

class TestServicesReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-services-report
                            {--chat-id= : Chat ID to test with}
                            {--period=today : Period to test (today, week, month)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test ServicesReportGenerator directly';

    private ServicesReportGenerator $servicesReportGenerator;

    public function __construct(ServicesReportGenerator $servicesReportGenerator)
    {
        parent::__construct();
        $this->servicesReportGenerator = $servicesReportGenerator;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chatId = $this->option('chat-id');
        $period = $this->option('period');

        if (!$chatId) {
            $this->error('❌ Chat ID é obrigatório! Use --chat-id=123456789');
            return 1;
        }

        $this->info('🧪 Testando ServicesReportGenerator');
        $this->info('==================================');
        $this->info("Chat ID: {$chatId}");
        $this->info("Período: {$period}");
        $this->info('');

        try {
            $this->info('🔍 Gerando relatório...');

            $result = $this->servicesReportGenerator->generate((int) $chatId, ['period' => $period]);

            $this->info('✅ Relatório gerado!');
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
                }
            } else {
                $this->warn('⚠️ Sucesso: Não');
                $this->line('📝 Resultado: ' . json_encode($result, JSON_UNESCAPED_UNICODE));
            }

        } catch (\Exception $e) {
            $this->error('❌ Erro ao gerar relatório: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        $this->info('');
        $this->info('🎯 Teste concluído!');
        return 0;
    }
}
