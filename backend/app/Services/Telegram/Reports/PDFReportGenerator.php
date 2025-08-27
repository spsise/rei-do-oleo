<?php

namespace App\Services\Telegram\Reports;

use App\Contracts\Telegram\TelegramReportGeneratorInterface;
use App\Contracts\LoggingServiceInterface;
use App\Domain\Service\Services\ServiceService;
use App\Services\Channels\TelegramChannel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PDFReportGenerator implements TelegramReportGeneratorInterface
{
    public function __construct(
        private ServiceService $serviceService,
        private TelegramChannel $telegramChannel,
        private LoggingServiceInterface $loggingService
    ) {}

        public function generate(int $chatId, array $params = []): array
    {
        $period = $params['period'] ?? 'today';
        $reportType = $params['report_type'] ?? 'general';
        $pdfPath = null;

        try {
            // Get dashboard metrics and data
            $dashboardData = $this->serviceService->getDashboardMetrics(null, $period);

            // Prepare data for PDF
            $pdfData = $this->preparePdfData($dashboardData, $period, $reportType);

            // Generate PDF
            $pdfPath = $this->generatePdf($pdfData, $chatId);

            // Send PDF document via Telegram
            return $this->sendPdfDocument($chatId, $pdfPath, $pdfData);

        } catch (\Exception $e) {
            $this->loggingService->logTelegramEvent('pdf_generation_failed', [
                'chat_id' => $chatId,
                'period' => $period,
                'report_type' => $reportType,
                'error' => $e->getMessage()
            ], 'error');

            // Clean up PDF file if it was created
            if ($pdfPath) {
                $this->cleanupPdfFile($pdfPath);
            }

            return $this->sendErrorMessage($chatId, $e->getMessage());
        }
    }

    /**
     * Generate PDF report for today (called by command system)
     */
    public function generateTodayPdfReport(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->generate($chatId, ['period' => 'today', 'report_type' => 'general']);
    }

    /**
     * Generate PDF report for week (called by command system)
     */
    public function generateWeekPdfReport(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->generate($chatId, ['period' => 'week', 'report_type' => 'general']);
    }

    /**
     * Generate PDF report for month (called by command system)
     */
    public function generateMonthPdfReport(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->generate($chatId, ['period' => 'month', 'report_type' => 'general']);
    }

    /**
     * Generate custom PDF report (called by command system)
     */
    public function generateCustomPdfReport(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;
        $period = $context['period'] ?? 'today';
        $reportType = $context['report_type'] ?? 'general';

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->generate($chatId, [
            'period' => $period,
            'report_type' => $reportType
        ]);
    }

    public function getReportType(): string
    {
        return 'pdf';
    }

    public function getReportName(): string
    {
        return 'Relatório PDF';
    }

    public function getAvailablePeriods(): array
    {
        return ['today', 'week', 'month', 'custom'];
    }

    /**
     * Prepare data for PDF generation
     */
    private function preparePdfData(array $dashboardData, string $period, string $reportType): array
    {
        $periodLabel = match($period) {
            'today' => 'Hoje',
            'week' => 'Esta Semana',
            'month' => 'Este Mês',
            default => 'Período Personalizado'
        };

        $reportTypeLabel = match($reportType) {
            'general' => 'Relatório Geral',
            'services' => 'Relatório de Serviços',
            'products' => 'Relatório de Produtos',
            'financial' => 'Relatório Financeiro',
            default => 'Relatório Customizado'
        };

        // Ensure all required keys exist with default values
        $data = array_merge([
            'total_services' => 0,
            'scheduled' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'total_revenue' => 0,
            'average_ticket' => 0,
            'total_products' => 0,
            'low_stock_count' => 0,
            'average_service_time' => 0,
            'pending_services' => 0
        ], $dashboardData);

        // Get additional data based on report type
        $additionalData = $this->getAdditionalReportData($reportType, $period);

        return [
            'title' => $reportTypeLabel,
            'subtitle' => "Rei do Óleo - Sistema de Gestão",
            'period_label' => $periodLabel,
            'report_type' => $reportTypeLabel,
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'data' => $data,
            'services' => $additionalData['services'] ?? [],
            'products' => $additionalData['products'] ?? [],
            'custom_sections' => $additionalData['custom_sections'] ?? []
        ];
    }

    /**
     * Get additional data based on report type
     */
    private function getAdditionalReportData(string $reportType, string $period): array
    {
        $additionalData = [
            'services' => [],
            'products' => [],
            'custom_sections' => []
        ];

        try {
            // For now, we'll add mock data - in a real implementation,
            // this would fetch actual data from the database
            if ($reportType === 'services' || $reportType === 'general') {
                $additionalData['services'] = [
                    [
                        'id' => 1,
                        'client' => 'Cliente Exemplo 1',
                        'type' => 'Troca de Óleo',
                        'status' => 'completed',
                        'date' => now()->format('d/m/Y')
                    ],
                    [
                        'id' => 2,
                        'client' => 'Cliente Exemplo 2',
                        'type' => 'Manutenção',
                        'status' => 'in-progress',
                        'date' => now()->format('d/m/Y')
                    ]
                ];
            }

            if ($reportType === 'products' || $reportType === 'general') {
                $additionalData['products'] = [
                    [
                        'name' => 'Óleo Motor 5W30',
                        'category' => 'Automotivo',
                        'stock' => 150,
                        'price' => 45.90
                    ],
                    [
                        'name' => 'Óleo Industrial 10W40',
                        'category' => 'Industrial',
                        'stock' => 8,
                        'price' => 89.90
                    ]
                ];
            }

            if ($reportType === 'financial') {
                $additionalData['custom_sections'][] = [
                    'icon' => '💰',
                    'title' => 'Detalhamento Financeiro',
                    'content' => '
                        <div class="metric-grid">
                            <div class="metric-item">
                                <div class="metric-label">Vendas à Vista</div>
                                <div class="metric-value positive">R$ 2.450,00</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-label">Vendas a Prazo</div>
                                <div class="metric-value">R$ 1.320,00</div>
                            </div>
                        </div>
                    '
                ];
            }

        } catch (\Exception $e) {
            $this->loggingService->logTelegramEvent('additional_report_data_failed', [
                'report_type' => $reportType,
                'period' => $period,
                'error' => $e->getMessage()
            ], 'warning');
        }

        return $additionalData;
    }

    /**
     * Generate PDF from data
     */
    private function generatePdf(array $data, int $chatId): string
    {
        // Generate unique filename
        $filename = sprintf('report_%s_%d_%s.pdf',
            $data['data']['total_services'] > 0 ? 'services' : 'general',
            $chatId,
            now()->format('Y-m-d_H-i-s')
        );

        // Generate PDF
        $pdf = Pdf::loadView('pdf.report', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        // Save PDF to storage
        $pdfPath = "telegram/reports/{$filename}";
        Storage::disk('local')->put($pdfPath, $pdf->output());

        $this->loggingService->logTelegramEvent('pdf_generated_successfully', [
            'chat_id' => $chatId,
            'filename' => $filename,
            'path' => $pdfPath,
            'file_size' => Storage::disk('local')->size($pdfPath)
        ], 'info');

        return $pdfPath;
    }

        /**
     * Send PDF document via Telegram
     */
    private function sendPdfDocument(int $chatId, string $pdfPath, array $pdfData): array
    {
        $result = null;

        try {
            // Get absolute path for Telegram API
            $absolutePath = Storage::disk('local')->path($pdfPath);

            if (!file_exists($absolutePath)) {
                throw new \Exception('PDF file not found');
            }

            // Log file info before sending
            $fileSize = filesize($absolutePath);
            $this->loggingService->logTelegramEvent('pdf_send_starting', [
                'chat_id' => $chatId,
                'pdf_path' => $pdfPath,
                'file_size' => $fileSize
            ], 'info');

            // Prepare document caption
            $caption = "📊 *{$pdfData['title']}*\n\n" .
                      "📅 Período: {$pdfData['period_label']}\n" .
                      "🕒 Gerado em: {$pdfData['generated_at']}\n\n" .
                      "📄 Relatório completo em PDF";

            // Send document via Telegram API
            $result = $this->telegramChannel->sendDocument($chatId, $absolutePath, $caption);

            // Prepare keyboard for user actions
            $keyboard = [
                [
                    ['text' => '📊 Novo Relatório PDF', 'callback_data' => 'pdf_report_menu'],
                    ['text' => '📈 Relatório Texto', 'callback_data' => 'report_menu']
                ],
                [
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ]
            ];

            // Send follow-up message with options
            $followUpMessage = "✅ *PDF enviado com sucesso!*\n\n" .
                             "O relatório foi gerado e enviado como documento PDF. " .
                             "Você pode baixar o arquivo e visualizar em qualquer dispositivo.\n\n" .
                             "O que deseja fazer agora?";

            $this->telegramChannel->sendMessageWithKeyboard($followUpMessage, $chatId, $keyboard);

            return [
                'success' => true,
                'message' => 'PDF report sent successfully',
                'result' => $result
            ];

        } catch (\Exception $e) {
            $this->loggingService->logTelegramEvent('pdf_send_failed', [
                'chat_id' => $chatId,
                'pdf_path' => $pdfPath,
                'error' => $e->getMessage()
            ], 'error');

            throw $e;
        } finally {
            // ALWAYS clean up the PDF file, regardless of success or failure
            $this->cleanupPdfFile($pdfPath);
        }
    }

    /**
     * Clean up generated PDF file
     */
    private function cleanupPdfFile(string $pdfPath): void
    {
        try {
            if (Storage::disk('local')->exists($pdfPath)) {
                $fileSize = Storage::disk('local')->size($pdfPath);
                Storage::disk('local')->delete($pdfPath);

                $this->loggingService->logTelegramEvent('pdf_file_cleaned_up', [
                    'path' => $pdfPath,
                    'file_size' => $fileSize
                ], 'info');
            }
        } catch (\Exception $e) {
            $this->loggingService->logTelegramEvent('pdf_cleanup_failed', [
                'path' => $pdfPath,
                'error' => $e->getMessage()
            ], 'warning');
        }
    }

    /**
     * Clean up old orphaned PDF files
     * This method can be called periodically to clean up files that weren't properly deleted
     */
    public function cleanupOrphanedFiles(int $olderThanMinutes = 60): array
    {
        try {
            $reportPath = 'telegram/reports';
            $disk = Storage::disk('local');

            if (!$disk->exists($reportPath)) {
                return [
                    'success' => true,
                    'message' => 'Reports directory does not exist',
                    'deleted_count' => 0
                ];
            }

            $cutoffTime = now()->subMinutes($olderThanMinutes);
            $allFiles = $disk->files($reportPath);
            $pdfFiles = array_filter($allFiles, fn($file) => str_ends_with(strtolower($file), '.pdf'));

            $deletedCount = 0;
            $totalSize = 0;

            foreach ($pdfFiles as $file) {
                $lastModified = \Carbon\Carbon::createFromTimestamp($disk->lastModified($file));

                if ($lastModified->lt($cutoffTime)) {
                    $fileSize = $disk->size($file);

                    if ($disk->delete($file)) {
                        $deletedCount++;
                        $totalSize += $fileSize;

                        $this->loggingService->logTelegramEvent('orphaned_pdf_cleaned', [
                            'file_path' => $file,
                            'age_minutes' => $lastModified->diffInMinutes(now()),
                            'file_size' => $fileSize
                        ], 'info');
                    }
                }
            }

            $this->loggingService->logTelegramEvent('orphaned_cleanup_completed', [
                'total_files_checked' => count($pdfFiles),
                'deleted_count' => $deletedCount,
                'total_size_deleted' => $totalSize,
                'cutoff_minutes' => $olderThanMinutes
            ], 'info');

            return [
                'success' => true,
                'message' => "Cleaned up {$deletedCount} orphaned files",
                'deleted_count' => $deletedCount,
                'total_size' => $totalSize
            ];

        } catch (\Exception $e) {
            $this->loggingService->logTelegramEvent('orphaned_cleanup_failed', [
                'error' => $e->getMessage(),
                'cutoff_minutes' => $olderThanMinutes
            ], 'error');

            return [
                'success' => false,
                'message' => 'Failed to cleanup orphaned files: ' . $e->getMessage(),
                'deleted_count' => 0
            ];
        }
    }

    /**
     * Send error message
     */
    private function sendErrorMessage(int $chatId, string $errorMessage = ''): array
    {
        $message = "⚠️ *Erro na Geração do PDF*\n\n" .
                   "Ocorreu um erro ao gerar o relatório em PDF.\n" .
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
}
