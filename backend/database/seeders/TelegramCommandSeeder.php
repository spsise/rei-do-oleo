<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Telegram\TelegramCommand;

class TelegramCommandSeeder extends Seeder
{
    //php artisan db:seed --class=TelegramCommandSeeder
    public function run(): void
    {
        $commands = [
            // Basic Commands (highest priority)
            [
                'command_id' => 'help_start',
                'aliases' => ['ajuda', 'help', 'comandos', 'opções', 'iniciar', 'start', 'begin', 'home', 'principal'],
                'description' => 'Comando de ajuda e início',
                'action_handler' => 'App\Services\Telegram\Handlers\StartCommandHandler',
                'action_method' => 'handle',
                'action_parameters' => [],
                'permissions' => ['all'],
                'category' => 'basic',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 1,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'ajuda',
                    'help',
                    'comandos',
                    'opções',
                    'iniciar',
                    'start',
                    'begin',
                    'home',
                    'principal'
                ],
                'fallback' => [
                    'message' => 'Olá! Como posso ajudar você?',
                    'suggestions' => ['Menu', 'Status', 'Relatórios']
                ],
                'is_active' => true,
                'priority' => 1
            ],
            // Menu Navigation Commands
            [
                'command_id' => 'main_menu',
                'aliases' => ['menu', 'main_menu', 'voltar', 'back', 'menu_principal'],
                'description' => 'Comando para envio do menu principal para o usuário',
                'action_handler' => 'App\Services\Telegram\TelegramMenuBuilder',
                'action_method' => 'buildMainMenu',
                'action_parameters' => [],
                'permissions' => ['all'],
                'category' => 'navigation',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 2,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'menu',
                    'main_menu',
                    'voltar',
                    'back',
                    'menu_principal',
                    'mostrar menu',
                    'quero ver o menu',
                    'abrir menu',
                    'menu principal',
                    'voltar ao menu'
                ],
                'fallback' => [
                    'message' => 'Aqui está o menu principal:',
                    'suggestions' => ['Menu', 'Ajuda', 'Relatórios']
                ],
                'is_active' => true,
                'priority' => 2
            ],
            // Dashboard/Status Commands
            [
                'command_id' => 'system_status',
                'aliases' => ['dashboard', 'status', 'como', 'está', 'dashboard_menu', 'sistema', 'verificar sistema', 'status do sistema'],
                'description' => 'Mostra status do sistema',
                'action_handler' => 'App\Services\Telegram\Handlers\StatusCommandHandler',
                'action_method' => 'handleStatus',
                'action_parameters' => [],
                'permissions' => ['all'],
                'category' => 'system',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 3,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'dashboard',
                    'status',
                    'como está',
                    'dashboard_menu',
                    'sistema',
                    'verificar sistema',
                    'status do sistema',
                    'como está o sistema',
                    'sistema funcionando',
                    'status sistema',
                    'verificar status',
                    'mostrar status'
                ],
                'fallback' => [
                    'message' => 'Aqui está o status do sistema:',
                    'suggestions' => ['Menu', 'Relatórios', 'Serviços']
                ],
                'is_active' => true,
                'priority' => 3
            ],
            // Dashboard Menu
            [
                'command_id' => 'dashboard_menu',
                'aliases' => ['dashboard_menu'],
                'description' => 'Menu do dashboard',
                'action_handler' => 'App\Services\Telegram\TelegramMenuBuilder',
                'action_method' => 'buildDashboardMenu',
                'action_parameters' => [],
                'permissions' => ['all'],
                'category' => 'navigation',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 3,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'dashboard menu',
                    'menu dashboard',
                    'painel de controle'
                ],
                'fallback' => [
                    'message' => 'Aqui está o menu do dashboard:',
                    'suggestions' => ['Status', 'Relatórios', 'Menu Principal']
                ],
                'is_active' => true,
                'priority' => 4
            ],
            // Report Commands with Period
            [
                'command_id' => 'report_with_period',
                'aliases' => ['relatório', 'report', 'reports'],
                'description' => 'Relatório com período específico',
                'action_handler' => 'App\Services\Telegram\Handlers\ReportCommandHandler',
                'action_method' => 'handle',
                'action_parameters' => [],
                'permissions' => ['all'],
                'category' => 'reports',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 5,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'relatório hoje',
                    'relatório semana',
                    'relatório mês',
                    'report today',
                    'report week',
                    'report month',
                    'enviar relatório',
                    'quero relatório',
                    'preciso relatório',
                    'mostre relatório',
                    'mostra relatório'
                ],
                'fallback' => [
                    'message' => 'Qual período você gostaria para o relatório?',
                    'suggestions' => ['Hoje', 'Semana', 'Mês']
                ],
                'is_active' => true,
                'priority' => 5
            ],
            // Services Commands
            [
                'command_id' => 'services_with_period',
                'aliases' => ['serviços', 'services'],
                'description' => 'Serviços com período específico',
                'action_handler' => 'App\Services\Telegram\Handlers\ServicesCommandHandler',
                'action_method' => 'handle',
                'action_parameters' => [],
                'permissions' => ['all'],
                'category' => 'services',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 6,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'serviços hoje',
                    'serviços semana',
                    'serviços mês',
                    'services today',
                    'services week',
                    'services month'
                ],
                'fallback' => [
                    'message' => 'Qual período você gostaria para os serviços?',
                    'suggestions' => ['Hoje', 'Semana', 'Mês']
                ],
                'is_active' => true,
                'priority' => 6
            ],
            // Products Commands
            [
                'command_id' => 'products_with_period',
                'aliases' => ['produtos', 'products'],
                'description' => 'Produtos com período específico',
                'action_handler' => 'App\Services\Telegram\Handlers\ProductsCommandHandler',
                'action_method' => 'handle',
                'action_parameters' => [],
                'permissions' => ['all'],
                'category' => 'products',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 7,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'produtos hoje',
                    'produtos semana',
                    'produtos mês',
                    'products today',
                    'products week',
                    'products month'
                ],
                'fallback' => [
                    'message' => 'Qual período você gostaria para os produtos?',
                    'suggestions' => ['Hoje', 'Semana', 'Mês']
                ],
                'is_active' => true,
                'priority' => 7
            ],
            // General Menu Commands
            [
                'command_id' => 'report_menu',
                'aliases' => ['report_menu'],
                'description' => 'Menu de relatórios',
                'action_handler' => 'App\Services\Telegram\TelegramMenuBuilder',
                'action_method' => 'buildReportMenu',
                'action_parameters' => [],
                'permissions' => ['all'],
                'category' => 'navigation',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 8,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'menu relatórios',
                    'relatórios',
                    'report menu'
                ],
                'fallback' => [
                    'message' => 'Aqui está o menu de relatórios:',
                    'suggestions' => ['Relatório Hoje', 'Relatório Semana', 'Menu Principal']
                ],
                'is_active' => true,
                'priority' => 8
            ],
            [
                'command_id' => 'services_menu',
                'aliases' => ['services_menu', 'menu_service', 'Menu de serviços'],
                'description' => 'Exibe menu de serviços disponíveis',
                'action_handler' => 'App\Services\Telegram\TelegramMenuBuilder',
                'action_method' => 'buildServicesMenu',
                'action_parameters' => [],
                'permissions' => ['all'],
                'category' => 'navigation',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 2,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'quero ver serviços',
                    'mostrar serviços',
                    'menu de serviços',
                    'serviços disponíveis',
                    'lista de serviços'
                ],
                'fallback' => [
                    'message' => 'Desculpe, não entendi. Você quer ver os serviços?',
                    'suggestions' => ['Serviços', 'Menu', 'Relatórios']
                ],
                'is_active' => true,
                'priority' => 2
            ],
            [
                'command_id' => 'products_menu',
                'aliases' => ['products_menu', 'menu_produtos', 'Menu de produtos'],
                'description' => 'Exibe menu de produtos disponíveis',
                'action_handler' => 'App\Services\Telegram\TelegramMenuBuilder',
                'action_method' => 'buildProductsMenu',
                'action_parameters' => [],
                'permissions' => ['all'],
                'category' => 'navigation',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 9,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'menu produtos',
                    'produtos',
                    'products menu',
                    'mostrar produtos',
                    'ver produtos'
                ],
                'fallback' => [
                    'message' => 'Aqui está o menu de produtos:',
                    'suggestions' => ['Estoque', 'Produtos Baixo', 'Menu Principal']
                ],
                'is_active' => true,
                'priority' => 9
            ],
            [
                'command_id' => 'reports_menu',
                'aliases' => ['reports', 'relatórios', 'menu_relatorios', 'Menu de relatórios'],
                'description' => 'Exibe menu de relatórios disponíveis',
                'action_handler' => 'App\Services\Telegram\TelegramMenuBuilder',
                'action_method' => 'showReportsMenu',
                'action_parameters' => [],
                'permissions' => ['all'],
                'category' => 'navigation',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 3,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'quero ver relatórios',
                    'mostrar relatórios',
                    'menu de relatórios',
                    'relatórios disponíveis',
                    'lista de relatórios'
                ],
                'fallback' => [
                    'message' => 'Desculpe, não entendi. Você quer ver os relatórios?',
                    'suggestions' => ['Relatórios', 'Menu', 'Serviços']
                ],
                'is_active' => true,
                'priority' => 3
            ],
            [
                'command_id' => 'today_report',
                'aliases' => ['relatório hoje', 'hoje', 'relatório diário', 'daily', 'report today'],
                'description' => 'Gera relatório do dia atual',
                'action_handler' => 'App\Services\Telegram\Reports\GeneralReportGenerator',
                'action_method' => 'generateTodayReport',
                'action_parameters' => ['period' => 'today'],
                'permissions' => ['admin', 'manager', 'user'],
                'category' => 'reports',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 4,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'relatório de hoje',
                    'como foi hoje',
                    'resumo do dia',
                    'relatório diário',
                    'hoje'
                ],
                'fallback' => [
                    'message' => 'Desculpe, não entendi. Você quer o relatório de hoje?',
                    'suggestions' => ['Hoje', 'Semana', 'Mês']
                ],
                'is_active' => true,
                'priority' => 4
            ],
            [
                'command_id' => 'week_report',
                'aliases' => ['relatório semana', 'semana', 'relatório semanal', 'weekly', 'report week'],
                'description' => 'Gera relatório da semana atual',
                'action_handler' => 'App\Services\Telegram\Reports\GeneralReportGenerator',
                'action_method' => 'generateWeekReport',
                'action_parameters' => ['period' => 'week'],
                'permissions' => ['admin', 'manager', 'user'],
                'category' => 'reports',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 5,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'relatório da semana',
                    'como foi a semana',
                    'resumo semanal',
                    'relatório semanal',
                    'semana'
                ],
                'fallback' => [
                    'message' => 'Desculpe, não entendi. Você quer o relatório da semana?',
                    'suggestions' => ['Semana', 'Hoje', 'Mês']
                ],
                'is_active' => true,
                'priority' => 5
            ],
            [
                'command_id' => 'month_report',
                'aliases' => ['relatório mês', 'mês', 'relatório mensal', 'monthly', 'report month'],
                'description' => 'Gera relatório do mês atual',
                'action_handler' => 'App\Services\Telegram\Reports\GeneralReportGenerator',
                'action_method' => 'generateMonthReport',
                'action_parameters' => ['period' => 'month'],
                'permissions' => ['admin', 'manager', 'user'],
                'category' => 'reports',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 6,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'relatório do mês',
                    'como foi o mês',
                    'resumo mensal',
                    'relatório mensal',
                    'mês'
                ],
                'fallback' => [
                    'message' => 'Desculpe, não entendi. Você quer o relatório do mês?',
                    'suggestions' => ['Mês', 'Hoje', 'Semana']
                ],
                'is_active' => true,
                'priority' => 6
            ],
            [
                'command_id' => 'services_report',
                'aliases' => ['relatório serviços', 'serviços relatório', 'services report'],
                'description' => 'Gera relatório específico de serviços',
                'action_handler' => 'App\Services\Telegram\Reports\ServicesReportGenerator',
                'action_method' => 'generateReport',
                'action_parameters' => ['type' => 'services'],
                'permissions' => ['admin', 'manager'],
                'category' => 'reports',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 7,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'relatório de serviços',
                    'serviços relatório',
                    'como estão os serviços',
                    'status dos serviços'
                ],
                'fallback' => [
                    'message' => 'Desculpe, não entendi. Você quer o relatório de serviços?',
                    'suggestions' => ['Serviços', 'Geral', 'Produtos']
                ],
                'is_active' => true,
                'priority' => 7
            ],
            [
                'command_id' => 'products_report',
                'aliases' => ['relatório produtos', 'produtos relatório', 'products report'],
                'description' => 'Gera relatório específico de produtos',
                'action_handler' => 'App\Services\Telegram\Reports\ProductsReportGenerator',
                'action_method' => 'generateReport',
                'action_parameters' => ['type' => 'products'],
                'permissions' => ['admin', 'manager'],
                'category' => 'reports',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 8,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'relatório de produtos',
                    'produtos relatório',
                    'como estão os produtos',
                    'status dos produtos',
                    'estoque'
                ],
                'fallback' => [
                    'message' => 'Desculpe, não entendi. Você quer o relatório de produtos?',
                    'suggestions' => ['Produtos', 'Geral', 'Serviços']
                ],
                'is_active' => true,
                'priority' => 8
            ],
            [
                'command_id' => 'system_status',
                'aliases' => ['status', 'status sistema', 'system status', 'como está o sistema'],
                'description' => 'Mostra status atual do sistema',
                'action_handler' => 'App\Services\Telegram\Handlers\StatusCommandHandler',
                'action_method' => 'handle',
                'action_parameters' => [],
                'permissions' => ['admin', 'manager'],
                'category' => 'system',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 9,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'como está o sistema',
                    'status do sistema',
                    'sistema status',
                    'verificar sistema',
                    'sistema ok'
                ],
                'fallback' => [
                    'message' => 'Desculpe, não entendi. Você quer ver o status do sistema?',
                    'suggestions' => ['Status', 'Menu', 'Ajuda']
                ],
                'is_active' => true,
                'priority' => 9
            ],
            [
                'command_id' => 'help',
                'aliases' => ['help', 'ajuda', 'socorro', 'comandos', 'commands'],
                'description' => 'Mostra ajuda e lista de comandos disponíveis',
                'action_handler' => 'App\Services\Telegram\Handlers\StartCommandHandler',
                'action_method' => 'showHelp',
                'action_parameters' => [],
                'permissions' => ['all'],
                'category' => 'help',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 10,
                    'noise_reduction' => true,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'preciso de ajuda',
                    'quero ajuda',
                    'mostrar ajuda',
                    'comandos disponíveis',
                    'como usar',
                    'socorro'
                ],
                'fallback' => [
                    'message' => 'Desculpe, não entendi. Você quer ajuda?',
                    'suggestions' => ['Ajuda', 'Menu', 'Comandos']
                ],
                'is_active' => true,
                'priority' => 10
            ],
            [
                'command_id' => 'voice_test',
                'aliases' => ['testvoice', 'teste voz', 'teste de voz', 'voice test'],
                'description' => 'Comando oculto para testar funcionalidades de voz',
                'action_handler' => 'App\Services\Telegram\Handlers\VoiceCommandHandler',
                'action_method' => 'testVoice',
                'action_parameters' => [],
                'permissions' => ['admin'],
                'category' => 'system',
                'voice_settings' => [
                    'enabled' => true,
                    'priority' => 99,
                    'noise_reduction' => false,
                    'language' => ['pt', 'en']
                ],
                'natural_language' => [
                    'teste de voz',
                    'testar voz',
                    'verificar voz',
                    'teste voz'
                ],
                'fallback' => [
                    'message' => 'Comando de teste de voz não reconhecido',
                    'suggestions' => []
                ],
                'is_active' => true,
                'priority' => 99
            ]
        ];

        foreach ($commands as $commandData) {
            TelegramCommand::updateOrCreate(
                ['command_id' => $commandData['command_id']],
                $commandData
            );
        }

        $this->command->info('Telegram commands seeded successfully!');
    }
}
