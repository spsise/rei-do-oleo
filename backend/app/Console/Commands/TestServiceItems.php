<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Domain\Service\Models\ServiceItem;
use App\Domain\Service\Models\Service;
use App\Domain\Product\Models\Product;

class TestServiceItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:service-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test service items creation and check for duplicates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testando criação de itens de serviço...');

        // Verificar se temos dados necessários
        $services = Service::count();
        $products = Product::count();

        $this->info("📊 Serviços encontrados: $services");
        $this->info("📊 Produtos encontrados: $products");

        if ($services === 0 || $products === 0) {
            $this->error('❌ Dados insuficientes. Execute os seeders básicos primeiro.');
            return 1;
        }

        // Limpar itens de serviço existentes
        $this->info('🧹 Limpando itens de serviço existentes...');
        ServiceItem::truncate();

        // Executar seeder de itens de serviço
        $this->info('🌱 Executando ServiceItemFakeSeederV2...');

        try {
            Artisan::call('db:seed', ['--class' => 'ServiceItemFakeSeederV2']);
            $this->info('✅ ServiceItemFakeSeederV2 executado com sucesso!');
        } catch (\Exception $e) {
            $this->error('❌ Erro ao executar ServiceItemFakeSeederV2: ' . $e->getMessage());
            return 1;
        }

        // Verificar duplicados
        $this->info('🔍 Verificando duplicados...');

        $duplicates = ServiceItem::select('service_id', 'product_id')
            ->groupBy('service_id', 'product_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->count() > 0) {
            $this->error("❌ Encontrados {$duplicates->count()} grupos de duplicados:");

            foreach ($duplicates as $duplicate) {
                $this->error("   - Serviço ID: {$duplicate->service_id}, Produto ID: {$duplicate->product_id}");
            }

            return 1;
        } else {
            $this->info('✅ Nenhum duplicado encontrado!');
        }

        // Estatísticas finais
        $totalItems = ServiceItem::count();
        $servicesWithItems = ServiceItem::distinct('service_id')->count();

        $this->info("📊 Total de itens criados: $totalItems");
        $this->info("📊 Serviços com itens: $servicesWithItems");

        $this->info('🎉 Teste concluído com sucesso!');
        return 0;
    }
}
