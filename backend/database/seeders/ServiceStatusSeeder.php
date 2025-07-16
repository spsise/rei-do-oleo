<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'scheduled',
                'description' => 'Serviço agendado',
                'color' => '#3B82F6', // Blue
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'in_progress',
                'description' => 'Serviço em andamento',
                'color' => '#F59E0B', // Yellow
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'completed',
                'description' => 'Serviço concluído',
                'color' => '#10B981', // Green
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cancelled',
                'description' => 'Serviço cancelado',
                'color' => '#EF4444', // Red
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('service_statuses')->insert($statuses);
    }
}
