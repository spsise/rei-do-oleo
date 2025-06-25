<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceCenterSeeder extends Seeder
{
    public function run(): void
    {
        $serviceCenters = [
            [
                'code' => 'RDO001',
                'name' => 'Rei do Óleo - Matriz',
                'slug' => Str::slug('Rei do Óleo - Matriz'),
                'cnpj' => '12.345.678/0001-90',
                'state_registration' => '123456789',
                'legal_name' => 'Rei do Óleo Serviços Automotivos Ltda',
                'trade_name' => 'Rei do Óleo - Matriz',
                'address_line' => 'Rua das Oficinas',
                'number' => '123',
                'complement' => null,
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01000-000',
                'latitude' => -23.5505,
                'longitude' => -46.6333,
                'phone' => '(11) 3333-4444',
                'whatsapp' => '(11) 99999-8888',
                'email' => 'matriz@reidooleo.com.br',
                'website' => 'https://reidooleo.com.br',
                'facebook_url' => 'https://facebook.com/reidooleo',
                'instagram_url' => 'https://instagram.com/reidooleo',
                'google_maps_url' => 'https://maps.google.com/?q=-23.5505,-46.6333',
                'manager_id' => null, // Will be set after users are created
                'technical_responsible' => 'João Silva (CREA 123456)',
                'opening_date' => '2020-01-15',
                'operating_hours' => json_encode([
                    'monday' => '08:00-18:00',
                    'tuesday' => '08:00-18:00',
                    'wednesday' => '08:00-18:00',
                    'thursday' => '08:00-18:00',
                    'friday' => '08:00-18:00',
                    'saturday' => '08:00-12:00',
                    'sunday' => 'closed'
                ]),
                'is_main_branch' => true,
                'active' => true,
                'observations' => 'Unidade principal com todos os serviços disponíveis',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'RDO002',
                'name' => 'Rei do Óleo - Vila Madalena',
                'slug' => Str::slug('Rei do Óleo - Vila Madalena'),
                'cnpj' => '12.345.678/0002-71',
                'state_registration' => '123456790',
                'legal_name' => 'Rei do Óleo Serviços Automotivos Ltda',
                'trade_name' => 'Rei do Óleo - Vila Madalena',
                'address_line' => 'Rua Harmonia',
                'number' => '456',
                'complement' => 'Loja 2',
                'neighborhood' => 'Vila Madalena',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '05435-000',
                'latitude' => -23.5570,
                'longitude' => -46.6892,
                'phone' => '(11) 3333-5555',
                'whatsapp' => '(11) 99999-7777',
                'email' => 'vilamadalena@reidooleo.com.br',
                'website' => 'https://reidooleo.com.br',
                'facebook_url' => 'https://facebook.com/reidooleo',
                'instagram_url' => 'https://instagram.com/reidooleo',
                'google_maps_url' => 'https://maps.google.com/?q=-23.5570,-46.6892',
                'manager_id' => null, // Will be set after users are created
                'technical_responsible' => 'Maria Santos (CREA 654321)',
                'opening_date' => '2021-06-10',
                'operating_hours' => json_encode([
                    'monday' => '08:00-18:00',
                    'tuesday' => '08:00-18:00',
                    'wednesday' => '08:00-18:00',
                    'thursday' => '08:00-18:00',
                    'friday' => '08:00-18:00',
                    'saturday' => '08:00-16:00',
                    'sunday' => 'closed'
                ]),
                'is_main_branch' => false,
                'active' => true,
                'observations' => 'Unidade especializada em troca de óleo e filtros',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'RDO003',
                'name' => 'Rei do Óleo - Santo André',
                'slug' => Str::slug('Rei do Óleo - Santo André'),
                'cnpj' => '12.345.678/0003-52',
                'state_registration' => '123456791',
                'legal_name' => 'Rei do Óleo Serviços Automotivos Ltda',
                'trade_name' => 'Rei do Óleo - Santo André',
                'address_line' => 'Avenida Industrial',
                'number' => '789',
                'complement' => null,
                'neighborhood' => 'Santa Teresinha',
                'city' => 'Santo André',
                'state' => 'SP',
                'zip_code' => '09210-000',
                'latitude' => -23.6528,
                'longitude' => -46.5388,
                'phone' => '(11) 3333-6666',
                'whatsapp' => '(11) 99999-6666',
                'email' => 'santoandre@reidooleo.com.br',
                'website' => 'https://reidooleo.com.br',
                'facebook_url' => 'https://facebook.com/reidooleo',
                'instagram_url' => 'https://instagram.com/reidooleo',
                'google_maps_url' => 'https://maps.google.com/?q=-23.6528,-46.5388',
                'manager_id' => null, // Will be set after users are created
                'technical_responsible' => 'Carlos Oliveira (CREA 789123)',
                'opening_date' => '2022-03-20',
                'operating_hours' => json_encode([
                    'monday' => '07:30-17:30',
                    'tuesday' => '07:30-17:30',
                    'wednesday' => '07:30-17:30',
                    'thursday' => '07:30-17:30',
                    'friday' => '07:30-17:30',
                    'saturday' => '08:00-14:00',
                    'sunday' => 'closed'
                ]),
                'is_main_branch' => false,
                'active' => true,
                'observations' => 'Unidade com foco em atendimento empresarial',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('service_centers')->insert($serviceCenters);
    }
}
