<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Domain\User\Models\User;
use App\Domain\Service\Models\ServiceCenter;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar o primeiro service center para associar aos usuários
        $serviceCenter = ServiceCenter::first();

        if (!$serviceCenter) {
            $this->command->error('Nenhum Service Center encontrado. Execute o ServiceCenterSeeder primeiro.');
            return;
        }

        // Criar usuário Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador do Sistema',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'),
                'phone' => '(11) 99999-0001',
                'service_center_id' => $serviceCenter->id,
                'active' => true,
            ]
        );

        // Atribuir role de admin
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        if ($adminRole && !$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }

        // Criar usuário Gerente
        $manager = User::updateOrCreate(
            ['email' => 'gerente@admin.com'],
            [
                'name' => 'Gerente Operacional',
                'email_verified_at' => now(),
                'password' => Hash::make('gerente123'),
                'phone' => '(11) 99999-0002',
                'service_center_id' => $serviceCenter->id,
                'active' => true,
            ]
        );

        // Atribuir role de manager
        $managerRole = Role::where('name', 'manager')->where('guard_name', 'web')->first();
        if ($managerRole && !$manager->hasRole('manager')) {
            $manager->assignRole($managerRole);
        }

        // Criar usuário Atendente (exemplo adicional)
        $attendant = User::updateOrCreate(
            ['email' => 'atendente@admin.com'],
            [
                'name' => 'Atendente Principal',
                'email_verified_at' => now(),
                'password' => Hash::make('atendente123'),
                'phone' => '(11) 99999-0003',
                'service_center_id' => $serviceCenter->id,
                'active' => true,
            ]
        );

        // Atribuir role de attendant
        $attendantRole = Role::where('name', 'attendant')->where('guard_name', 'web')->first();
        if ($attendantRole && !$attendant->hasRole('attendant')) {
            $attendant->assignRole($attendantRole);
        }

        // Criar usuário Técnico (exemplo adicional)
        $technician = User::updateOrCreate(
            ['email' => 'tecnico@admin.com'],
            [
                'name' => 'Técnico Senior',
                'email_verified_at' => now(),
                'password' => Hash::make('tecnico123'),
                'phone' => '(11) 99999-0004',
                'service_center_id' => $serviceCenter->id,
                'active' => true,
            ]
        );

        // Atribuir role de technician
        $technicianRole = Role::where('name', 'technician')->where('guard_name', 'web')->first();
        if ($technicianRole && !$technician->hasRole('technician')) {
            $technician->assignRole($technicianRole);
        }

        // Exibir informações dos usuários criados
        $this->command->info('Usuários administrativos criados com sucesso:');
        $this->command->line('');
        $this->command->info('👑 ADMINISTRADOR:');
        $this->command->line('   Email: admin@admin.com');
        $this->command->line('   Senha: admin123');
        $this->command->line('');
        $this->command->info('👨‍💼 GERENTE:');
        $this->command->line('   Email: gerente@admin.com');
        $this->command->line('   Senha: gerente123');
        $this->command->line('');
        $this->command->info('👨‍💻 ATENDENTE:');
        $this->command->line('   Email: atendente@admin.com');
        $this->command->line('   Senha: atendente123');
        $this->command->line('');
        $this->command->info('🔧 TÉCNICO:');
        $this->command->line('   Email: tecnico@admin.com');
        $this->command->line('   Senha: tecnico123');
        $this->command->line('');
        $this->command->warn('⚠️  IMPORTANTE: Altere as senhas padrão em produção!');
    }
}
