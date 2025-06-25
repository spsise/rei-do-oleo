<?php

namespace Database\Factories;

use App\Domain\User\Models\User;
use App\Domain\Service\Models\ServiceCenter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\User\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'service_center_id' => ServiceCenter::factory(),
            'phone' => $this->faker->optional(0.9)->passthrough($this->generateBrazilianPhone()),
            'whatsapp' => $this->faker->optional(0.7)->passthrough($this->generateBrazilianPhone()),
            'document' => $this->faker->optional(0.8)->passthrough($this->generateCPF()),
            'birth_date' => $this->faker->optional(0.7)->dateTimeBetween('-65 years', '-18 years'),
            'hire_date' => $this->faker->optional(0.9)->dateTimeBetween('-10 years', 'now'),
            'salary' => $this->faker->optional(0.6)->randomFloat(2, 1500, 8000),
            'commission_rate' => $this->faker->optional(0.4)->randomFloat(2, 0, 15),
            'specialties' => $this->faker->optional(0.5)->randomElement([
                'Motor, Suspensão',
                'Freios, Direção',
                'Elétrica, Ar Condicionado',
                'Injeção Eletrônica',
                'Câmbio, Embreagem',
                'Diesel, Caminhões',
                'Diagnóstico Eletrônico',
                'Pintura, Funilaria'
            ]),
            'active' => $this->faker->boolean(95), // 95% chance of being active
            'last_login_at' => $this->faker->optional(0.7)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Admin ' . $this->faker->lastName(),
            'salary' => $this->faker->randomFloat(2, 5000, 12000),
            'specialties' => null,
        ])->afterCreating(function (User $user) {
            $user->assignRole('admin');
        });
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Gerente ' . $this->faker->lastName(),
            'salary' => $this->faker->randomFloat(2, 4000, 10000),
            'specialties' => 'Gestão, Vendas',
        ])->afterCreating(function (User $user) {
            $user->assignRole('manager');
        });
    }

    public function attendant(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Atendente ' . $this->faker->firstName(),
            'salary' => $this->faker->randomFloat(2, 1500, 3500),
            'specialties' => 'Atendimento, Vendas',
        ])->afterCreating(function (User $user) {
            $user->assignRole('attendant');
        });
    }

    public function technician(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Técnico ' . $this->faker->firstName(),
            'salary' => $this->faker->randomFloat(2, 2000, 6000),
            'commission_rate' => $this->faker->randomFloat(2, 5, 20),
            'specialties' => $this->faker->randomElement([
                'Motor, Suspensão',
                'Freios, Direção',
                'Elétrica, Ar Condicionado',
                'Injeção Eletrônica',
                'Câmbio, Embreagem',
                'Diesel, Caminhões',
                'Diagnóstico Eletrônico'
            ]),
        ])->afterCreating(function (User $user) {
            $user->assignRole('technician');
        });
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

    public function withServiceCenter(ServiceCenter $serviceCenter): static
    {
        return $this->state(fn (array $attributes) => [
            'service_center_id' => $serviceCenter->id,
        ]);
    }

    public function senior(): static
    {
        return $this->state(fn (array $attributes) => [
            'hire_date' => $this->faker->dateTimeBetween('-15 years', '-5 years'),
            'salary' => $this->faker->randomFloat(2, 4000, 10000),
        ]);
    }

    public function junior(): static
    {
        return $this->state(fn (array $attributes) => [
            'hire_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'salary' => $this->faker->randomFloat(2, 1500, 4000),
        ]);
    }

    private function generateCPF(): string
    {
        // Generate valid CPF format (not necessarily a real valid CPF)
        $cpf = '';
        for ($i = 0; $i < 9; $i++) {
            $cpf .= $this->faker->numberBetween(0, 9);
        }

        // Add check digits (simplified)
        $cpf .= '00'; // Using 00 as check digits for simplicity

        return $cpf;
    }

    private function generateBrazilianPhone(): string
    {
        $ddd = $this->faker->randomElement([
            '11', '12', '13', '14', '15', '16', '17', '18', '19', // SP
            '21', '22', '24', // RJ
            '31', '32', '33', '34', '35', '37', '38', // MG
            '41', '42', '43', '44', '45', '46', // PR
            '47', '48', '49', // SC
            '51', '53', '54', '55', // RS
            '61', // DF
            '85', '88', // CE
        ]);

        // 80% chance of mobile (9 digits), 20% chance of landline (8 digits)
        if ($this->faker->boolean(80)) {
            // Mobile: 9XXXX-XXXX
            return $ddd . '9' . $this->faker->numberBetween(1000, 9999) . $this->faker->numberBetween(1000, 9999);
        } else {
            // Landline: XXXX-XXXX
            return $ddd . $this->faker->numberBetween(2000, 5999) . $this->faker->numberBetween(1000, 9999);
        }
    }
}
