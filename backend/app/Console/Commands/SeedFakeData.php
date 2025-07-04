<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedFakeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
                protected $signature = 'seed:fake
                            {--fresh : Executar migrate:fresh antes de popular}
                            {--safe : Usar seeder seguro que verifica dados existentes}
                            {--clean : Limpar dados fake existentes antes de criar novos}
                            {--final : Usar seeder final que resolve todos os problemas de duplicação}
                            {--only= : Executar apenas um seeder específico (clients, vehicles, products, services, items)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Popular a base de dados com dados fake para desenvolvimento';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando população da base de dados com dados fake...');

        // Verificar se deve executar migrate:fresh
        if ($this->option('fresh')) {
            $this->warn('⚠️ Executando migrate:fresh - todos os dados serão perdidos!');

            if (!$this->confirm('Tem certeza que deseja continuar?')) {
                $this->info('❌ Operação cancelada.');
                return 1;
            }

            $this->info('🔄 Executando migrate:fresh...');
            Artisan::call('migrate:fresh');
            $this->info('✅ Migrations executadas com sucesso!');
        }

                                // Executar seeder específico ou completo
        $only = $this->option('only');
        $safe = $this->option('safe');
        $clean = $this->option('clean');
        $final = $this->option('final');

        if ($only) {
            $this->runSpecificSeeder($only);
        } elseif ($safe) {
            $this->runSafeSeeder();
        } elseif ($clean) {
            $this->runCleanSeeder();
        } elseif ($final) {
            $this->runFinalSeeder();
        } else {
            $this->runCompleteSeeder();
        }

        $this->info('🎉 População da base de dados concluída com sucesso!');
        return 0;
    }

    /**
     * Executar seeder específico
     */
    private function runSpecificSeeder(string $seeder): void
    {
        $seeders = [
            'clients' => 'ClientFakeSeeder',
            'vehicles' => 'VehicleFakeSeeder',
            'products' => 'ProductFakeSeeder',
            'services' => 'ServiceFakeSeeder',
            'items' => 'ServiceItemFakeSeeder',
        ];

        if (!isset($seeders[$seeder])) {
            $this->error("❌ Seeder '$seeder' não encontrado. Opções disponíveis: " . implode(', ', array_keys($seeders)));
            return;
        }

        $seederClass = $seeders[$seeder];
        $this->info("🌱 Executando $seederClass...");

        try {
            Artisan::call('db:seed', ['--class' => $seederClass]);
            $this->info("✅ $seederClass executado com sucesso!");
        } catch (\Exception $e) {
            $this->error("❌ Erro ao executar $seederClass: " . $e->getMessage());
        }
    }

    /**
     * Executar seeder completo
     */
    private function runCompleteSeeder(): void
    {
        $this->info('🌱 Executando DatabaseSeederFake...');

        try {
            Artisan::call('db:seed', ['--class' => 'DatabaseSeederFake']);
            $this->info('✅ DatabaseSeederFake executado com sucesso!');
        } catch (\Exception $e) {
            $this->error('❌ Erro ao executar DatabaseSeederFake: ' . $e->getMessage());

            $this->warn('💡 Tentando executar seeders individuais...');
            $this->runIndividualSeeders();
        }
    }

        /**
     * Executar seeder seguro
     */
    private function runSafeSeeder(): void
    {
        $this->info('🌱 Executando DatabaseSeederFakeSafe...');

        try {
            Artisan::call('db:seed', ['--class' => 'DatabaseSeederFakeSafe']);
            $this->info('✅ DatabaseSeederFakeSafe executado com sucesso!');
        } catch (\Exception $e) {
            $this->error('❌ Erro ao executar DatabaseSeederFakeSafe: ' . $e->getMessage());
        }
    }

    /**
     * Executar seeder com limpeza
     */
    private function runCleanSeeder(): void
    {
        $this->info('🧹 Executando DatabaseSeederFakeClean...');

        try {
            Artisan::call('db:seed', ['--class' => 'DatabaseSeederFakeClean']);
            $this->info('✅ DatabaseSeederFakeClean executado com sucesso!');
        } catch (\Exception $e) {
            $this->error('❌ Erro ao executar DatabaseSeederFakeClean: ' . $e->getMessage());
        }
    }

    /**
     * Executar seeder final
     */
    private function runFinalSeeder(): void
    {
        $this->info('🎯 Executando DatabaseSeederFakeFinal...');

        try {
            Artisan::call('db:seed', ['--class' => 'DatabaseSeederFakeFinal']);
            $this->info('✅ DatabaseSeederFakeFinal executado com sucesso!');
        } catch (\Exception $e) {
            $this->error('❌ Erro ao executar DatabaseSeederFakeFinal: ' . $e->getMessage());
        }
    }

    /**
     * Executar seeders individuais em caso de erro
     */
    private function runIndividualSeeders(): void
    {
        $seeders = [
            'RolePermissionSeeder',
            'ServiceStatusSeeder',
            'PaymentMethodSeeder',
            'CategorySeeder',
            'ServiceCenterSeeder',
            'UserSeeder',
            'ClientFakeSeeder',
            'VehicleFakeSeeder',
            'ProductFakeSeeder',
            'ServiceFakeSeeder',
            'ServiceItemFakeSeeder',
        ];

        foreach ($seeders as $seeder) {
            $this->info("🌱 Executando $seeder...");

            try {
                Artisan::call('db:seed', ['--class' => $seeder]);
                $this->info("✅ $seeder executado com sucesso!");
            } catch (\Exception $e) {
                $this->error("❌ Erro ao executar $seeder: " . $e->getMessage());
                $this->warn("⚠️ Continuando com próximo seeder...");
            }
        }
    }
}
