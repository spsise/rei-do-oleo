<?php

namespace App\Services\Telegram\Handlers;

use App\Contracts\Telegram\TelegramCommandHandlerInterface;
use App\Services\SpeechToTextService;
use App\Services\Channels\TelegramChannel;
use App\Services\Telegram\TelegramMenuBuilder;
use Illuminate\Support\Facades\Log;

class VoiceCommandHandler implements TelegramCommandHandlerInterface
{
    private const HIDDEN_COMMANDS = [
        'testvoice',
        'test_voice',
        'voice_test',
        'testvoz',
        'test_voz',
        'voz_test',
        'enablevoice',
        'enable_voice',
        'voice_enable',
        'ativarvoz',
        'ativar_voz',
        'voz_ativar',
        'voice_status',
        'status_voz',
        'voz_status'
    ];

    public function __construct(
        private SpeechToTextService $speechService,
        private TelegramChannel $telegramChannel,
        private TelegramMenuBuilder $menuBuilder
    ) {}

    /**
     * Check if handler can handle this command
     */
    public function canHandle(string $command): bool
    {
        return in_array(strtolower($command), self::HIDDEN_COMMANDS);
    }

    /**
     * Get command name
     */
    public function getCommandName(): string
    {
        return 'voice_commands';
    }

    /**
     * Get command description
     */
    public function getCommandDescription(): string
    {
        return 'Comandos ocultos para gerenciar funcionalidades de voz';
    }

    /**
     * Handle command
     */
    public function handle(int $chatId, array $params = []): array
    {
        $command = strtolower($params['command'] ?? '');

        Log::info('Voice command handler called', [
            'chat_id' => $chatId,
            'command' => $command,
            'params' => $params
        ]);

        return match($command) {
            'testvoice', 'test_voice', 'voice_test', 'testvoz', 'test_voz', 'voz_test' => $this->testVoiceService($chatId),
            'enablevoice', 'enable_voice', 'voice_enable', 'ativarvoz', 'ativar_voz', 'voz_ativar' => $this->enableVoiceService($chatId),
            'voice_status', 'status_voz', 'voz_status' => $this->getVoiceStatus($chatId),
            default => $this->getHelpMessage($chatId)
        };
    }

