<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Domain\User\Models\User;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar usuário de teste se não existir
        User::firstOrCreate(
            ['email' => 'admin@reidooleo.com'],
            [
                'name' => 'Admin Rei do Óleo',
                'email' => 'admin@reidooleo.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Usuário Teste',
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'joao@example.com'],
            [
                'name' => 'João Silva',
                'email' => 'joao@example.com',
                'password' => Hash::make('MinhaSenh@123'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuários de teste criados com sucesso!');
        $this->command->info('Email: admin@reidooleo.com | Senha: password123');
        $this->command->info('Email: test@example.com | Senha: password123');
        $this->command->info('Email: joao@example.com | Senha: MinhaSenh@123');
    }
}
