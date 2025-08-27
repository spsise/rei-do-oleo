<?php

namespace App\Services\Telegram\Handlers;

use App\Contracts\Telegram\TelegramCommandHandlerInterface;
use App\Contracts\LoggingServiceInterface;
use App\Services\Telegram\TelegramMenuBuilder;
use App\Services\Telegram\Reports\PDFReportGenerator;
use App\Services\Channels\TelegramChannel;

class PdfCommandHandler implements TelegramCommandHandlerInterface
{
    public function __construct(
        private TelegramMenuBuilder $menuBuilder,
        private PDFReportGenerator $pdfReportGenerator,
        private TelegramChannel $telegramChannel,
        private LoggingServiceInterface $loggingService
    ) {}

    /**
     * Handle PDF command (legacy signature for compatibility)
     */
    public function handle(int $chatId, array $params = []): array
    {
        $this->loggingService->logTelegramEvent('pdf_command_handler_called_legacy', [
            'chat_id' => $chatId,
            'params' => $params
        ], 'info');

        // Convert to new context format
        $context = array_merge($params, ['chat_id' => $chatId]);

        return $this->handlePdfRequest($context);
    }

    /**
     * Handle PDF request with new signature
     */
    public function handlePdfRequest(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;
        $period = $context['period'] ?? null;
        $reportType = $context['report_type'] ?? 'general';

        $this->loggingService->logTelegramEvent('pdf_command_handler_called', [
            'chat_id' => $chatId,
            'period' => $period,
            'report_type' => $reportType,
            'context' => $context
        ], 'info');

        try {
            // If no period specified, show PDF menu
            if (!$period) {
                return $this->showPdfMenu($chatId);
            }

            // Generate PDF report for specific period
            return $this->pdfReportGenerator->generate($chatId, [
                'period' => $period,
                'report_type' => $reportType
            ]);

        } catch (\Exception $e) {
            $this->loggingService->logTelegramEvent('pdf_command_failed', [
                'chat_id' => $chatId,
                'context' => $context,
                'error' => $e->getMessage()
            ], 'error');

            return $this->sendErrorMessage($chatId, $e->getMessage());
        }
    }

    /**
     * Generate today PDF report
     */
    public function generateTodayPdf(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        $this->loggingService->logTelegramEvent('generating_today_pdf_report', [
            'chat_id' => $chatId
        ], 'info');

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->pdfReportGenerator->generateTodayPdfReport($context);
    }

    /**
     * Generate week PDF report
     */
    public function generateWeekPdf(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        $this->loggingService->logTelegramEvent('generating_week_pdf_report', [
            'chat_id' => $chatId
        ], 'info');

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->pdfReportGenerator->generateWeekPdfReport($context);
    }

    /**
     * Generate month PDF report
     */
    public function generateMonthPdf(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        $this->loggingService->logTelegramEvent('generating_month_pdf_report', [
            'chat_id' => $chatId
        ], 'info');

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->pdfReportGenerator->generateMonthPdfReport($context);
    }

    /**
     * Generate custom PDF report
     */
    public function generateCustomPdf(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        $this->loggingService->logTelegramEvent('generating_custom_pdf_report', [
            'chat_id' => $chatId,
            'context' => $context
        ], 'info');

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->pdfReportGenerator->generateCustomPdfReport($context);
    }

    /**
     * Show PDF menu
     */
    private function showPdfMenu(int $chatId): array
    {
        $message = "📄 *Relatórios PDF*\n\n" .
                   "Escolha o período para gerar seu relatório em PDF:\n\n" .
                   "📊 O relatório será gerado com dados completos e " .
                   "enviado como documento PDF que você pode baixar e compartilhar.";

        $keyboard = [
            [
                ['text' => '📅 PDF Hoje', 'callback_data' => 'pdf_today'],
                ['text' => '📊 PDF Semana', 'callback_data' => 'pdf_week']
            ],
            [
                ['text' => '📈 PDF Mês', 'callback_data' => 'pdf_month'],
                ['text' => '📋 PDF Personalizado', 'callback_data' => 'pdf_custom']
            ],
            [
                ['text' => '📄 Tipos de Relatório', 'callback_data' => 'pdf_types'],
                ['text' => '📱 Relatório Texto', 'callback_data' => 'report_menu']
            ],
            [
                ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }

    /**
     * Show PDF types menu
     */
    public function showPdfTypesMenu(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        $message = "📋 *Tipos de Relatório PDF*\n\n" .
                   "Escolha o tipo de relatório que deseja gerar:\n\n" .
                   "• **Geral**: Visão completa do sistema\n" .
                   "• **Serviços**: Foco nos serviços realizados\n" .
                   "• **Produtos**: Análise de produtos e estoque\n" .
                   "• **Financeiro**: Dados financeiros detalhados";

        $keyboard = [
            [
                ['text' => '📊 PDF Geral', 'callback_data' => 'pdf_general'],
                ['text' => '🔧 PDF Serviços', 'callback_data' => 'pdf_services']
            ],
            [
                ['text' => '📦 PDF Produtos', 'callback_data' => 'pdf_products'],
                ['text' => '💰 PDF Financeiro', 'callback_data' => 'pdf_financial']
            ],
            [
                ['text' => '⬅️ Voltar ao Menu PDF', 'callback_data' => 'pdf_report_menu'],
                ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }

    /**
     * Handle PDF type selection
     */
    public function handlePdfTypeSelection(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;
        $reportType = $context['report_type'] ?? 'general';

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        $typeLabel = match($reportType) {
            'general' => 'Geral',
            'services' => 'Serviços',
            'products' => 'Produtos',
            'financial' => 'Financeiro',
            default => 'Personalizado'
        };

        $message = "📋 *Relatório PDF - {$typeLabel}*\n\n" .
                   "Escolha o período para o relatório {$typeLabel}:";

        $keyboard = [
            [
                ['text' => '📅 Hoje', 'callback_data' => "pdf_today_{$reportType}"],
                ['text' => '📊 Semana', 'callback_data' => "pdf_week_{$reportType}"]
            ],
            [
                ['text' => '📈 Mês', 'callback_data' => "pdf_month_{$reportType}"],
                ['text' => '📋 Personalizado', 'callback_data' => "pdf_custom_{$reportType}"]
            ],
            [
                ['text' => '⬅️ Tipos de Relatório', 'callback_data' => 'pdf_types'],
                ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }

    /**
     * Send error message
     */
    private function sendErrorMessage(int $chatId, string $errorMessage = ''): array
    {
        $message = "⚠️ *Erro no Comando PDF*\n\n" .
                   "Ocorreu um erro ao processar o comando de PDF.\n" .
                   "Tente novamente em alguns instantes.";

        if ($errorMessage) {
            $message .= "\n\n*Detalhes:* " . $errorMessage;
        }

        $keyboard = [
            [
                ['text' => '🔄 Tentar Novamente', 'callback_data' => 'pdf_report_menu'],
                ['text' => '📈 Relatório Texto', 'callback_data' => 'report_menu']
            ],
            [
                ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }

    public function getCommandName(): string
    {
        return 'pdf_report';
    }

    public function getCommandDescription(): string
    {
        return 'Gerar e enviar relatórios em formato PDF via Telegram';
    }

    public function canHandle(string $command): bool
    {
        return in_array($command, [
            'pdf_report',
            'pdf',
            'relatório pdf',
            'report pdf'
        ]);
    }
}
