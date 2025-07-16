<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class SafeTest extends Command
{
    protected $signature = 'safe:test {--unit} {--filter=} {--testsuite=}';
    protected $description = 'Executa os testes com segurança usando .env.testing e banco rei_do_oleo_test';

        public function handle()
    {
        $this->info('🔒 Iniciando verificação de segurança para testes...');

        // Forçar ambiente de teste
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';

        // Forçar configuração de banco de teste
        putenv('DB_DATABASE=rei_do_oleo_test');
        $_ENV['DB_DATABASE'] = 'rei_do_oleo_test';

        // Limpar cache de configuração para garantir carregamento correto
        $this->info('🧹 Limpando cache de configuração...');
        $this->call('config:clear');

        // Aguardar um momento para o cache ser limpo
        sleep(1);

        // Forçar configuração de banco após limpeza de cache
        config(['database.connections.mysql.database' => 'rei_do_oleo_test']);

        // Verificar banco de dados
        try {
            $banco = DB::connection()->getDatabaseName();
            $this->info("📊 Banco de dados atual: $banco");

            if ($banco !== 'rei_do_oleo_test') {
                $this->error("🚫 CRÍTICO: Banco de dados atual é '$banco', e não 'rei_do_oleo_test'!");
                $this->error("🚫 Isso pode destruir seus dados de desenvolvimento!");
                $this->error("🚫 Abortando testes por segurança!");

                $this->warn("💡 Dicas para resolver:");
                $this->warn("   1. Verifique se o arquivo .env.testing existe");
                $this->warn("   2. Verifique se DB_DATABASE=rei_do_oleo_test no .env.testing");
                $this->warn("   3. Execute: php artisan config:clear");
                $this->warn("   4. Execute: APP_ENV=testing php artisan safe:test");

                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro ao conectar com banco de dados: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("✅ Ambiente de teste verificado com sucesso!");
        $this->info("✅ Executando testes em banco: $banco");

        // Construir comando de teste
        $cmd = 'php artisan test';

        if ($this->option('unit')) {
            $cmd .= ' --testsuite=Unit';
        }

        if ($this->option('testsuite')) {
            $cmd .= ' --testsuite=' . $this->option('testsuite');
        }

        if ($this->option('filter')) {
            $cmd .= ' --filter="' . $this->option('filter') . '"';
        }

        $this->info("🚀 Executando: $cmd");

        // Executar testes
        passthru($cmd, $returnCode);

        if ($returnCode === 0) {
            $this->info("✅ Testes executados com sucesso!");
        } else {
            $this->error("❌ Testes falharam com código: $returnCode");
        }

        return $returnCode === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
