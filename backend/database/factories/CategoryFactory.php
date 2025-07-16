<?php

namespace Database\Factories;

use App\Domain\Product\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $automotiveCategories = [
            'Lubrificantes' => 'Óleos, graxas e lubrificantes para motor e transmissão',
            'Filtros' => 'Filtros de ar, óleo, combustível e cabine',
            'Sistema Elétrico' => 'Velas, cabos, bateria e componentes elétricos',
            'Freios' => 'Pastilhas, discos, lonas e fluidos de freio',
            'Suspensão' => 'Amortecedores, molas e componentes da suspensão',
            'Motor' => 'Componentes internos do motor e acessórios',
            'Transmissão' => 'Embreagem, câmbio e componentes de transmissão',
            'Arrefecimento' => 'Radiador, bomba d\'água e sistema de arrefecimento',
            'Combustível' => 'Bomba, bico injetor e sistema de combustível',
            'Escapamento' => 'Silenciosos, catalisadores e tubulações',
            'Pneus' => 'Pneus de diversos tamanhos e marcas',
            'Rodas' => 'Rodas de liga e ferro',
            'Acessórios' => 'Tapetes, capas e acessórios diversos',
            'Ferramentas' => 'Ferramentas e equipamentos para oficina',
            'Limpeza' => 'Produtos de limpeza automotiva'
        ];

        $categoryName = $this->faker->randomKey($automotiveCategories);
        $description = $automotiveCategories[$categoryName];

        return [
            'name' => $categoryName,
            'description' => $description,
            'active' => $this->faker->boolean(95), // 95% chance of being active
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function withProducts(int $count = 5): static
    {
        return $this->afterCreating(function (Category $category) use ($count) {
            \App\Domain\Product\Models\Product::factory()
                ->count($count)
                ->create(['category_id' => $category->id]);
        });
    }
}
