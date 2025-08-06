<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Service\Models\ServiceStatus;

class ServiceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'scheduled',
                'description' => 'Serviço agendado',
                'color' => '#3B82F6', // Blue
                'sort_order' => 1,
            ],
            [
                'name' => 'in_progress',
                'description' => 'Serviço em andamento',
                'color' => '#F59E0B', // Amber
                'sort_order' => 2,
            ],
            [
                'name' => 'completed',
                'description' => 'Serviço concluído',
                'color' => '#10B981', // Green
                'sort_order' => 3,
            ],
            [
                'name' => 'cancelled',
                'description' => 'Serviço cancelado',
                'color' => '#EF4444', // Red
                'sort_order' => 4,
            ],
            [
                'name' => 'pending',
                'description' => 'Aguardando aprovação',
                'color' => '#6B7280', // Gray
                'sort_order' => 0,
            ],
        ];

        foreach ($statuses as $status) {
            ServiceStatus::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}
