<?php

namespace Database\Factories;

use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceCenter;
use App\Domain\Service\Models\ServiceStatus;
use App\Domain\Service\Models\PaymentMethod;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $scheduledDate = $this->faker->optional(0.8)->dateTimeBetween('-30 days', '+15 days');
        $createdAt = $this->faker->dateTimeBetween('-60 days', 'now');

        // Realistic service flow based on status
        $statusFlow = $this->generateRealisticStatusFlow($createdAt, $scheduledDate);

        return [
            'service_center_id' => ServiceCenter::factory(),
            'client_id' => Client::factory(),
            'vehicle_id' => Vehicle::factory(),
            'service_number' => $this->generateServiceNumber(),
            'description' => $this->generateServiceDescription(),
            'complaint' => $this->faker->optional(0.9)->randomElement([
                'Veículo fazendo ruído estranho no motor',
                'Freios fazendo barulho ao frear',
                'Carro puxando para um lado',
                'Motor falhando na aceleração',
                'Ar condicionado não está gelando',
                'Direção hidráulica pesada',
                'Bateria descarregando rapidamente',
                'Pneus desgastando irregularmente',
                'Câmbio engasgando',
                'Superaquecimento do motor',
                'Luz do painel acesa',
                'Consumo alto de combustível',
                'Dificuldade para dar partida',
                'Vibração no volante',
                'Vazamento de óleo'
            ]),
            'diagnosis' => $this->faker->optional(0.7)->randomElement([
                'Necessário troca de óleo e filtros',
                'Pastilhas de freio desgastadas',
                'Alinhamento e balanceamento necessários',
                'Velas de ignição com desgaste',
                'Gás do ar condicionado baixo',
                'Fluido da direção hidráulica baixo',
                'Bateria com vida útil esgotada',
                'Pneus com pressão incorreta',
                'Óleo do câmbio contaminado',
                'Radiador com vazamento',
                'Sensor com defeito',
                'Bico injetor entupido',
                'Motor de arranque com defeito',
                'Disco de freio empenado'
            ]),
            'solution' => $this->faker->optional(0.6)->randomElement([
                'Realizada troca de óleo 5W30 e filtros',
                'Substituídas pastilhas de freio dianteiras',
                'Executado alinhamento e balanceamento',
                'Trocadas velas de ignição',
                'Reabastecido gás R134a do ar condicionado',
                'Completado fluido da direção hidráulica',
                'Instalada nova bateria 60Ah',
                'Calibrados pneus conforme especificação',
                'Trocado óleo do câmbio automático',
                'Reparado vazamento no radiador',
                'Substituído sensor de temperatura',
                'Realizada limpeza dos bicos injetores',
                'Reparado motor de arranque',
                'Retificados discos de freio'
            ]),
            'scheduled_date' => $scheduledDate,
            'started_at' => $statusFlow['started_at'],
            'finished_at' => $statusFlow['finished_at'],
            'technician_id' => $this->faker->optional(0.8)->randomElement([null, User::factory()]),
            'attendant_id' => $this->faker->optional(0.9)->randomElement([null, User::factory()]),
            'status_id' => $statusFlow['status_id'],
            'payment_method_id' => $this->faker->optional(0.6)->randomElement([null, PaymentMethod::factory()]),
            'labor_cost' => $this->faker->optional(0.8)->randomFloat(2, 50, 500),
            'discount' => $this->faker->optional(0.3)->randomFloat(2, 0, 100),
            'total_amount' => $this->faker->optional(0.7)->randomFloat(2, 100, 1500),
            'mileage' => $this->faker->optional(0.9)->numberBetween(0, 300000),
            'fuel_level' => $this->faker->optional(0.8)->randomElement(['empty', '1/4', '1/2', '3/4', 'full']),
            'observations' => $this->faker->optional(0.4)->sentence(),
            'internal_notes' => $this->faker->optional(0.3)->sentence(),
            'warranty_months' => $this->faker->optional(0.7)->randomElement([3, 6, 12, 24]),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high', 'urgent']),
            'created_at' => $createdAt,
            'updated_at' => $statusFlow['updated_at'] ?? $createdAt,
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => 1, // Assuming 1 is 'scheduled'
            'scheduled_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'started_at' => null,
            'finished_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        $startedAt = $this->faker->dateTimeBetween('-7 days', 'now');

        return $this->state(fn (array $attributes) => [
            'status_id' => 2, // Assuming 2 is 'in_progress'
            'started_at' => $startedAt,
            'finished_at' => null,
            'technician_id' => User::factory(),
        ]);
    }

    public function completed(): static
    {
        $startedAt = $this->faker->dateTimeBetween('-30 days', '-1 day');
        $finishedAt = $this->faker->dateTimeBetween($startedAt, 'now');

        return $this->state(fn (array $attributes) => [
            'status_id' => 3, // Assuming 3 is 'completed'
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
            'technician_id' => User::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'total_amount' => $this->faker->randomFloat(2, 150, 2000),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => 4, // Assuming 4 is 'cancelled'
            'started_at' => null,
            'finished_at' => null,
            'total_amount' => null,
            'observations' => 'Serviço cancelado pelo cliente',
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    public function withServiceCenter(ServiceCenter $serviceCenter): static
    {
        return $this->state(fn (array $attributes) => [
            'service_center_id' => $serviceCenter->id,
        ]);
    }

    public function withClient(Client $client): static
    {
        return $this->state(fn (array $attributes) => [
            'client_id' => $client->id,
        ]);
    }

    public function withVehicle(Vehicle $vehicle): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_id' => $vehicle->id,
            'client_id' => $vehicle->client_id,
        ]);
    }

    public function withTechnician(User $technician): static
    {
        return $this->state(fn (array $attributes) => [
            'technician_id' => $technician->id,
        ]);
    }

    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'labor_cost' => $this->faker->randomFloat(2, 300, 800),
            'total_amount' => $this->faker->randomFloat(2, 800, 3000),
        ]);
    }

    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'labor_cost' => $this->faker->randomFloat(2, 30, 150),
            'total_amount' => $this->faker->randomFloat(2, 50, 300),
        ]);
    }

    private function generateServiceNumber(): string
    {
        $year = date('Y');
        $month = str_pad(date('m'), 2, '0', STR_PAD_LEFT);
        $sequence = $this->faker->unique()->numberBetween(1000, 9999);

        return "OS{$year}{$month}{$sequence}";
    }

    private function generateServiceDescription(): string
    {
        $services = [
            'Troca de óleo e filtros',
            'Revisão geral do veículo',
            'Manutenção preventiva',
            'Reparo no sistema de freios',
            'Alinhamento e balanceamento',
            'Troca de pastilhas de freio',
            'Substituição de velas de ignição',
            'Manutenção do ar condicionado',
            'Troca de correia dentada',
            'Reparo na suspensão',
            'Manutenção do sistema elétrico',
            'Troca de bateria',
            'Reparo no radiador',
            'Manutenção da direção hidráulica',
            'Troca de amortecedores',
            'Reparo no câmbio',
            'Substituição de pneus',
            'Limpeza de bicos injetores',
            'Troca de fluidos',
            'Diagnóstico eletrônico'
        ];

        return $this->faker->randomElement($services);
    }

    private function generateRealisticStatusFlow($createdAt, $scheduledDate): array
    {
        $statusOptions = [1, 2, 3, 4]; // scheduled, in_progress, completed, cancelled
        $weights = [20, 15, 60, 5]; // Realistic distribution

        $statusId = $this->faker->randomElement(
            array_merge(
                array_fill(0, $weights[0], 1),
                array_fill(0, $weights[1], 2),
                array_fill(0, $weights[2], 3),
                array_fill(0, $weights[3], 4)
            )
        );

        $flow = ['status_id' => $statusId];

        switch ($statusId) {
            case 1: // scheduled
                $flow['started_at'] = null;
                $flow['finished_at'] = null;
                $flow['updated_at'] = $createdAt;
                break;

            case 2: // in_progress
                $startedAt = $scheduledDate
                    ? $this->faker->dateTimeBetween($scheduledDate, 'now')
                    : $this->faker->dateTimeBetween($createdAt, 'now');

                $flow['started_at'] = $startedAt;
                $flow['finished_at'] = null;
                $flow['updated_at'] = $startedAt;
                break;

            case 3: // completed
                $startedAt = $scheduledDate
                    ? $this->faker->dateTimeBetween($scheduledDate, '-1 day')
                    : $this->faker->dateTimeBetween($createdAt, '-1 day');

                $finishedAt = $this->faker->dateTimeBetween($startedAt, 'now');

                $flow['started_at'] = $startedAt;
                $flow['finished_at'] = $finishedAt;
                $flow['updated_at'] = $finishedAt;
                break;

            case 4: // cancelled
                $flow['started_at'] = null;
                $flow['finished_at'] = null;
                $flow['updated_at'] = $this->faker->dateTimeBetween($createdAt, 'now');
                break;
        }

        return $flow;
    }
}
