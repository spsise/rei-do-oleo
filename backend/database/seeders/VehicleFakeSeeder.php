<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Client\Models\Vehicle;
use App\Domain\Client\Models\Client;
use Faker\Factory as Faker;

class VehicleFakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $clients = Client::all();

        if ($clients->isEmpty()) {
            $this->command->warn('⚠️ Nenhum cliente encontrado. Execute ClientFakeSeeder primeiro.');
            return;
        }

        // Marcas e modelos populares no Brasil
        $brands = [
            'Fiat' => ['Palio', 'Uno', 'Siena', 'Strada', 'Toro'],
            'Volkswagen' => ['Gol', 'Polo', 'Voyage', 'Saveiro', 'T-Cross'],
            'Chevrolet' => ['Onix', 'Prisma', 'Cobalt', 'Tracker', 'Spin'],
            'Ford' => ['Ka', 'Fiesta', 'Focus', 'EcoSport', 'Ranger'],
            'Honda' => ['Civic', 'Fit', 'HR-V', 'CR-V', 'City'],
            'Toyota' => ['Corolla', 'Etios', 'SW4', 'Hilux', 'Yaris'],
            'Hyundai' => ['HB20', 'i30', 'Tucson', 'Santa Fe', 'Creta'],
            'Renault' => ['Kwid', 'Sandero', 'Logan', 'Duster', 'Captur'],
            'Nissan' => ['March', 'Versa', 'Kicks', 'Frontier', 'Sentra'],
            'Peugeot' => ['208', '2008', '3008', '308', '408'],
        ];

        $colors = [
            'Branco', 'Preto', 'Prata', 'Cinza', 'Azul', 'Vermelho',
            'Verde', 'Amarelo', 'Laranja', 'Marrom', 'Dourado', 'Rosa'
        ];

        // Criar 80 veículos fake (média de 1.6 veículos por cliente)
        for ($i = 0; $i < 80; $i++) {
            $client = $clients->random();
            $brand = $faker->randomElement(array_keys($brands));
            $models = $brands[$brand];
            $model = $faker->randomElement($models);

            Vehicle::create([
                'client_id' => $client->id,
                'brand' => $brand,
                'model' => $model,
                'year' => $faker->numberBetween(2000, 2024),
                'color' => $faker->randomElement($colors),
                'license_plate' => $faker->regexify('[A-Z]{3}[0-9]{4}'), // Formato antigo
                'mileage' => $faker->numberBetween(0, 200000),
                'fuel_type' => $faker->randomElement(['Gasolina', 'Etanol', 'Flex', 'Diesel', 'Elétrico', 'Híbrido']),
                'last_service' => $faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            ]);
        }

        $this->command->info('✅ 80 veículos fake criados com sucesso!');
    }
}
