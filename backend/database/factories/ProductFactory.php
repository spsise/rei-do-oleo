<?php

namespace Database\Factories;

use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $automotiveProducts = [
            'Óleo Motor' => ['5W30', '10W40', '15W40', 'SAE 30', 'SAE 40'],
            'Filtro' => ['Ar', 'Óleo', 'Combustível', 'Cabine', 'Hidráulico'],
            'Vela' => ['Ignição', 'Aquecimento', 'Iridium', 'Platina'],
            'Pastilha' => ['Freio Dianteira', 'Freio Traseira', 'Cerâmica'],
            'Disco' => ['Freio Dianteiro', 'Freio Traseiro', 'Ventilado'],
            'Pneu' => ['175/70R13', '185/60R15', '195/55R16', '205/55R16'],
            'Bateria' => ['45Ah', '60Ah', '70Ah', '90Ah', 'AGM'],
            'Amortecedor' => ['Dianteiro', 'Traseiro', 'Pressurizado'],
            'Correia' => ['Dentada', 'Alternador', 'Direção', 'Ar Condicionado'],
            'Fluido' => ['Freio', 'Direção', 'Arrefecimento', 'Transmissão']
        ];

        $brands = [
            'Castrol', 'Mobil', 'Shell', 'Valvoline', 'Ipiranga', 'Petronas',
            'Bosch', 'NGK', 'Denso', 'Mann', 'Mahle', 'Tecfil', 'Wega',
            'TRW', 'Bendix', 'Jurid', 'Ferodo', 'ATE', 'Ate', 'Continental',
            'Pirelli', 'Michelin', 'Goodyear', 'Bridgestone', 'Dunlop'
        ];

        $productType = $this->faker->randomKey($automotiveProducts);
        $productVariant = $this->faker->randomElement($automotiveProducts[$productType]);
        $brand = $this->faker->randomElement($brands);

        $costPrice = $this->faker->randomFloat(2, 10, 500);
        $markup = $this->faker->randomFloat(2, 1.2, 2.5); // 20% to 150% markup
        $price = round($costPrice * $markup, 2);

        return [
            'category_id' => null, // Will be set by relationship or test
            'name' => $brand . ' ' . $productType . ' ' . $productVariant,
            'description' => $this->generateProductDescription($productType, $productVariant, $brand),
            'sku' => $this->generateSKU($brand, $productType),
            'price' => $price,
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'min_stock' => $this->faker->numberBetween(5, 20),
            'unit' => $this->faker->randomElement(['UN', 'LT', 'ML', 'KG', 'G', 'MT', 'CM']),
            'active' => $this->faker->boolean(90), // 90% chance of being active
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

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $this->faker->numberBetween(0, 5),
            'min_stock' => $this->faker->numberBetween(10, 20),
        ]);
    }

    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 500, 2000),
        ]);
    }

    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 5, 50),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    public function highStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $this->faker->numberBetween(50, 200),
        ]);
    }

    public function withCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    private function generateSKU(string $brand, string $productType): string
    {
        $brandCode = strtoupper(substr($brand, 0, 3));
        $typeCode = strtoupper(substr(str_replace(' ', '', $productType), 0, 3));
        $number = $this->faker->numberBetween(1000, 9999);

        return $brandCode . $typeCode . $number;
    }

    private function generateProductDescription(string $type, string $variant, string $brand): string
    {
        $descriptions = [
            'Óleo Motor' => "Óleo lubrificante {$brand} {$variant} de alta qualidade, ideal para motores a gasolina e álcool. Proporciona proteção superior contra desgaste e formação de depósitos.",
            'Filtro' => "Filtro {$brand} de {$variant} com alta capacidade de filtragem. Garante proteção eficiente do motor contra impurezas e contaminantes.",
            'Vela' => "Vela de {$variant} {$brand} com tecnologia avançada. Proporciona ignição eficiente e economia de combustível.",
            'Pastilha' => "Pastilha de {$variant} {$brand} com composto cerâmico. Oferece frenagem segura e durabilidade superior.",
            'Disco' => "Disco de {$variant} {$brand} em ferro fundido. Resistente ao calor e proporciona frenagem uniforme.",
            'Pneu' => "Pneu {$brand} {$variant} com tecnologia avançada de borracha. Oferece aderência superior e maior durabilidade.",
            'Bateria' => "Bateria {$brand} {$variant} livre de manutenção. Alta capacidade de partida a frio e longa vida útil.",
            'Amortecedor' => "Amortecedor {$brand} {$variant} com tecnologia hidráulica. Proporciona conforto e estabilidade na condução.",
            'Correia' => "Correia {$brand} de {$variant} em borracha de alta resistência. Transmissão eficiente de potência.",
            'Fluido' => "Fluido {$brand} para {$variant} com aditivos especiais. Proteção contra corrosão e desgaste."
        ];

        return $descriptions[$type] ?? "Produto automotivo {$brand} {$type} {$variant} de alta qualidade.";
    }
}
