<?php

namespace Database\Factories;

use App\Domain\Service\Models\ServiceCenter;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceCenterFactory extends Factory
{
    protected $model = ServiceCenter::class;

    public function definition(): array
    {
        $cities = [
            'São Paulo' => ['SP', -23.5505, -46.6333],
            'Rio de Janeiro' => ['RJ', -22.9068, -43.1729],
            'Belo Horizonte' => ['MG', -19.9191, -43.9378],
            'Porto Alegre' => ['RS', -30.0346, -51.2177],
            'Curitiba' => ['PR', -25.4284, -49.2733],
            'Florianópolis' => ['SC', -27.5954, -48.5480],
            'Salvador' => ['BA', -12.9714, -38.5014],
            'Brasília' => ['DF', -15.8267, -47.9218],
            'Goiânia' => ['GO', -16.6869, -49.2648],
            'Recife' => ['PE', -8.0476, -34.8770],
            'Fortaleza' => ['CE', -3.7319, -38.5267],
            'Campinas' => ['SP', -22.9099, -47.0626],
            'Santos' => ['SP', -23.9618, -46.3322],
            'Sorocaba' => ['SP', -23.5015, -47.4526],
            'Ribeirão Preto' => ['SP', -21.1775, -47.8103]
        ];

        $cityName = $this->faker->randomKey($cities);
        [$state, $lat, $lng] = $cities[$cityName];

        // Add some variation to coordinates (within ~5km radius)
        $latVariation = $this->faker->randomFloat(4, -0.045, 0.045);
        $lngVariation = $this->faker->randomFloat(4, -0.045, 0.045);

        $name = $this->generateServiceCenterName();

        return [
            'code' => $this->generateCode(),
            'name' => $name,
            'slug' => Str::slug($name),
            'cnpj' => $this->generateCNPJ(),
            'state_registration' => $this->faker->optional(0.8)->numerify('###.###.###.###'),
            'legal_name' => $name . ' Ltda',
            'trade_name' => $name,
            'address_line' => $this->faker->streetName(),
            'number' => $this->faker->buildingNumber(),
            'complement' => $this->faker->optional(0.3)->randomElement([
                'Loja A', 'Galpão 1', 'Sala 101', 'Térreo', 'Sobreloja'
            ]),
            'neighborhood' => $this->faker->randomElement([
                'Centro', 'Vila Nova', 'Jardim das Flores', 'Bela Vista', 'São José',
                'Santa Maria', 'Parque Industrial', 'Vila São Paulo', 'Jardim América',
                'Cidade Nova', 'Alto da Glória', 'Zona Sul', 'Zona Norte'
            ]),
            'city' => $cityName,
            'state' => $state,
            'zip_code' => $this->faker->regexify('[0-9]{5}-[0-9]{3}'),
            'latitude' => $lat + $latVariation,
            'longitude' => $lng + $lngVariation,
            'phone' => $this->generateBrazilianPhone($state),
            'whatsapp' => $this->faker->optional(0.9)->passthrough($this->generateBrazilianPhone($state)),
            'email' => strtolower(str_replace(' ', '', $name)) . '@' . $this->faker->randomElement([
                'gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com.br'
            ]),
            'website' => $this->faker->optional(0.4)->url(),
            'facebook_url' => $this->faker->optional(0.6)->url(),
            'instagram_url' => $this->faker->optional(0.7)->url(),
            'google_maps_url' => $this->faker->optional(0.8)->url(),
            'manager_id' => $this->faker->optional(0.7)->randomElement([null, User::factory()]),
            'technical_responsible' => $this->faker->optional(0.8)->name(),
            'opening_date' => $this->faker->optional(0.9)->dateTimeBetween('-10 years', 'now'),
            'operating_hours' => $this->generateOperatingHours(),
            'is_main_branch' => false, // Will be set to true for one specific center
            'active' => $this->faker->boolean(95), // 95% chance of being active
            'observations' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function mainBranch(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_main_branch' => true,
            'code' => 'MAIN001',
            'name' => 'Rei do Óleo - Matriz',
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

    public function withManager(User $manager): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id' => $manager->id,
        ]);
    }

    public function inSaoPaulo(): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => 'São Paulo',
            'state' => 'SP',
            'latitude' => $this->faker->randomFloat(4, -23.7, -23.4),
            'longitude' => $this->faker->randomFloat(4, -46.8, -46.4),
        ]);
    }

    public function inRioDeJaneiro(): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'latitude' => $this->faker->randomFloat(4, -23.1, -22.7),
            'longitude' => $this->faker->randomFloat(4, -43.8, -43.1),
        ]);
    }

    private function generateCode(): string
    {
        return 'RC' . $this->faker->unique()->numberBetween(100, 999);
    }

    private function generateServiceCenterName(): string
    {
        $prefixes = ['Auto Center', 'Oficina', 'Mecânica', 'Centro Automotivo'];
        $names = [
            'Rei do Óleo', 'Auto Peças', 'Speed Car', 'Motor Show', 'Car Service',
            'Auto Mecânica', 'Express Car', 'Top Car', 'Master Auto', 'Pro Car'
        ];
        $suffixes = ['Premium', 'Express', 'Center', 'Service', 'Auto'];

        return $this->faker->randomElement($prefixes) . ' ' .
               $this->faker->randomElement($names) . ' ' .
               $this->faker->optional(0.5)->randomElement($suffixes);
    }

    private function generateCNPJ(): string
    {
        // Generate valid CNPJ format (not necessarily a real valid CNPJ)
        $cnpj = '';
        for ($i = 0; $i < 12; $i++) {
            $cnpj .= $this->faker->numberBetween(0, 9);
        }

        // Add branch and check digits
        $cnpj .= '0001'; // Branch: 0001

        return $cnpj;
    }

    private function generateBrazilianPhone(string $state): string
    {
        $ddds = [
            'SP' => ['11', '12', '13', '14', '15', '16', '17', '18', '19'],
            'RJ' => ['21', '22', '24'],
            'MG' => ['31', '32', '33', '34', '35', '37', '38'],
            'RS' => ['51', '53', '54', '55'],
            'PR' => ['41', '42', '43', '44', '45', '46'],
            'SC' => ['47', '48', '49'],
            'BA' => ['71', '73', '74', '75', '77'],
            'DF' => ['61'],
            'GO' => ['62', '64'],
            'PE' => ['81', '87'],
            'CE' => ['85', '88'],
        ];

        $ddd = $this->faker->randomElement($ddds[$state] ?? ['11']);

        // 30% chance of mobile (9 digits), 70% chance of landline (8 digits) for business
        if ($this->faker->boolean(30)) {
            // Mobile: 9XXXX-XXXX
            return $ddd . '9' . $this->faker->numberBetween(1000, 9999) . $this->faker->numberBetween(1000, 9999);
        } else {
            // Landline: XXXX-XXXX
            return $ddd . $this->faker->numberBetween(2000, 5999) . $this->faker->numberBetween(1000, 9999);
        }
    }

    private function generateOperatingHours(): string
    {
        $schedules = [
            'Segunda a Sexta: 08:00 às 18:00, Sábado: 08:00 às 12:00',
            'Segunda a Sexta: 07:30 às 17:30, Sábado: 08:00 às 13:00',
            'Segunda a Sexta: 08:00 às 17:00, Sábado: 08:00 às 14:00',
            'Segunda a Sábado: 08:00 às 18:00',
            'Segunda a Sexta: 07:00 às 18:00, Sábado: 07:00 às 12:00',
            'Segunda a Sexta: 08:30 às 18:30, Sábado: 08:00 às 16:00'
        ];

        return $this->faker->randomElement($schedules);
    }
}
