<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Relatório' }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
            color: #333;
            background-color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }

        .header .subtitle {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .report-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            border: 1px solid #dee2e6;
        }

        .report-info h3 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 16px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
        }

        .info-value {
            color: #007bff;
        }

        .section {
            margin-bottom: 25px;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            overflow: hidden;
        }

        .section-header {
            background-color: #007bff;
            color: white;
            padding: 12px 15px;
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .section-content {
            padding: 15px;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }

        .metric-item {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }

        .metric-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .metric-value.positive {
            color: #28a745;
        }

        .metric-value.negative {
            color: #dc3545;
        }

        .metric-value.warning {
            color: #ffc107;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
            font-size: 14px;
        }

        .table td {
            font-size: 13px;
        }

        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }

        .chart-placeholder {
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            margin: 15px 0;
            border-radius: 4px;
        }

        @page {
            margin: 2cm;
        }

        .page-break {
            page-break-before: always;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-in-progress {
            background-color: #cce7ff;
            color: #004085;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Relatório do Sistema' }}</h1>
        <div class="subtitle">{{ $subtitle ?? 'Rei do Óleo - Sistema de Gestão' }}</div>
    </div>

    <div class="report-info">
        <h3>📋 Informações do Relatório</h3>
        <div class="info-row">
            <span class="info-label">Período:</span>
            <span class="info-value">{{ $period_label ?? 'Não especificado' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Gerado em:</span>
            <span class="info-value">{{ $generated_at ?? now()->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tipo:</span>
            <span class="info-value">{{ $report_type ?? 'Geral' }}</span>
        </div>
    </div>

    @if(isset($data))
        <!-- Seção de Serviços -->
        <div class="section">
            <h2 class="section-header">🔧 Serviços</h2>
            <div class="section-content">
                <div class="metric-grid">
                    <div class="metric-item">
                        <div class="metric-label">Total de Serviços</div>
                        <div class="metric-value">{{ $data['total_services'] ?? 0 }}</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-label">Agendados</div>
                        <div class="metric-value">{{ $data['scheduled'] ?? 0 }}</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-label">Em Andamento</div>
                        <div class="metric-value warning">{{ $data['in_progress'] ?? 0 }}</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-label">Concluídos</div>
                        <div class="metric-value positive">{{ $data['completed'] ?? 0 }}</div>
                    </div>
                </div>

                @if(isset($services) && count($services) > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($services as $service)
                                <tr>
                                    <td>#{{ $service['id'] ?? 'N/A' }}</td>
                                    <td>{{ $service['client'] ?? 'N/A' }}</td>
                                    <td>{{ $service['type'] ?? 'N/A' }}</td>
                                    <td>
                                        <span class="status-badge status-{{ strtolower($service['status'] ?? 'pending') }}">
                                            {{ $service['status'] ?? 'Pendente' }}
                                        </span>
                                    </td>
                                    <td>{{ $service['date'] ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <!-- Seção Financeira -->
        <div class="section">
            <h2 class="section-header">💰 Financeiro</h2>
            <div class="section-content">
                <div class="metric-grid">
                    <div class="metric-item">
                        <div class="metric-label">Receita Total</div>
                        <div class="metric-value positive">R$ {{ number_format($data['total_revenue'] ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-label">Ticket Médio</div>
                        <div class="metric-value">R$ {{ number_format($data['average_ticket'] ?? 0, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de Produtos -->
        <div class="section">
            <h2 class="section-header">📦 Produtos</h2>
            <div class="section-content">
                <div class="metric-grid">
                    <div class="metric-item">
                        <div class="metric-label">Total de Produtos</div>
                        <div class="metric-value">{{ $data['total_products'] ?? 0 }}</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-label">Estoque Baixo</div>
                        <div class="metric-value warning">{{ $data['low_stock_count'] ?? 0 }}</div>
                    </div>
                </div>

                @if(isset($products) && count($products) > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Estoque</th>
                                <th>Preço</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>{{ $product['name'] ?? 'N/A' }}</td>
                                    <td>{{ $product['category'] ?? 'N/A' }}</td>
                                    <td>{{ $product['stock'] ?? 0 }}</td>
                                    <td>R$ {{ number_format($product['price'] ?? 0, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <!-- Seção de Performance -->
        <div class="section">
            <h2 class="section-header">⏱️ Performance</h2>
            <div class="section-content">
                <div class="metric-grid">
                    <div class="metric-item">
                        <div class="metric-label">Tempo Médio de Serviço</div>
                        <div class="metric-value">{{ $data['average_service_time'] ?? 0 }} min</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-label">Serviços Pendentes</div>
                        <div class="metric-value {{ ($data['pending_services'] ?? 0) > 0 ? 'warning' : 'positive' }}">
                            {{ $data['pending_services'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(isset($custom_sections) && count($custom_sections) > 0)
        @foreach($custom_sections as $section)
            <div class="section">
                <h2 class="section-header">{{ $section['icon'] ?? '📊' }} {{ $section['title'] ?? 'Seção Personalizada' }}</h2>
                <div class="section-content">
                    {!! $section['content'] ?? 'Conteúdo não disponível' !!}
                </div>
            </div>
        @endforeach
    @endif

    <div class="footer">
        <p>© {{ date('Y') }} Rei do Óleo - Sistema de Gestão | Relatório gerado automaticamente</p>
        <p>Este documento foi gerado via Telegram Bot em {{ $generated_at ?? now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
