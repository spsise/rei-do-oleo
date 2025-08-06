<?php

namespace Database\Factories;

use App\Domain\Service\Models\ServiceTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Service\Models\ServiceTemplate>
 */
class ServiceTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['maintenance', 'repair', 'inspection', 'emergency', 'preventive', 'general'];
        $priorities = ['low', 'medium', 'high'];

        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement($categories),
            'estimated_duration' => $this->faker->randomElement([30, 60, 90, 120, 180, 240]),
            'priority' => $this->faker->randomElement($priorities),
            'notes' => $this->faker->optional()->paragraph(),
            'service_items' => $this->faker->optional()->randomElements([
                [
                    'product_name' => 'Óleo Motor',
                    'quantity' => 1,
                    'unit_price' => 89.90,
                    'notes' => 'Óleo sintético'
                ],
                [
                    'product_name' => 'Filtro de Óleo',
                    'quantity' => 1,
                    'unit_price' => 25.00,
                    'notes' => 'Filtro de qualidade'
                ]
            ], $this->faker->numberBetween(0, 3)),
            'active' => $this->faker->boolean(80), // 80% chance of being active
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the template is for maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'maintenance',
            'name' => $this->faker->randomElement([
                'Troca de Óleo',
                'Revisão Geral',
                'Troca de Freios',
                'Alinhamento',
                'Balanceamento'
            ]),
        ]);
    }

    /**
     * Indicate that the template is for repair.
     */
    public function repair(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'repair',
            'name' => $this->faker->randomElement([
                'Reparo de Motor',
                'Reparo de Transmissão',
                'Reparo Elétrico',
                'Reparo de Suspensão',
                'Reparo de Freios'
            ]),
        ]);
    }

    /**
     * Indicate that the template is for inspection.
     */
    public function inspection(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'inspection',
            'name' => $this->faker->randomElement([
                'Inspeção de Segurança',
                'Inspeção Pré-Compra',
                'Inspeção Técnica',
                'Diagnóstico Completo'
            ]),
        ]);
    }

    /**
     * Indicate that the template is for emergency.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'emergency',
            'priority' => 'high',
            'name' => $this->faker->randomElement([
                'Reparo de Emergência',
                'Recuperação de Veículo',
                'Reparo Urgente',
                'Assistência Técnica'
            ]),
        ]);
    }

    /**
     * Indicate that the template is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    /**
     * Indicate that the template is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
