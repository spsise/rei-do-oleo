<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramCommandParser;
use Illuminate\Console\Command;

class TelegramDebugParserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:debug-parser
                            {text : Text to parse}
                            {--detailed : Show detailed analysis}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug Telegram command parser with specific text';

    private TelegramCommandParser $parser;

    public function __construct(TelegramCommandParser $parser)
    {
        parent::__construct();
        $this->parser = $parser;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $text = $this->argument('text');
        $detailed = $this->option('detailed');

        $this->info('🔍 Debug do Parser de Comandos Telegram');
        $this->info('=====================================');
        $this->info("Texto de entrada: '{$text}'");
        $this->info('');

        try {
            $result = $this->parser->parseCommand($text);

            $this->info('📋 RESULTADO DO PARSER:');
            $this->info('======================');
            $this->info('');

            $this->table(
                ['Campo', 'Valor'],
                [
                    ['Tipo', $result['type'] ?? 'N/A'],
                    ['Parâmetros', json_encode($result['params'] ?? [], JSON_UNESCAPED_UNICODE)],
                    ['Comando Original', $result['params']['command'] ?? 'N/A'],
                    ['Período', $result['params']['period'] ?? 'N/A'],
                ]
            );

            if ($detailed) {
                $this->info('');
                $this->info('🔍 ANÁLISE DETALHADA:');
                $this->info('====================');
                $this->info('');

                $this->analyzeParsing($text, $result);
            }

            $this->info('');
            $this->info('✅ Parser executado com sucesso!');

        } catch (\Exception $e) {
            $this->error('❌ Erro no parser: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    /**
     * Analyze parsing result
     */
    private function analyzeParsing(string $text, array $result): void
    {
        $type = $result['type'] ?? 'unknown';
        $params = $result['params'] ?? [];

        $this->info("📝 Tipo detectado: {$type}");

        if (isset($params['period'])) {
            $this->info("📅 Período detectado: {$params['period']}");
        }

        if (isset($params['menu_type'])) {
            $this->info("🎯 Tipo de menu: {$params['menu_type']}");
        }

        // Analyze what should happen next
        $this->info('');
        $this->info('🎯 PRÓXIMOS PASSOS:');
        $this->info('==================');

        switch ($type) {
            case 'start':
                $this->info('• Comando será tratado por StartCommandHandler');
                $this->info('• Resultado: Menu principal');
                break;

            case 'menu':
                $this->info('• Comando será tratado por MenuCommandHandler');
                $this->info('• Resultado: Menu específico baseado em menu_type');
                break;

            case 'report':
                $this->info('• Comando será tratado por ReportCommandHandler');
                if (isset($params['period'])) {
                    $this->info('• Resultado: Relatório geral com período específico');
                } else {
                    $this->info('• Resultado: Menu de relatórios');
                }
                break;

            case 'services':
                $this->info('• Comando será tratado por ServicesCommandHandler');
                $this->info('• Resultado: Relatório de serviços');
                break;

            case 'products':
                $this->info('• Comando será tratado por ProductsCommandHandler');
                $this->info('• Resultado: Relatório de produtos');
                break;

            case 'unknown':
                $this->warn('• Comando não reconhecido');
                $this->warn('• Resultado: Menu principal (padrão)');
                break;

            default:
                $this->info("• Comando será tratado por handler específico para '{$type}'");
                break;
        }
    }
}
