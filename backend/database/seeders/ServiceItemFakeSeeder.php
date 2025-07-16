<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Service\Models\ServiceItem;
use App\Domain\Service\Models\Service;
use App\Domain\Product\Models\Product;
use Faker\Factory as Faker;

class ServiceItemFakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        $services = Service::all();
        $products = Product::all();

        if ($services->isEmpty() || $products->isEmpty()) {
            $this->command->warn('⚠️ Dados necessários não encontrados. Execute os seeders anteriores primeiro.');
            return;
        }

                        // Criar itens de serviço para cada serviço
        foreach ($services as $service) {
            // Cada serviço terá entre 1 e 5 itens
            $numItems = $faker->numberBetween(1, 5);
            $usedProducts = collect(); // Controlar produtos já usados neste serviço

            for ($i = 0; $i < $numItems; $i++) {
                // Pegar produto que ainda não foi usado neste serviço
                $availableProducts = $products->whereNotIn('id', $usedProducts);

                if ($availableProducts->isEmpty()) {
                    break; // Se não há mais produtos disponíveis, parar
                }

                $product = $availableProducts->random();
                $usedProducts->push($product->id);

                $quantity = $faker->numberBetween(1, 4);
                $unitPrice = $product->price;
                $totalPrice = $unitPrice * $quantity;

                // Verificar se já existe este produto neste serviço
                $existingItem = ServiceItem::where('service_id', $service->id)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$existingItem) {
                    ServiceItem::create([
                        'service_id' => $service->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'notes' => $faker->optional(0.3)->sentence(),
                    ]);
                }
            }
        }

        // Criar alguns itens de serviço adicionais (mão de obra, serviços sem produto)
        $serviceItems = [
            ['name' => 'Mão de Obra - Troca de Óleo', 'price' => 30.00],
            ['name' => 'Mão de Obra - Troca de Filtros', 'price' => 25.00],
            ['name' => 'Mão de Obra - Troca de Pastilhas', 'price' => 80.00],
            ['name' => 'Mão de Obra - Troca de Bateria', 'price' => 20.00],
            ['name' => 'Alinhamento', 'price' => 40.00],
            ['name' => 'Balanceamento', 'price' => 35.00],
            ['name' => 'Troca de Pneus', 'price' => 25.00],
            ['name' => 'Diagnóstico Eletrônico', 'price' => 50.00],
            ['name' => 'Limpeza de Injetores', 'price' => 120.00],
            ['name' => 'Troca de Embreagem', 'price' => 200.00],
            ['name' => 'Troca de Radiador', 'price' => 150.00],
            ['name' => 'Troca de Bomba de Água', 'price' => 100.00],
            ['name' => 'Troca de Termostato', 'price' => 60.00],
            ['name' => 'Troca de Sensor de Oxigênio', 'price' => 80.00],
            ['name' => 'Troca de Catalisador', 'price' => 300.00],
            ['name' => 'Lavagem do Motor', 'price' => 45.00],
            ['name' => 'Limpeza do Sistema de Arrefecimento', 'price' => 90.00],
            ['name' => 'Troca de Fluido de Freio', 'price' => 70.00],
            ['name' => 'Troca de Óleo de Transmissão', 'price' => 85.00],
            ['name' => 'Troca de Correia Dentada', 'price' => 120.00],
        ];

                // Adicionar alguns itens de mão de obra aos serviços
        foreach ($services as $service) {
            if ($faker->boolean(0.7)) { // 70% dos serviços terão mão de obra
                $serviceItem = $faker->randomElement($serviceItems);

                // Verificar se já existe mão de obra para este serviço
                $existingLabor = ServiceItem::where('service_id', $service->id)
                    ->whereNull('product_id')
                    ->first();

                if (!$existingLabor) {
                    ServiceItem::create([
                        'service_id' => $service->id,
                        'product_id' => null, // Mão de obra não tem produto
                        'quantity' => 1,
                        'unit_price' => $serviceItem['price'],
                        'total_price' => $serviceItem['price'],
                        'notes' => $serviceItem['name'],
                    ]);
                }
            }
        }

        $this->command->info('✅ Itens de serviço fake criados com sucesso!');
    }
}
