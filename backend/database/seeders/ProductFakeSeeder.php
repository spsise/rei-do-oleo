<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\Category;
use Faker\Factory as Faker;

class ProductFakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('⚠️ Nenhuma categoria encontrada. Execute CategorySeeder primeiro.');
            return;
        }

        // Produtos comuns em oficinas mecânicas
        $products = [
            // Óleos
            ['name' => 'Óleo de Motor 5W30 Sintético', 'price' => 45.90, 'unit' => 'Litro'],
            ['name' => 'Óleo de Motor 10W40 Mineral', 'price' => 28.50, 'unit' => 'Litro'],
            ['name' => 'Óleo de Motor 15W40 Diesel', 'price' => 35.80, 'unit' => 'Litro'],
            ['name' => 'Óleo de Transmissão ATF', 'price' => 42.00, 'unit' => 'Litro'],
            ['name' => 'Óleo de Freio DOT4', 'price' => 18.90, 'unit' => 'Litro'],
            ['name' => 'Óleo de Direção Hidráulica', 'price' => 25.60, 'unit' => 'Litro'],

            // Filtros
            ['name' => 'Filtro de Óleo', 'price' => 12.50, 'unit' => 'Unidade'],
            ['name' => 'Filtro de Ar', 'price' => 15.80, 'unit' => 'Unidade'],
            ['name' => 'Filtro de Combustível', 'price' => 22.40, 'unit' => 'Unidade'],
            ['name' => 'Filtro de Cabine', 'price' => 18.90, 'unit' => 'Unidade'],

            // Pastilhas e Lonas
            ['name' => 'Pastilha de Freio Dianteira', 'price' => 85.00, 'unit' => 'Par'],
            ['name' => 'Pastilha de Freio Traseira', 'price' => 75.00, 'unit' => 'Par'],
            ['name' => 'Lona de Freio Traseira', 'price' => 45.00, 'unit' => 'Par'],
            ['name' => 'Disco de Freio Dianteiro', 'price' => 120.00, 'unit' => 'Unidade'],
            ['name' => 'Disco de Freio Traseiro', 'price' => 95.00, 'unit' => 'Unidade'],

            // Baterias
            ['name' => 'Bateria 60Ah', 'price' => 280.00, 'unit' => 'Unidade'],
            ['name' => 'Bateria 70Ah', 'price' => 320.00, 'unit' => 'Unidade'],
            ['name' => 'Bateria 80Ah', 'price' => 380.00, 'unit' => 'Unidade'],

            // Pneus
            ['name' => 'Pneu 175/70R13', 'price' => 180.00, 'unit' => 'Unidade'],
            ['name' => 'Pneu 185/65R14', 'price' => 220.00, 'unit' => 'Unidade'],
            ['name' => 'Pneu 195/65R15', 'price' => 280.00, 'unit' => 'Unidade'],
            ['name' => 'Pneu 205/55R16', 'price' => 350.00, 'unit' => 'Unidade'],

            // Amortecedores
            ['name' => 'Amortecedor Dianteiro', 'price' => 180.00, 'unit' => 'Unidade'],
            ['name' => 'Amortecedor Traseiro', 'price' => 150.00, 'unit' => 'Unidade'],
            ['name' => 'Mola Dianteira', 'price' => 120.00, 'unit' => 'Unidade'],
            ['name' => 'Mola Traseira', 'price' => 95.00, 'unit' => 'Unidade'],

            // Correias
            ['name' => 'Correia Dentada', 'price' => 85.00, 'unit' => 'Unidade'],
            ['name' => 'Correia Alternador', 'price' => 45.00, 'unit' => 'Unidade'],
            ['name' => 'Correia Ar Condicionado', 'price' => 35.00, 'unit' => 'Unidade'],

            // Velas
            ['name' => 'Vela de Ignição', 'price' => 25.00, 'unit' => 'Unidade'],
            ['name' => 'Vela de Ignição Iridium', 'price' => 45.00, 'unit' => 'Unidade'],
            ['name' => 'Cabo de Vela', 'price' => 35.00, 'unit' => 'Jogo'],

            // Fluidos
            ['name' => 'Aditivo Radiador', 'price' => 15.50, 'unit' => 'Litro'],
            ['name' => 'Líquido de Arrefecimento', 'price' => 12.80, 'unit' => 'Litro'],
            ['name' => 'Aditivo Combustível', 'price' => 8.90, 'unit' => 'Unidade'],

            // Acessórios
            ['name' => 'Tapete de Borracha', 'price' => 45.00, 'unit' => 'Jogo'],
            ['name' => 'Capa de Volante', 'price' => 25.00, 'unit' => 'Unidade'],
            ['name' => 'Suporte Celular', 'price' => 35.00, 'unit' => 'Unidade'],
            ['name' => 'Carregador USB', 'price' => 28.00, 'unit' => 'Unidade'],
        ];

                        // Criar produtos baseados na lista
        foreach ($products as $index => $productData) {
            $category = $categories->random();

            // Verificar se o produto já existe
            $existingProduct = Product::where('name', $productData['name'])->first();
            if ($existingProduct) {
                continue; // Pular se já existe
            }

            Product::create([
                'category_id' => $category->id,
                'name' => $productData['name'],
                'slug' => \Illuminate\Support\Str::slug($productData['name']) . '-' . $faker->unique()->numberBetween(1000, 9999),
                'description' => $faker->optional(0.7)->sentence(),
                'sku' => $faker->unique()->regexify('[A-Z]{2}[0-9]{6}'),
                'price' => $productData['price'],
                'stock_quantity' => $faker->numberBetween(0, 50),
                'min_stock' => $faker->numberBetween(2, 10),
                'unit' => $productData['unit'],
                'active' => $faker->boolean(95), // 95% ativos
            ]);
        }

                // Criar alguns produtos adicionais com nomes gerados
        for ($i = 0; $i < 20; $i++) {
            $category = $categories->random();
            $productNames = [
                'Peça de Reposição ' . $faker->unique()->word(),
                'Componente ' . $faker->unique()->word(),
                'Acessório ' . $faker->unique()->word(),
                'Ferramenta ' . $faker->unique()->word(),
                'Equipamento ' . $faker->unique()->word(),
            ];

            $productName = $faker->randomElement($productNames);

            Product::create([
                'category_id' => $category->id,
                'name' => $productName,
                'slug' => \Illuminate\Support\Str::slug($productName) . '-' . $faker->unique()->numberBetween(1000, 9999),
                'description' => $faker->sentence(),
                'sku' => $faker->unique()->regexify('[A-Z]{2}[0-9]{6}'),
                'price' => $faker->randomFloat(2, 5, 500),
                'stock_quantity' => $faker->numberBetween(0, 30),
                'min_stock' => $faker->numberBetween(1, 5),
                'unit' => $faker->randomElement(['Unidade', 'Par', 'Jogo', 'Litro', 'Metro', 'Quilograma']),
                'active' => $faker->boolean(90),
            ]);
        }

        $this->command->info('✅ ' . (count($products) + 20) . ' produtos fake criados com sucesso!');
    }
}
