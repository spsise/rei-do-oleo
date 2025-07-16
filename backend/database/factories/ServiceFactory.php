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
        $scheduledAt = $this->faker->optional(0.8)->dateTimeBetween('-30 days', '+15 days');
        $createdAt = $this->faker->dateTimeBetween('-60 days', 'now');

        // Get a random existing status instead of hardcoded ID
        $statusId = ServiceStatus::inRandomOrder()->value('id') ?? $this->createDefaultStatus();

        // Realistic service flow based on status
        $statusFlow = $this->generateRealisticStatusFlow($createdAt, $scheduledAt, $statusId);

        return [
            'service_center_id' => ServiceCenter::factory(),
            'client_id' => Client::factory(),
            'vehicle_id' => Vehicle::factory(),
            'user_id' => User::factory(),
            'service_number' => $this->generateServiceNumber(),
            'scheduled_at' => $scheduledAt,
            'started_at' => $statusFlow['started_at'],
            'completed_at' => $statusFlow['completed_at'],
            'service_status_id' => $statusFlow['service_status_id'],
            'payment_method_id' => $this->faker->optional(0.6)->randomElement([null, PaymentMethod::factory()]),
            'mileage_at_service' => $this->faker->optional(0.9)->numberBetween(0, 300000),
            'total_amount' => $this->faker->optional(0.7)->randomFloat(2, 100, 1500),
            'discount_amount' => $this->faker->optional(0.3)->randomFloat(2, 0, 100),
            'final_amount' => function (array $attributes) {
                $total = $attributes['total_amount'] ?? 0;
                $discount = $attributes['discount_amount'] ?? 0;
                return max(0, $total - $discount);
            },
            'observations' => $this->faker->optional(0.4)->sentence(),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'active' => $this->faker->boolean(95),
            'created_at' => $createdAt,
            'updated_at' => $statusFlow['updated_at'] ?? $createdAt,
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_status_id' => $this->getStatusIdByName('scheduled'),
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+30 days'),
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        $startedAt = $this->faker->dateTimeBetween('-7 days', 'now');

        return $this->state(fn (array $attributes) => [
            'service_status_id' => $this->getStatusIdByName('in_progress'),
            'started_at' => $startedAt,
            'completed_at' => null,
            'user_id' => User::factory(),
        ]);
    }

    public function completed(): static
    {
        $startedAt = $this->faker->dateTimeBetween('-30 days', '-1 day');
        $completedAt = $this->faker->dateTimeBetween($startedAt, 'now');

        return $this->state(fn (array $attributes) => [
            'service_status_id' => $this->getStatusIdByName('completed'),
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'user_id' => User::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'total_amount' => $this->faker->randomFloat(2, 150, 2000),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_status_id' => $this->getStatusIdByName('cancelled'),
            'started_at' => null,
            'completed_at' => null,
            'total_amount' => null,
            'observations' => 'Serviço cancelado pelo cliente',
        ]);
    }

    public function withHighValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => $this->faker->randomFloat(2, 1000, 5000),
        ]);
    }

    public function withLowValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => $this->faker->randomFloat(2, 50, 200),
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
            'user_id' => $technician->id,
        ]);
    }

    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => $this->faker->randomFloat(2, 800, 3000),
        ]);
    }

    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => $this->faker->randomFloat(2, 50, 300),
        ]);
    }

    private function getStatusIdByName(string $statusName): int
    {
        $status = ServiceStatus::where('name', $statusName)->first();

        if (!$status) {
            // Create status if it doesn't exist (for testing)
            $status = ServiceStatus::create([
                'name' => $statusName,
                'description' => ucfirst(str_replace('_', ' ', $statusName)),
                'color' => '#000000',
                'sort_order' => 1,
            ]);
        }

        return $status->id;
    }

    private function createDefaultStatus(): int
    {
        $status = ServiceStatus::create([
            'name' => 'scheduled',
            'description' => 'Serviço agendado',
            'color' => '#3B82F6',
            'sort_order' => 1,
        ]);

        return $status->id;
    }

    private function generateRealisticStatusFlow($createdAt, $scheduledDate, $defaultStatusId = null): array
    {
        // Get existing status IDs dynamically
        $statusIds = ServiceStatus::pluck('id', 'name')->toArray();

        if (empty($statusIds)) {
            // Create basic statuses if none exist
            $this->createDefaultStatuses();
            $statusIds = ServiceStatus::pluck('id', 'name')->toArray();
        }

        $statusOptions = array_values($statusIds);
        $weights = [20, 15, 60, 5]; // Realistic distribution

        $statusId = $defaultStatusId ?? $this->faker->randomElement(
            array_merge(
                array_fill(0, $weights[0], $statusOptions[0] ?? 1),
                array_fill(0, $weights[1], $statusOptions[1] ?? 2),
                array_fill(0, $weights[2], $statusOptions[2] ?? 3),
                array_fill(0, $weights[3], $statusOptions[3] ?? 4)
            )
        );

        $flow = ['service_status_id' => $statusId];

        // Get status name for logic
        $statusName = array_search($statusId, $statusIds) ?: 'scheduled';

        switch ($statusName) {
            case 'scheduled':
                $flow['started_at'] = null;
                $flow['completed_at'] = null;
                $flow['updated_at'] = $createdAt;
                break;

            case 'in_progress':
                $startedAt = $scheduledDate
                    ? $this->faker->dateTimeBetween($scheduledDate, 'now')
                    : $this->faker->dateTimeBetween($createdAt, 'now');

                $flow['started_at'] = $startedAt;
                $flow['completed_at'] = null;
                $flow['updated_at'] = $startedAt;
                break;

            case 'completed':
                // Ensure all dates are logical and in the past
                $now = new \DateTime('now');
                $yesterday = (clone $now)->modify('-1 day');
                $createdAtDateTime = $createdAt instanceof \DateTime ? $createdAt : new \DateTime($createdAt);

                // startedAt should be between createdAt and yesterday
                $maxStartDate = $createdAtDateTime < $yesterday
                    ? $createdAtDateTime->modify('+1 hour')  // Start at least 1 hour after creation
                    : $createdAtDateTime;

                $startedAt = $this->faker->dateTimeBetween($createdAtDateTime, '-1 day');

                // completedAt should be between startedAt and now
                $completedAt = $this->faker->dateTimeBetween($startedAt, 'now');

                $flow['started_at'] = $startedAt;
                $flow['completed_at'] = $completedAt;
                $flow['updated_at'] = $completedAt;
                break;

            case 'cancelled':
            default:
                $flow['started_at'] = null;
                $flow['completed_at'] = null;
                $flow['updated_at'] = $this->faker->dateTimeBetween($createdAt, 'now');
                break;
        }

        return $flow;
    }

    private function createDefaultStatuses(): void
    {
        $statuses = [
            ['name' => 'scheduled', 'description' => 'Serviço agendado', 'color' => '#3B82F6', 'sort_order' => 1],
            ['name' => 'in_progress', 'description' => 'Serviço em andamento', 'color' => '#F59E0B', 'sort_order' => 2],
            ['name' => 'completed', 'description' => 'Serviço concluído', 'color' => '#10B981', 'sort_order' => 3],
            ['name' => 'cancelled', 'description' => 'Serviço cancelado', 'color' => '#EF4444', 'sort_order' => 4],
        ];

        foreach ($statuses as $status) {
            ServiceStatus::firstOrCreate(['name' => $status['name']], $status);
        }
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
}
