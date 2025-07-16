<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Client\Models\Client;
use Faker\Factory as Faker;

class ClientFakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // Criar 50 clientes fake
        for ($i = 0; $i < 50; $i++) {
            $isPerson = $faker->boolean(70); // 70% pessoa física, 30% pessoa jurídica

            Client::create([
                'name' => $isPerson
                    ? $faker->name()
                    : $faker->company(),
                'phone01' => $faker->phoneNumber(),
                'phone02' => $faker->optional(0.3)->phoneNumber(), // 30% chance de ter segundo telefone
                'email' => $faker->safeEmail(),
                'cpf' => $isPerson ? $faker->cpf(false) : null,
                'cnpj' => !$isPerson ? $faker->cnpj(false) : null,
                'address' => $faker->streetAddress(),
                'city' => $faker->city(),
                'state' => $faker->stateAbbr(),
                'zip_code' => $faker->postcode(),
                'notes' => $faker->optional(0.4)->sentence(), // 40% chance de ter observações
                'active' => $faker->boolean(90), // 90% ativos
            ]);
        }

        $this->command->info('✅ 50 clientes fake criados com sucesso!');
    }
}
