<?php

namespace Database\Factories;

use App\Domain\Client\Models\Vehicle;
use App\Domain\Client\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $brands = [
            'Volkswagen', 'Chevrolet', 'Fiat', 'Ford', 'Toyota', 'Honda', 'Hyundai',
            'Nissan', 'Renault', 'Peugeot', 'Citroën', 'Jeep', 'BMW', 'Mercedes-Benz',
            'Audi', 'Volvo', 'Mitsubishi', 'Kia', 'Suzuki', 'Subaru'
        ];

        $models = [
            'Volkswagen' => ['Gol', 'Fox', 'Polo', 'Golf', 'Jetta', 'Passat', 'Tiguan', 'Amarok'],
            'Chevrolet' => ['Onix', 'Prisma', 'Cruze', 'Tracker', 'S10', 'Camaro', 'Spin'],
            'Fiat' => ['Uno', 'Palio', 'Siena', 'Punto', 'Bravo', 'Toro', 'Mobi', 'Argo'],
            'Ford' => ['Ka', 'Fiesta', 'Focus', 'Fusion', 'EcoSport', 'Edge', 'Ranger'],
            'Toyota' => ['Etios', 'Yaris', 'Corolla', 'Camry', 'RAV4', 'Hilux', 'Prius'],
            'Honda' => ['Fit', 'City', 'Civic', 'Accord', 'HR-V', 'CR-V', 'Pilot'],
        ];

        $brand = $this->faker->randomElement($brands);
        $availableModels = $models[$brand] ?? ['Modelo A', 'Modelo B', 'Modelo C'];

        return [
            'client_id' => Client::factory(),
            'license_plate' => $this->generateBrazilianLicensePlate(),
            'brand' => $brand,
            'model' => $this->faker->randomElement($availableModels),
            'year' => $this->faker->numberBetween(1990, date('Y') + 1),
            'color' => $this->faker->randomElement([
                'Branco', 'Prata', 'Preto', 'Cinza', 'Azul', 'Vermelho',
                'Verde', 'Amarelo', 'Bege', 'Marrom', 'Dourado'
            ]),
            'fuel_type' => $this->faker->randomElement([
                'gasoline', 'ethanol', 'diesel', 'flex', 'electric', 'hybrid'
            ]),
            'engine' => $this->faker->randomElement([
                '1.0', '1.3', '1.4', '1.5', '1.6', '1.8', '2.0', '2.4', '3.0'
            ]),
            'chassis' => $this->faker->optional(0.7)->regexify('[A-Z0-9]{17}'),
            'renavam' => $this->faker->optional(0.8)->numerify('###########'),
            'mileage' => $this->faker->optional(0.9)->numberBetween(0, 300000),
            'observations' => $this->faker->optional(0.2)->sentence(),
        ];
    }

    public function withClient(Client $client): static
    {
        return $this->state(fn (array $attributes) => [
            'client_id' => $client->id,
        ]);
    }

    public function oldFormat(): static
    {
        return $this->state(fn (array $attributes) => [
            'license_plate' => $this->generateOldFormatPlate(),
        ]);
    }

    public function mercosulFormat(): static
    {
        return $this->state(fn (array $attributes) => [
            'license_plate' => $this->generateMercosulPlate(),
        ]);
    }

    public function newVehicle(): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => $this->faker->numberBetween(date('Y') - 2, date('Y') + 1),
            'mileage' => $this->faker->numberBetween(0, 50000),
        ]);
    }

    public function oldVehicle(): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => $this->faker->numberBetween(1990, 2010),
            'mileage' => $this->faker->numberBetween(100000, 400000),
        ]);
    }

    public function electric(): static
    {
        return $this->state(fn (array $attributes) => [
            'fuel_type' => 'electric',
            'engine' => null,
        ]);
    }

    public function flex(): static
    {
        return $this->state(fn (array $attributes) => [
            'fuel_type' => 'flex',
        ]);
    }

    private function generateBrazilianLicensePlate(): string
    {
        // 70% chance of old format, 30% chance of Mercosul format
        return $this->faker->boolean(70)
            ? $this->generateOldFormatPlate()
            : $this->generateMercosulPlate();
    }

    private function generateOldFormatPlate(): string
    {
        // Old format: ABC-1234
        $letters = '';
        for ($i = 0; $i < 3; $i++) {
            $letters .= $this->faker->randomElement(range('A', 'Z'));
        }

        $numbers = $this->faker->numberBetween(1000, 9999);

        return $letters . '-' . $numbers;
    }

    private function generateMercosulPlate(): string
    {
        // Mercosul format: ABC1D23
        $letters = '';
        for ($i = 0; $i < 3; $i++) {
            $letters .= $this->faker->randomElement(range('A', 'Z'));
        }

        $firstNumber = $this->faker->numberBetween(0, 9);
        $letter = $this->faker->randomElement(range('A', 'Z'));
        $lastNumbers = $this->faker->numberBetween(10, 99);

        return $letters . $firstNumber . $letter . $lastNumbers;
    }
}
