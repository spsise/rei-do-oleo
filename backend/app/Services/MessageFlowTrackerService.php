<?php

namespace App\Services;

use App\Contracts\MessageFlowTrackerInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MessageFlowTrackerService implements MessageFlowTrackerInterface
{
    private array $executedMethods = [];
    private array $expectedMethods = [];
    private array $methodData = [];
    private array $timestamps = [];
    private string $messageId;
    private bool $isEnabled;

    public function __construct()
    {
        $this->isEnabled = config('message-flow.enabled', false);
        $this->messageId = uniqid('msg_', true);

        // Define o fluxo esperado para cada tipo de mensagem
        $this->expectedMethods = [
            'text' => [
                'TelegramMessageProcessorService.processWebhookPayload',
                'TelegramMessageProcessorService.processTextMessage',
                'TelegramBotService.processMessage',
                // UnifiedCommandSystem is the current command processor; optional to track
                // 'UnifiedCommandSystem.processCommand',
                // Response can be sent via keyboard or plain text; we track keyboard variant
                'TelegramChannel.sendMessageWithKeyboard'
            ],
            'voice' => [
                'TelegramMessageProcessorService.processWebhookPayload',
                'TelegramMessageProcessorService.processVoiceMessage',
                'SpeechToTextService.convertVoiceToText',
                'TelegramBotService.processMessage',
                'TelegramChannel.sendTextMessage'
            ],
            'callback_query' => [
                'TelegramMessageProcessorService.processWebhookPayload',
                'TelegramMessageProcessorService.processCallbackQuery',
                'TelegramBotService.processCallbackQuery',
                'TelegramChannel.sendTextMessage'
            ]
        ];
    }

    /**
     * Método interno para tracking (usado pelo trait)
     */
    public function trackMethodInternal(string $className, string $methodName, array $inputData = [], array $outputData = []): void
    {
        $this->trackMethod($className, $methodName, $inputData, $outputData);
    }

    /**
     * Registra que um método foi executado
     */
    public function trackMethod(string $className, string $methodName, array $inputData = [], array $outputData = []): void
    {
        if (!$this->isEnabled) return;

        $methodKey = "{$className}.{$methodName}";
        $startTime = microtime(true);

        $this->executedMethods[] = $methodKey;
        $this->methodData[$methodKey] = [
            'input' => $inputData,
            'output' => $outputData,
            'start_time' => $startTime,
            'execution_time' => 0
        ];

        $this->timestamps[$methodKey] = $startTime;

        Log::info("Message flow: Method executed", [
            'message_id' => $this->messageId,
            'method' => $methodKey,
            'input_data' => $inputData
        ]);
    }

    /**
     * Método interno para finalizar tracking (usado pelo trait)
     */
    public function endMethodInternal(string $className, string $methodName, array $outputData = []): void
    {
        $this->endMethod($className, $methodName, $outputData);
    }

    /**
     * Finaliza o tracking de um método
     */
    public function endMethod(string $className, string $methodName, array $outputData = []): void
    {
        if (!$this->isEnabled) return;

        $methodKey = "{$className}.{$methodName}";

        if (isset($this->methodData[$methodKey])) {
            $endTime = microtime(true);
            $startTime = $this->methodData[$methodKey]['start_time'];
            $executionTime = ($endTime - $startTime) * 1000; // em milissegundos

            $this->methodData[$methodKey]['output'] = $outputData;
            $this->methodData[$methodKey]['execution_time'] = $executionTime;
            $this->methodData[$methodKey]['end_time'] = $endTime;
        }
    }

    /**
     * Identifica o tipo de mensagem baseado no payload
     */
    public function identifyMessageType(array $payload): string
    {
        if (isset($payload['callback_query'])) return 'callback_query';
        if (isset($payload['message']['voice'])) return 'voice';
        if (isset($payload['message']['text'])) return 'text';
        return 'unknown';
    }

    /**
     * Gera relatório completo do fluxo
     */
    public function generateFlowReport(array $payload): string
    {
        if (!$this->isEnabled) return '';

        $messageType = $this->identifyMessageType($payload);
        $expectedMethods = $this->expectedMethods[$messageType] ?? [];

        $report = "🔄 **Rastreamento da Mensagem**\n\n";
        $report .= "📋 **Informações Gerais**\n";
        $report .= "• ID da Mensagem: `{$this->messageId}`\n";
        $report .= "• Tipo: " . $this->getMessageTypeEmoji($messageType) . " " . strtoupper($messageType) . "\n";
        $report .= "• Conteúdo: " . $this->extractMessageContent($payload) . "\n";
        $report .= "• Timestamp: " . now()->format('H:i:s.u') . "\n\n";

        $report .= "✅ **Métodos Executados**\n";
        if (empty($this->executedMethods)) {
            $report .= "❌ Nenhum método foi executado!\n\n";
        } else {
            foreach ($this->executedMethods as $index => $method) {
                $methodData = $this->methodData[$method] ?? [];
                $executionTime = $methodData['execution_time'] ?? 0;

                $report .= ($index + 1) . ". **{$method}**\n";
                $report .= "   ⏱️ Tempo: " . round($executionTime, 2) . "ms\n";

                if (!empty($methodData['input'])) {
                    $report .= "   📥 Entrada: " . $this->summarizeData($methodData['input']) . "\n";
                }

                if (!empty($methodData['output'])) {
                    $report .= "   📤 Saída: " . $this->summarizeData($methodData['output']) . "\n";
                }

                $report .= "\n";
            }
        }

        $report .= "❌ **Métodos NÃO Executados (Esperados)**\n";
        $missingMethods = array_diff($expectedMethods, $this->executedMethods);

        // OR logic for response methods on text messages
        if ($messageType === 'text') {
            $responseOr = [
                'TelegramChannel.sendMessageWithKeyboard',
                'TelegramChannel.sendTextMessage'
            ];
            $executedResponse = array_intersect($this->executedMethods, $responseOr);
            if (!empty($executedResponse)) {
                $missingMethods = array_values(array_diff($missingMethods, $responseOr));
            }
        }

        if (empty($missingMethods)) {
            $report .= "✅ Todos os métodos esperados foram executados!\n\n";
        } else {
            foreach ($missingMethods as $method) {
                $report .= "• **{$method}** - ❌ NÃO EXECUTADO\n";
            }
            $report .= "\n";
        }

        $report .= "⚠️ **Análise do Problema**\n";
        if (!empty($missingMethods)) {
            $report .= "A mensagem não foi processada completamente porque:\n";
            foreach ($missingMethods as $method) {
                $report .= "• " . $this->explainMissingMethod($method) . "\n";
            }
        } else {
            $report .= "Todos os métodos foram executados com sucesso.\n";
        }

        $report .= "\n🔧 **Dicas para Debug**\n";
        $report .= "• Verifique logs do Laravel para erros\n";
        $report .= "• Confirme se todos os serviços estão ativos\n";
        $report .= "• Verifique permissões e configurações\n";

        return $report;
    }

    /**
     * Gera relatório resumido para o usuário
     */
    public function generateUserReport(array $payload): string
    {
        if (!$this->isEnabled) return '';

        $messageType = $this->identifyMessageType($payload);
        $expectedMethods = $this->expectedMethods[$messageType] ?? [];
        $missingMethods = array_diff($expectedMethods, $this->executedMethods);

        // OR logic for response methods on text messages
        if ($messageType === 'text') {
            $responseOr = [
                'TelegramChannel.sendMessageWithKeyboard',
                'TelegramChannel.sendTextMessage'
            ];
            $executedResponse = array_intersect($this->executedMethods, $responseOr);
            if (!empty($executedResponse)) {
                $missingMethods = array_values(array_diff($missingMethods, $responseOr));
            }
        }

        if (empty($missingMethods)) {
            return "✅ Sua mensagem foi processada com sucesso em todas as etapas!";
        }

        $report = "❌ Sua mensagem não foi processada completamente.\n\n";
        $report .= "**Etapas executadas:** " . count($this->executedMethods) . "/" . count($expectedMethods) . "\n\n";

        if (count($missingMethods) <= 3) {
            $report .= "**Etapas que falharam:**\n";
            foreach ($missingMethods as $method) {
                $report .= "• " . $this->getUserFriendlyMethodName($method) . "\n";
            }
        } else {
            $report .= "**Muitas etapas falharam** - Entre em contato com o suporte.\n";
        }

        $report .= "\n💡 **Sugestões:**\n";
        $report .= "• Tente novamente em alguns instantes\n";
        $report .= "• Use comandos simples como /start ou /help\n";
        $report .= "• Se o problema persistir, contate o suporte\n";

        return $report;
    }

    /**
     * Salva o rastreamento no cache para análise posterior
     */
    public function saveTrace(): void
    {
        if (!$this->isEnabled) return;

        $trace = [
            'message_id' => $this->messageId,
            'executed_methods' => $this->executedMethods,
            'expected_methods' => $this->expectedMethods[$this->identifyMessageType([])] ?? [],
            'method_data' => $this->methodData,
            'timestamps' => $this->timestamps,
            'created_at' => now()->toISOString()
        ];

        Cache::put("message_trace:{$this->messageId}", $trace, now()->addHours(24));
    }

    /**
     * Inicia o tracking de uma nova mensagem
     */
    public function startTracking(string $messageId): void
    {
        $this->messageId = $messageId;
        $this->clearTrace();
        $this->isEnabled = config('message-flow.enabled', false);
    }

    /**
     * Finaliza o tracking e salva o trace
     */
    public function endTracking(): void
    {
        $this->saveTrace();
        $this->clearTrace();
    }

    /**
     * Limpa o rastreamento atual
     */
    public function clearTrace(): void
    {
        $this->executedMethods = [];
        $this->methodData = [];
        $this->timestamps = [];
        $this->messageId = uniqid('msg_', true);
    }

    /**
     * Verifica se o tracking está habilitado
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Retorna os métodos executados
     */
    public function getExecutedMethods(): array
    {
        return $this->executedMethods;
    }

    /**
     * Retorna os métodos esperados para o tipo de mensagem
     */
    public function getExpectedMethods(): array
    {
        $messageType = $this->identifyMessageType([]);
        return $this->expectedMethods[$messageType] ?? [];
    }

    /**
     * Retorna os dados dos métodos
     */
    public function getMethodData(): array
    {
        return $this->methodData;
    }

    // Métodos auxiliares privados
    private function getMessageTypeEmoji(string $type): string
    {
        return match($type) {
            'text' => '📝',
            'voice' => '🎤',
            'callback_query' => '🔘',
            default => '❓'
        };
    }

    private function extractMessageContent(array $payload): string
    {
        if (isset($payload['message']['text'])) {
            return '"' . $payload['message']['text'] . '"';
        }

        if (isset($payload['message']['voice'])) {
            return 'Mensagem de voz';
        }

        if (isset($payload['callback_query']['data'])) {
            return 'Callback: ' . $payload['callback_query']['data'];
        }

        return 'Conteúdo desconhecido';
    }

    private function summarizeData(array $data): string
    {
        if (empty($data)) return 'Sem dados';

        $summary = [];
        foreach ($data as $key => $value) {
            if (is_string($value) && strlen($value) < 30) {
                $summary[] = "$key: $value";
            } elseif (is_numeric($value)) {
                $summary[] = "$key: $value";
            } elseif (is_bool($value)) {
                $summary[] = "$key: " . ($value ? 'Sim' : 'Não');
            }
        }

        return implode(', ', array_slice($summary, 0, 3));
    }

    private function explainMissingMethod(string $method): string
    {
        $explanations = [
            'TelegramMessageProcessorService.processWebhookPayload' => 'Falha na validação inicial do payload',
            'TelegramMessageProcessorService.processTextMessage' => 'Falha no processamento da mensagem de texto',
            'TelegramBotService.processMessage' => 'Falha no serviço principal do bot',
            'TelegramCommandParser.parseCommand' => 'Falha na interpretação do comando',
            'TelegramCommandHandlerManager.handleCommand' => 'Falha no gerenciamento de comandos',
            'TelegramChannel.sendTextMessage' => 'Falha no envio da resposta',
            'TelegramChannel.sendMessageWithKeyboard' => 'Falha no envio da resposta',
            'SpeechToTextService.convertVoiceToText' => 'Falha na conversão de voz para texto'
        ];

        return $explanations[$method] ?? 'Método não executado por razão desconhecida';
    }

    private function getUserFriendlyMethodName(string $method): string
    {
        $names = [
            'TelegramMessageProcessorService.processWebhookPayload' => 'Validação da mensagem',
            'TelegramMessageProcessorService.processTextMessage' => 'Processamento do texto',
            'TelegramBotService.processMessage' => 'Análise do comando',
            'TelegramCommandParser.parseCommand' => 'Interpretação do comando',
            'TelegramCommandHandlerManager.handleCommand' => 'Execução do comando',
            'TelegramChannel.sendTextMessage' => 'Envio da resposta',
            'TelegramChannel.sendMessageWithKeyboard' => 'Envio da resposta',
            'SpeechToTextService.convertVoiceToText' => 'Conversão de voz'
        ];

        return $names[$method] ?? 'Processamento da mensagem';
    }
}
