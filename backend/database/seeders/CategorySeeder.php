<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Óleos Lubrificantes',
                'slug' => Str::slug('Óleos Lubrificantes'),
                'description' => 'Óleos para motor, câmbio, freio e direção hidráulica',
                'active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Filtros',
                'slug' => Str::slug('Filtros'),
                'description' => 'Filtros de óleo, ar, combustível e cabine',
                'active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fluidos Automotivos',
                'slug' => Str::slug('Fluidos Automotivos'),
                'description' => 'Fluidos de freio, radiador, limpador e direção',
                'active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Serviços',
                'slug' => Str::slug('Serviços'),
                'description' => 'Mão de obra e serviços especializados',
                'active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Acessórios',
                'slug' => Str::slug('Acessórios'),
                'description' => 'Peças e acessórios automotivos',
                'active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('categories')->insert($categories);
    }
}
