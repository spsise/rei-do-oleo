<?php

namespace Database\Factories;

use App\Domain\Service\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        $paymentMethods = [
            'Dinheiro' => 'Pagamento em espécie',
            'Cartão de Débito' => 'Pagamento com cartão de débito',
            'Cartão de Crédito' => 'Pagamento com cartão de crédito',
            'PIX' => 'Pagamento instantâneo via PIX',
            'Transferência Bancária' => 'Transferência bancária',
            'Boleto Bancário' => 'Pagamento via boleto',
            'Cheque' => 'Pagamento com cheque',
            'Vale' => 'Pagamento com vale/cupom'
        ];

        $methodName = $this->faker->randomKey($paymentMethods);
        $description = $paymentMethods[$methodName];

        // Add unique suffix to avoid duplicates
        $uniqueMethodName = $methodName . ' #' . $this->faker->unique()->numberBetween(1000, 9999);

        return [
            'name' => $uniqueMethodName,
            'description' => $description,
            'active' => $this->faker->boolean(90), // 90% chance of being active
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

    // Common payment methods
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Dinheiro',
            'description' => 'Pagamento em espécie',
            'active' => true,
            'sort_order' => 1,
        ]);
    }

    public function debitCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Cartão de Débito',
            'description' => 'Pagamento com cartão de débito',
            'active' => true,
            'sort_order' => 2,
        ]);
    }

    public function creditCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Cartão de Crédito',
            'description' => 'Pagamento com cartão de crédito',
            'active' => true,
            'sort_order' => 3,
        ]);
    }

    public function pix(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'PIX',
            'description' => 'Pagamento instantâneo via PIX',
            'active' => true,
            'sort_order' => 4,
        ]);
    }
}
