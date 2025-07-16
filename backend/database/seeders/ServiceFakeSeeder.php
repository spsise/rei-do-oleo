<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceStatus;
use App\Domain\Service\Models\PaymentMethod;
use App\Domain\Service\Models\ServiceCenter;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\User\Models\User;
use Faker\Factory as Faker;
use Carbon\Carbon;

class ServiceFakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        $clients = Client::all();
        $vehicles = Vehicle::all();
        $users = User::all();
        $serviceCenters = ServiceCenter::all();
        $serviceStatuses = ServiceStatus::all();
        $paymentMethods = PaymentMethod::all();

        if ($clients->isEmpty() || $vehicles->isEmpty() || $users->isEmpty()) {
            $this->command->warn('⚠️ Dados necessários não encontrados. Execute os seeders anteriores primeiro.');
            return;
        }

        // Tipos de serviços comuns
        $serviceTypes = [
            'Troca de Óleo',
            'Troca de Filtros',
            'Troca de Pastilhas de Freio',
            'Troca de Bateria',
            'Alinhamento e Balanceamento',
            'Troca de Pneus',
            'Troca de Amortecedores',
            'Troca de Correia Dentada',
            'Troca de Velas',
            'Troca de Fluido de Freio',
            'Troca de Óleo de Transmissão',
            'Troca de Filtro de Ar',
            'Troca de Filtro de Combustível',
            'Troca de Filtro de Cabine',
            'Troca de Disco de Freio',
            'Troca de Lona de Freio',
            'Troca de Mola',
            'Troca de Correia Alternador',
            'Troca de Cabo de Vela',
            'Troca de Líquido de Arrefecimento',
            'Manutenção Preventiva',
            'Manutenção Corretiva',
            'Diagnóstico Eletrônico',
            'Limpeza de Injetores',
            'Troca de Embreagem',
            'Troca de Radiador',
            'Troca de Bomba de Água',
            'Troca de Termostato',
            'Troca de Sensor de Oxigênio',
            'Troca de Catalisador',
        ];

        // Criar 100 serviços fake
        for ($i = 0; $i < 100; $i++) {
            $client = $clients->random();
            $clientVehicles = $vehicles->where('client_id', $client->id);

            // Verificar se o cliente tem veículos
            if ($clientVehicles->isEmpty()) {
                continue; // Pular se o cliente não tem veículos
            }

            $vehicle = $clientVehicles->random();
            $user = $users->random();
            $serviceCenter = $serviceCenters->random();
            $serviceStatus = $serviceStatuses->random();
            $paymentMethod = $paymentMethods->random();

                                    // Gerar datas realistas
            $scheduledAt = $faker->dateTimeBetween('-6 months', '+1 month');
            $startedAt = null;
            $completedAt = null;

            // Se o serviço foi iniciado ou completado
            if (in_array($serviceStatus->name, ['in_progress', 'completed', 'cancelled'])) {
                $startedAt = $faker->dateTimeBetween($scheduledAt, $scheduledAt->modify('+1 day'));

                if (in_array($serviceStatus->name, ['completed'])) {
                    $completedAt = $faker->dateTimeBetween($startedAt, $startedAt->modify('+2 days'));
                }
            }

            // Calcular valores
            $totalAmount = $faker->randomFloat(2, 50, 800);
            $discountAmount = $faker->optional(0.3)->randomFloat(2, 10, 100);
            $finalAmount = $totalAmount - ($discountAmount ?? 0);

            Service::create([
                'client_id' => $client->id,
                'vehicle_id' => $vehicle->id,
                'user_id' => $user->id,
                'service_center_id' => $serviceCenter->id,
                'service_number' => Service::generateServiceNumber(),
                'scheduled_at' => $scheduledAt,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'service_status_id' => $serviceStatus->id,
                'payment_method_id' => $paymentMethod->id,
                'mileage_at_service' => $faker->numberBetween($vehicle->mileage - 5000, $vehicle->mileage + 5000),
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'observations' => $faker->optional(0.6)->paragraph(),
                'notes' => $faker->optional(0.4)->sentence(),
                'active' => $faker->boolean(95), // 95% ativos
            ]);
        }

        $this->command->info('✅ 100 serviços fake criados com sucesso!');
    }
}
