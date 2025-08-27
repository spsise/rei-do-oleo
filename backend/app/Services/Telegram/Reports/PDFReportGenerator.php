<?php

namespace App\Services\Telegram\Reports;

use App\Contracts\Telegram\TelegramReportGeneratorInterface;
use App\Contracts\LoggingServiceInterface;
use App\Domain\Service\Services\ServiceService;
use App\Services\Channels\TelegramChannel;
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
        try {
            // Log início da geração
            $this->loggingService->logTelegramEvent('pdf_generation_started', [
                'chat_id' => $chatId,
                'data_keys' => array_keys($data),
                'has_data_key' => isset($data['data']),
                'data_data_keys' => isset($data['data']) ? array_keys($data['data']) : []
            ], 'info');

            // Generate unique filename
            $filename = sprintf('report_%s_%d_%s.pdf',
                $data['data']['total_services'] > 0 ? 'services' : 'general',
                $chatId,
                now()->format('Y-m-d_H-i-s')
            );

            $this->loggingService->logTelegramEvent('pdf_filename_generated', [
                'chat_id' => $chatId,
                'filename' => $filename
            ], 'info');

            // Verificar se a view existe
            if (!view()->exists('pdf.report')) {
                throw new \Exception('View pdf.report não encontrada');
            }

            $this->loggingService->logTelegramEvent('pdf_view_verified', [
                'chat_id' => $chatId,
                'view_exists' => true
            ], 'info');

            // Tentar diferentes abordagens para gerar o PDF
            $pdf = null;
            $attempts = [];

            // Abordagem 1: Facade completa
            try {
                $this->loggingService->logTelegramEvent('pdf_trying_facade_approach', [
                    'chat_id' => $chatId
                ], 'info');

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.report', $data)
                    ->setPaper('a4', 'portrait')
                    ->setOptions([
                        'dpi' => 150,
                        'defaultFont' => 'sans-serif',
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true,
                    ]);

                $attempts[] = 'facade_success';

            } catch (\Exception $e) {
                $attempts[] = 'facade_failed';
                $this->loggingService->logTelegramEvent('pdf_facade_failed', [
                    'chat_id' => $chatId,
                    'error' => $e->getMessage()
                ], 'warning');

                // Abordagem 2: Container resolution
                try {
                    $this->loggingService->logTelegramEvent('pdf_trying_container_approach', [
                        'chat_id' => $chatId
                    ], 'info');

                    $pdfWrapper = app('dompdf.wrapper');
                    $pdf = $pdfWrapper->loadView('pdf.report', $data)
                        ->setPaper('a4', 'portrait')
                        ->setOptions([
                            'dpi' => 150,
                            'defaultFont' => 'sans-serif',
                            'isHtml5ParserEnabled' => true,
                            'isRemoteEnabled' => true,
                        ]);

                    $attempts[] = 'container_success';

                } catch (\Exception $e2) {
                    $attempts[] = 'container_failed';
                    $this->loggingService->logTelegramEvent('pdf_container_failed', [
                        'chat_id' => $chatId,
                        'error' => $e2->getMessage()
                    ], 'warning');

                    // Abordagem 3: HTML direto usando view render
                    try {
                        $this->loggingService->logTelegramEvent('pdf_trying_html_approach', [
                            'chat_id' => $chatId
                        ], 'info');

                        $htmlContent = view('pdf.report', $data)->render();
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($htmlContent)
                            ->setPaper('a4', 'portrait')
                            ->setOptions([
                                'dpi' => 150,
                                'defaultFont' => 'sans-serif',
                                'isHtml5ParserEnabled' => true,
                                'isRemoteEnabled' => true,
                            ]);

                        $attempts[] = 'html_success';

                    } catch (\Exception $e3) {
                        $attempts[] = 'html_failed';
                        $this->loggingService->logTelegramEvent('pdf_html_failed', [
                            'chat_id' => $chatId,
                            'error' => $e3->getMessage()
                        ], 'error');

                        // Abordagem 4: Fallback com template simples
                        try {
                            $this->loggingService->logTelegramEvent('pdf_trying_fallback_approach', [
                                'chat_id' => $chatId
                            ], 'info');

                            $fallbackHtml = $this->generateFallbackHtml($data);
                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($fallbackHtml)
                                ->setPaper('a4', 'portrait')
                                ->setOptions([
                                    'dpi' => 150,
                                    'defaultFont' => 'sans-serif',
                                    'isHtml5ParserEnabled' => true,
                                    'isRemoteEnabled' => true,
                                ]);

                            $attempts[] = 'fallback_success';

                        } catch (\Exception $e4) {
                            $attempts[] = 'fallback_failed';
                            $this->loggingService->logTelegramEvent('pdf_all_approaches_failed', [
                                'chat_id' => $chatId,
                                'facade_error' => $e->getMessage(),
                                'container_error' => $e2->getMessage(),
                                'html_error' => $e3->getMessage(),
                                'fallback_error' => $e4->getMessage(),
                                'attempts' => $attempts
                            ], 'error');

                            throw new \Exception('Todas as abordagens de geração de PDF falharam. Último erro: ' . $e4->getMessage());
                        }
                    }
                }
            }

            if (!$pdf) {
                throw new \Exception('Não foi possível gerar o PDF após todas as tentativas');
            }

            $this->loggingService->logTelegramEvent('pdf_generated_successfully_step', [
                'chat_id' => $chatId,
                'pdf_class' => get_class($pdf)
            ], 'info');

            // Save PDF to storage
            $pdfPath = "telegram/reports/{$filename}";

            // Ensure directory exists
            $directory = dirname($pdfPath);
            if (!Storage::disk('local')->exists($directory)) {
                Storage::disk('local')->makeDirectory($directory);
            }

            // Generate PDF output
            $pdfOutput = $pdf->output();

            if (empty($pdfOutput)) {
                throw new \Exception('PDF output está vazio');
            }

            Storage::disk('local')->put($pdfPath, $pdfOutput);

            // Verify file was saved
            if (!Storage::disk('local')->exists($pdfPath)) {
                throw new \Exception('Falha ao salvar PDF no storage');
            }

            $fileSize = Storage::disk('local')->size($pdfPath);

            $this->loggingService->logTelegramEvent('pdf_generated_successfully', [
                'chat_id' => $chatId,
                'filename' => $filename,
                'path' => $pdfPath,
                'file_size' => $fileSize
            ], 'info');

            return $pdfPath;

        } catch (\Exception $e) {
            $this->loggingService->logTelegramEvent('pdf_generation_failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            throw new \Exception('Falha na geração do PDF: ' . $e->getMessage(), 0, $e);
        }
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
     * Generate fallback HTML when view fails
     */
    private function generateFallbackHtml(array $data): string
    {
        $title = $data['title'] ?? 'Relatório PDF';
        $periodLabel = $data['period_label'] ?? 'Período não especificado';
        $generatedAt = $data['generated_at'] ?? now()->format('d/m/Y H:i:s');
        $reportData = $data['data'] ?? [];

        $html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #007bff; padding-bottom: 20px; }
        .header h1 { color: #007bff; margin: 0; }
        .section { margin-bottom: 20px; }
        .section h2 { background-color: #007bff; color: white; padding: 10px; margin: 0; }
        .metric { background-color: #f8f9fa; padding: 10px; margin: 5px 0; border-left: 4px solid #007bff; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . htmlspecialchars($title) . '</h1>
        <p>Período: ' . htmlspecialchars($periodLabel) . '</p>
        <p>Gerado em: ' . htmlspecialchars($generatedAt) . '</p>
    </div>

    <div class="section">
        <h2>📊 Resumo Geral</h2>
        <div class="metric">Total de Serviços: ' . ($reportData['total_services'] ?? 0) . '</div>
        <div class="metric">Agendados: ' . ($reportData['scheduled'] ?? 0) . '</div>
        <div class="metric">Em Andamento: ' . ($reportData['in_progress'] ?? 0) . '</div>
        <div class="metric">Concluídos: ' . ($reportData['completed'] ?? 0) . '</div>
    </div>

    <div class="section">
        <h2>💰 Financeiro</h2>
        <div class="metric">Receita Total: R$ ' . number_format($reportData['total_revenue'] ?? 0, 2, ',', '.') . '</div>
        <div class="metric">Ticket Médio: R$ ' . number_format($reportData['average_ticket'] ?? 0, 2, ',', '.') . '</div>
    </div>

    <div class="section">
        <h2>📦 Produtos</h2>
        <div class="metric">Total de Produtos: ' . ($reportData['total_products'] ?? 0) . '</div>
        <div class="metric">Estoque Baixo: ' . ($reportData['low_stock_count'] ?? 0) . '</div>
    </div>

    <div class="footer">
        <p>© ' . date('Y') . ' Rei do Óleo - Sistema de Gestão</p>
        <p>Relatório gerado automaticamente via Telegram Bot</p>
        <p><strong>Nota:</strong> Este relatório foi gerado usando template de fallback devido a problemas técnicos.</p>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Test PDF generation with debugging
     */
    public function testPdfGeneration(int $chatId = 123456): array
    {
        try {
            $this->loggingService->logTelegramEvent('pdf_test_started', [
                'chat_id' => $chatId
            ], 'info');

            // Test 1: View exists
            $viewExists = view()->exists('pdf.report');
            $this->loggingService->logTelegramEvent('pdf_test_view_check', [
                'view_exists' => $viewExists
            ], 'info');

            // Test 2: DomPDF service
            $dompdfService = app('dompdf.wrapper');
            $this->loggingService->logTelegramEvent('pdf_test_service_check', [
                'service_class' => get_class($dompdfService)
            ], 'info');

            // Test 3: Simple HTML generation
            $simpleHtml = '<h1>Test PDF</h1><p>This is a test.</p>';
            $simplePdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($simpleHtml);
            $this->loggingService->logTelegramEvent('pdf_test_simple_html', [
                'success' => true,
                'pdf_class' => get_class($simplePdf)
            ], 'info');

            // Test 4: View with minimal data
            $minimalData = [
                'title' => 'Test Report',
                'data' => ['total_services' => 1]
            ];
            $viewPdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.report', $minimalData);
            $this->loggingService->logTelegramEvent('pdf_test_view_minimal', [
                'success' => true,
                'pdf_class' => get_class($viewPdf)
            ], 'info');

            // Test 5: Full data structure
            $fullData = $this->preparePdfData(['total_services' => 5], 'today', 'general');
            $fullPdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.report', $fullData);
            $this->loggingService->logTelegramEvent('pdf_test_view_full', [
                'success' => true,
                'pdf_class' => get_class($fullPdf),
                'data_keys' => array_keys($fullData)
            ], 'info');

            return [
                'success' => true,
                'message' => 'All PDF tests passed',
                'tests' => [
                    'view_exists' => $viewExists,
                    'service_available' => true,
                    'simple_html' => true,
                    'view_minimal' => true,
                    'view_full' => true
                ]
            ];

        } catch (\Exception $e) {
            $this->loggingService->logTelegramEvent('pdf_test_failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 'error');

            return [
                'success' => false,
                'message' => 'PDF test failed: ' . $e->getMessage(),
                'error_details' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
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
