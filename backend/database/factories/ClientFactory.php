<?php

namespace Database\Factories;

use App\Domain\Client\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $states = ['SP', 'RJ', 'MG', 'RS', 'PR', 'SC', 'BA', 'GO', 'PE', 'CE'];
        $isIndividual = $this->faker->boolean(70); // 70% chance of being individual

        return [
            'name' => $isIndividual
                ? $this->faker->name()
                : $this->faker->company() . ' ' . $this->faker->randomElement(['Ltda', 'S/A', 'ME', 'EPP']),
            'phone01' => $this->generateBrazilianPhone(),
            'phone02' => $this->faker->optional(0.3)->randomElement([
                $this->generateBrazilianPhone(),
                null
            ]),
            'email' => $this->faker->optional(0.7)->safeEmail(),
            'cpf' => $isIndividual ? $this->generateCPF() : null,
            'cnpj' => !$isIndividual ? $this->generateCNPJ() : null,
            'address' => $this->faker->optional(0.9)->streetAddress(),
            'city' => $this->faker->optional(0.9)->randomElement([
                'São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Porto Alegre', 'Curitiba',
                'Florianópolis', 'Salvador', 'Goiânia', 'Recife', 'Fortaleza', 'Brasília',
                'Campinas', 'Santos', 'Sorocaba', 'Ribeirão Preto'
            ]),
            'state' => $this->faker->optional(0.9)->randomElement($states),
            'zip_code' => $this->faker->optional(0.8)->regexify('[0-9]{5}-[0-9]{3}'),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'active' => $this->faker->boolean(95), // 95% chance of being active
        ];
    }

    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->name(),
            'cpf' => $this->generateCPF(),
            'cnpj' => null,
        ]);
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(['Ltda', 'S/A', 'ME', 'EPP']),
            'cpf' => null,
            'cnpj' => $this->generateCNPJ(),
        ]);
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

    private function generateCPF(): string
    {
        // Generate valid CPF format (not necessarily a real valid CPF)
        $cpf = '';
        for ($i = 0; $i < 9; $i++) {
            $cpf .= $this->faker->numberBetween(0, 9);
        }

        // Calculate check digits (simplified)
        $cpf .= '00'; // Using 00 as check digits for simplicity

        return $cpf;
    }

    private function generateCNPJ(): string
    {
        // Generate valid CNPJ format (not necessarily a real valid CNPJ)
        $cnpj = '';
        for ($i = 0; $i < 12; $i++) {
            $cnpj .= $this->faker->numberBetween(0, 9);
        }

        // Calculate check digits (simplified)
        $cnpj .= '00'; // Using 00 as check digits for simplicity

        return $cnpj;
    }

    private function generateBrazilianPhone(): string
    {
        $ddd = $this->faker->randomElement([
            '11', '12', '13', '14', '15', '16', '17', '18', '19', // SP
            '21', '22', '24', // RJ
            '27', '28', // ES
            '31', '32', '33', '34', '35', '37', '38', // MG
            '41', '42', '43', '44', '45', '46', // PR
            '47', '48', '49', // SC
            '51', '53', '54', '55', // RS
            '61', // DF
            '62', '64', // GO
            '63', // TO
            '65', '66', // MT
            '67', // MS
            '68', // AC
            '69', // RO
            '71', '73', '74', '75', '77', // BA
            '79', // SE
            '81', '87', // PE
            '82', // AL
            '83', // PB
            '84', // RN
            '85', '88', // CE
            '86', '89', // PI
            '91', '93', '94', // PA
            '92', '97', // AM
            '95', // RR
            '96', // AP
            '98', '99', // MA
        ]);

        // 70% chance of mobile (9 digits), 30% chance of landline (8 digits)
        if ($this->faker->boolean(70)) {
            // Mobile: 9XXXX-XXXX
            return $ddd . '9' . $this->faker->numberBetween(1000, 9999) . $this->faker->numberBetween(1000, 9999);
        } else {
            // Landline: XXXX-XXXX
            return $ddd . $this->faker->numberBetween(2000, 5999) . $this->faker->numberBetween(1000, 9999);
        }
    }
}
