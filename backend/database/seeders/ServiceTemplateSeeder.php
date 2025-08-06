<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Service\Models\ServiceTemplate;

class ServiceTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Manutenção
            [
                'name' => 'Troca de Óleo e Filtro',
                'description' => 'Troca completa de óleo do motor e filtros (óleo e ar)',
                'category' => 'maintenance',
                'estimated_duration' => 60,
                'priority' => 'medium',
                'notes' => 'Verificar nível de óleo após troca. Recomendado a cada 5.000 km.',
                'service_items' => [
                    [
                        'product_name' => 'Óleo Motor 5W30',
                        'quantity' => 1,
                        'unit_price' => 89.90,
                        'notes' => 'Óleo sintético premium'
                    ],
                    [
                        'product_name' => 'Filtro de Óleo',
                        'quantity' => 1,
                        'unit_price' => 25.00,
                        'notes' => 'Filtro de qualidade'
                    ],
                    [
                        'product_name' => 'Filtro de Ar',
                        'quantity' => 1,
                        'unit_price' => 35.00,
                        'notes' => 'Filtro de ar do motor'
                    ]
                ],
                'sort_order' => 1
            ],
            [
                'name' => 'Revisão Completa',
                'description' => 'Revisão geral do veículo incluindo fluidos, filtros e componentes',
                'category' => 'maintenance',
                'estimated_duration' => 120,
                'priority' => 'medium',
                'notes' => 'Revisão completa a cada 10.000 km. Inclui verificação de todos os sistemas.',
                'service_items' => [
                    [
                        'product_name' => 'Óleo Motor 5W30',
                        'quantity' => 1,
                        'unit_price' => 89.90,
                        'notes' => 'Óleo sintético premium'
                    ],
                    [
                        'product_name' => 'Filtro de Óleo',
                        'quantity' => 1,
                        'unit_price' => 25.00,
                        'notes' => 'Filtro de qualidade'
                    ],
                    [
                        'product_name' => 'Filtro de Ar',
                        'quantity' => 1,
                        'unit_price' => 35.00,
                        'notes' => 'Filtro de ar do motor'
                    ],
                    [
                        'product_name' => 'Filtro de Combustível',
                        'quantity' => 1,
                        'unit_price' => 45.00,
                        'notes' => 'Filtro de combustível'
                    ]
                ],
                'sort_order' => 2
            ],
            [
                'name' => 'Troca de Freios',
                'description' => 'Substituição de pastilhas e discos de freio',
                'category' => 'maintenance',
                'estimated_duration' => 90,
                'priority' => 'high',
                'notes' => 'Verificar desgaste dos freios. Substituir quando necessário.',
                'service_items' => [
                    [
                        'product_name' => 'Pastilhas de Freio Dianteiras',
                        'quantity' => 1,
                        'unit_price' => 120.00,
                        'notes' => 'Pastilhas de freio dianteiras'
                    ],
                    [
                        'product_name' => 'Pastilhas de Freio Traseiras',
                        'quantity' => 1,
                        'unit_price' => 100.00,
                        'notes' => 'Pastilhas de freio traseiras'
                    ]
                ],
                'sort_order' => 3
            ],

            // Reparo
            [
                'name' => 'Diagnóstico Elétrico',
                'description' => 'Diagnóstico completo do sistema elétrico do veículo',
                'category' => 'repair',
                'estimated_duration' => 60,
                'priority' => 'medium',
                'notes' => 'Usar scanner profissional para diagnóstico. Verificar códigos de erro.',
                'sort_order' => 4
            ],
            [
                'name' => 'Reparo de Motor',
                'description' => 'Reparo e ajustes no motor do veículo',
                'category' => 'repair',
                'estimated_duration' => 240,
                'priority' => 'high',
                'notes' => 'Reparo complexo. Verificar peças necessárias antes de iniciar.',
                'sort_order' => 5
            ],
            [
                'name' => 'Reparo de Transmissão',
                'description' => 'Reparo e manutenção da transmissão',
                'category' => 'repair',
                'estimated_duration' => 180,
                'priority' => 'high',
                'notes' => 'Trabalho especializado. Verificar fluido da transmissão.',
                'sort_order' => 6
            ],

            // Inspeção
            [
                'name' => 'Inspeção de Segurança',
                'description' => 'Inspeção completa dos itens de segurança do veículo',
                'category' => 'inspection',
                'estimated_duration' => 45,
                'priority' => 'medium',
                'notes' => 'Verificar freios, pneus, suspensão e itens de segurança.',
                'sort_order' => 7
            ],
            [
                'name' => 'Inspeção Pré-Compra',
                'description' => 'Inspeção completa para veículo usado',
                'category' => 'inspection',
                'estimated_duration' => 90,
                'priority' => 'medium',
                'notes' => 'Inspeção detalhada para compra de veículo usado.',
                'sort_order' => 8
            ],

            // Emergência
            [
                'name' => 'Reparo de Emergência',
                'description' => 'Reparo urgente para veículo com problema crítico',
                'category' => 'emergency',
                'estimated_duration' => 120,
                'priority' => 'high',
                'notes' => 'Serviço de emergência. Prioridade máxima.',
                'sort_order' => 9
            ],
            [
                'name' => 'Recuperação de Veículo',
                'description' => 'Recuperação e reparo de veículo com problema grave',
                'category' => 'emergency',
                'estimated_duration' => 300,
                'priority' => 'high',
                'notes' => 'Recuperação de veículo com problema grave. Verificar disponibilidade de peças.',
                'sort_order' => 10
            ],

            // Preventiva
            [
                'name' => 'Manutenção Preventiva',
                'description' => 'Manutenção preventiva programada',
                'category' => 'preventive',
                'estimated_duration' => 60,
                'priority' => 'low',
                'notes' => 'Manutenção preventiva para evitar problemas futuros.',
                'sort_order' => 11
            ],
            [
                'name' => 'Troca de Fluidos',
                'description' => 'Troca de todos os fluidos do veículo',
                'category' => 'preventive',
                'estimated_duration' => 90,
                'priority' => 'medium',
                'notes' => 'Troca de óleo, fluido de freio, direção hidráulica e arrefecimento.',
                'service_items' => [
                    [
                        'product_name' => 'Óleo Motor 5W30',
                        'quantity' => 1,
                        'unit_price' => 89.90,
                        'notes' => 'Óleo sintético premium'
                    ],
                    [
                        'product_name' => 'Fluido de Freio',
                        'quantity' => 1,
                        'unit_price' => 25.00,
                        'notes' => 'Fluido de freio DOT4'
                    ],
                    [
                        'product_name' => 'Fluido de Direção',
                        'quantity' => 1,
                        'unit_price' => 35.00,
                        'notes' => 'Fluido de direção hidráulica'
                    ]
                ],
                'sort_order' => 12
            ]
        ];

        foreach ($templates as $template) {
            ServiceTemplate::create($template);
        }
    }
}