    /**
     * Test voice service functionality
     */
    private function testVoiceService(int $chatId): array
    {
        try {
            // Test connection to speech service
            $testResult = $this->speechService->testConnection();

            if ($testResult['success']) {
                $message = "✅ **Teste de Voz - SUCESSO**\n\n";
                $message .= "🎤 **Provider**: {$testResult['provider']}\n";
                $message .= "📝 **Resultado do teste**: {$testResult['test_result']}\n";
                $message .= "🔧 **Status**: Funcionando corretamente\n\n";
                $message .= "💡 **Como usar**: Envie uma mensagem de voz para testar o reconhecimento.";
            } else {
                $message = "❌ **Teste de Voz - FALHOU**\n\n";
                $message .= "🎤 **Provider**: {$testResult['provider']}\n";
                $message .= "🚨 **Erro**: {$testResult['error']}\n";
                $message .= "🔧 **Status**: Requer configuração\n\n";
                $message .= "💡 **Solução**: Use o comando `/enablevoice` para ativar.";
            }

            $keyboard = [
                [
                    ['text' => '🔧 Ativar Voz', 'callback_data' => 'enablevoice'],
                    ['text' => '📊 Status', 'callback_data' => 'voice_status']
                ],
                [
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, (string) $chatId, $keyboard);

        } catch (\Exception $e) {
            Log::error('Voice test failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);

            $message = "❌ **Erro no teste de voz**: {$e->getMessage()}";

            $keyboard = [
                [
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, (string) $chatId, $keyboard);
        }
    }

    /**
     * Enable voice service
     */
    private function enableVoiceService(int $chatId): array
    {
        try {
            // Get available providers
            $providers = $this->speechService->getAvailableProviders();
            $enabledProviders = [];

            foreach ($providers as $provider => $info) {
                $status = $this->speechService->getProviderStatus($provider);

                if ($status['configured']) {
                    $enabledProviders[] = $provider;
                }
            }

            if (empty($enabledProviders)) {
                $message = "❌ **Nenhum provider de voz configurado**\n\n";
                $message .= "🔧 **Providers disponíveis**:\n";

                foreach ($providers as $provider => $info) {
                    $status = $this->speechService->getProviderStatus($provider);
                    $statusIcon = $status['configured'] ? '✅' : '❌';
                    $message .= "{$statusIcon} **{$info['name']}** ({$info['type']} - {$info['cost']})\n";
                }

                $message .= "\n💡 **Para ativar**: Configure pelo menos um provider no sistema.";
            } else {
                $message = "✅ **Serviço de voz ativado**\n\n";
                $message .= "🎤 **Providers ativos**:\n";

                foreach ($enabledProviders as $provider) {
                    $info = $providers[$provider];
                    $message .= "✅ **{$info['name']}** ({$info['type']} - {$info['cost']})\n";
                }

                $message .= "\n💡 **Como usar**: Envie uma mensagem de voz para testar o reconhecimento.";
            }

            $keyboard = [
                [
                    ['text' => '🧪 Testar Voz', 'callback_data' => 'testvoice'],
                    ['text' => '📊 Status', 'callback_data' => 'voice_status']
                ],
                [
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, (string) $chatId, $keyboard);

        } catch (\Exception $e) {
            Log::error('Voice activation failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);

            $message = "❌ **Erro ao ativar voz**: {$e->getMessage()}";

            $keyboard = [
                [
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, (string) $chatId, $keyboard);
        }
    }

    /**
     * Get voice service status
     */
    private function getVoiceStatus(int $chatId): array
    {
        try {
            $providers = $this->speechService->getAvailableProviders();
            $message = "📊 **Status do Serviço de Voz**\n\n";

            foreach ($providers as $provider => $info) {
                $status = $this->speechService->getProviderStatus($provider);
                $statusIcon = $status['configured'] ? '✅' : '❌';
                $statusText = $status['configured'] ? 'Ativo' : 'Inativo';

                $message .= "{$statusIcon} **{$info['name']}**\n";
                $message .= "   📋 Tipo: {$info['type']}\n";
                $message .= "   💰 Custo: {$info['cost']}\n";
                $message .= "   🎯 Precisão: {$info['accuracy']}\n";
                $message .= "   ⚡ Velocidade: {$info['speed']}\n";
                $message .= "   🔧 Status: {$statusText}\n\n";
            }

            $message .= "💡 **Comandos ocultos disponíveis**:\n";
            $message .= "• `/testvoice` - Testar serviço de voz\n";
            $message .= "• `/enablevoice` - Ativar serviço de voz\n";
            $message .= "• `/voice_status` - Ver este status";

            $keyboard = [
                [
                    ['text' => '🧪 Testar Voz', 'callback_data' => 'testvoice'],
                    ['text' => '🔧 Ativar Voz', 'callback_data' => 'enablevoice']
                ],
                [
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, (string) $chatId, $keyboard);

        } catch (\Exception $e) {
            Log::error('Voice status check failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);

            $message = "❌ **Erro ao verificar status**: {$e->getMessage()}";

            $keyboard = [
                [
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            return $this->telegramChannel->sendMessageWithKeyboard($message, (string) $chatId, $keyboard);
        }
    }

    /**
     * Get help message for hidden commands
     */
    private function getHelpMessage(int $chatId): array
    {
        $message = "🤫 **Comandos Ocultos de Voz**\n\n";
        $message .= "Estes comandos são para uso interno e não aparecem no menu:\n\n";
        $message .= "🧪 **Testar voz**:\n";
        $message .= "• `/testvoice` ou `/testvoz`\n\n";
        $message .= "🔧 **Ativar voz**:\n";
        $message .= "• `/enablevoice` ou `/ativarvoz`\n\n";
        $message .= "📊 **Status da voz**:\n";
        $message .= "• `/voice_status` ou `/status_voz`\n\n";
        $message .= "💡 **Lembrete**: Estes comandos são ocultos e só funcionam para usuários autorizados.";

        $keyboard = [
            [
                ['text' => '🧪 Testar Voz', 'callback_data' => 'testvoice'],
                ['text' => '🔧 Ativar Voz', 'callback_data' => 'enablevoice']
            ],
            [
                ['text' => '📊 Status', 'callback_data' => 'voice_status']
            ],
            [
                ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, (string) $chatId, $keyboard);
    }
}
