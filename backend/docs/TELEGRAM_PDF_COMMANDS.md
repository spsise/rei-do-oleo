# 📄 Sistema de Relatórios PDF para Telegram

## Visão Geral

O sistema de relatórios PDF permite que usuários solicitem relatórios em formato PDF através do Telegram Bot. Os relatórios são gerados dinamicamente, formatados profissionalmente e enviados como documentos PDF.

## Funcionalidades

### 🎯 Comandos Disponíveis

#### Comandos Principais

- `pdf_report_menu` - Menu principal de relatórios PDF
- `pdf_today` - Relatório PDF do dia atual
- `pdf_week` - Relatório PDF da semana atual
- `pdf_month` - Relatório PDF do mês atual
- `pdf_custom` - Relatório PDF personalizado

#### Comandos por Tipo

- `pdf_general` - Relatório PDF geral
- `pdf_services` - Relatório PDF de serviços
- `pdf_products` - Relatório PDF de produtos
- `pdf_financial` - Relatório PDF financeiro

#### Comandos de Navegação

- `pdf_types` - Menu de tipos de relatório PDF
- `text_reports_menu` - Menu de relatórios em texto

### 🔧 Aliases e Linguagem Natural

Os comandos respondem a vários aliases e frases em linguagem natural:

```
pdf_today:
- "pdf hoje"
- "pdf today"
- "relatório pdf hoje"
- "pdf de hoje"
- "gerar pdf hoje"
- "pdf diário"

pdf_week:
- "pdf semana"
- "pdf week"
- "relatório pdf semana"
- "pdf da semana"
- "pdf semanal"

pdf_month:
- "pdf mês"
- "pdf month"
- "relatório pdf mês"
- "pdf do mês"
- "pdf mensal"
```

## 🏗️ Arquitetura

### Componentes Principais

#### 1. PDFReportGenerator

**Localização**: `app/Services/Telegram/Reports/PDFReportGenerator.php`

Responsável por:

- Geração de dados para o PDF
- Criação do documento PDF usando DomPDF
- Gestão de arquivos temporários
- Envio do documento via Telegram

#### 2. PdfCommandHandler

**Localização**: `app/Services/Telegram/Handlers/PdfCommandHandler.php`

Responsável por:

- Processamento de comandos PDF
- Navegação entre menus
- Validação de parâmetros
- Tratamento de erros

#### 3. Template PDF

**Localização**: `resources/views/pdf/report.blade.php`

Características:

- Layout profissional e responsivo
- Suporte a diferentes tipos de dados
- Estilos CSS otimizados para PDF
- Seções modulares

#### 4. TelegramChannel (Extensão)

**Localização**: `app/Services/Channels/TelegramChannel.php`

Novo método adicionado:

- `sendDocument()` - Envio de documentos PDF via Telegram API

## 📊 Tipos de Relatório

### 1. Relatório Geral

- Visão completa do sistema
- Dados de serviços, produtos e financeiro
- Métricas de performance

### 2. Relatório de Serviços

- Foco nos serviços realizados
- Status e detalhamentos
- Performance por período

### 3. Relatório de Produtos

- Análise de produtos e estoque
- Produtos com baixo estoque
- Movimentações

### 4. Relatório Financeiro

- Dados financeiros detalhados
- Receitas e vendas
- Análise de performance

## ⚙️ Configuração

### Dependências Instaladas

```bash
composer require barryvdh/laravel-dompdf
```

### Configurações

#### DomPDF

- Configuração publicada em `config/dompdf.php`
- Fontes suportadas: Arial, sans-serif
- Formato padrão: A4 Portrait
- DPI: 150

#### Storage

- Diretório temporário: `storage/app/telegram/reports/`
- Limpeza automática após envio
- Nomes únicos por chat e timestamp

## 🔄 Fluxo de Execução

### 1. Solicitação do Usuário

```
Usuário: "pdf hoje"
↓
Sistema identifica comando: pdf_today
↓
Chama PdfCommandHandler::generateTodayPdf()
```

### 2. Geração do PDF

```
PDFReportGenerator::generate()
↓
Coleta dados do ServiceService
↓
Prepara dados para template
↓
Gera PDF com DomPDF
↓
Salva arquivo temporário
```

### 3. Envio via Telegram

```
TelegramChannel::sendDocument()
↓
Envia indicador de upload
↓
Faz upload do arquivo
↓
Remove arquivo temporário
↓
Envia menu de ações
```

## 🛡️ Segurança e Permissões

### Níveis de Acesso

- **All**: Comandos básicos (pdf_today, pdf_week, pdf_month)
- **Admin/Manager**: Comandos avançados (pdf_types, pdf_custom)

### Validações

- Verificação de Chat ID
- Validação de existência de arquivo
- Tratamento de erros de geração
- Limpeza automática de arquivos

## 🔧 Manutenção

### Logs

Todos os eventos são logados:

- Geração de PDF bem-sucedida
- Envio de documentos
- Erros de processamento
- Limpeza de arquivos

### Monitoramento

- Tempo de geração de PDF
- Tamanho dos arquivos
- Taxa de sucesso de envio
- Uso de storage

## 📱 Interface do Usuário

### Menu Principal de PDF

```
📄 Relatórios PDF

Escolha o período para gerar seu relatório em PDF:

📊 O relatório será gerado com dados completos e
enviado como documento PDF que você pode baixar e compartilhar.

[📅 PDF Hoje] [📊 PDF Semana]
[📈 PDF Mês] [📋 PDF Personalizado]
[📄 Tipos de Relatório] [📱 Relatório Texto]
[🏠 Menu Principal]
```

### Menu de Tipos

```
📋 Tipos de Relatório PDF

Escolha o tipo de relatório que deseja gerar:

• Geral: Visão completa do sistema
• Serviços: Foco nos serviços realizados
• Produtos: Análise de produtos e estoque
• Financeiro: Dados financeiros detalhados

[📊 PDF Geral] [🔧 PDF Serviços]
[📦 PDF Produtos] [💰 PDF Financeiro]
[⬅️ Voltar ao Menu PDF] [🏠 Menu Principal]
```

## 🚀 Comandos de Teste

### Verificar Comandos

```bash
php artisan tinker --execute="
\$commands = \App\Models\Telegram\TelegramCommand::where('category', 'reports')
    ->where('command_id', 'LIKE', 'pdf_%')
    ->get(['command_id', 'aliases']);
foreach(\$commands as \$cmd) {
    echo \$cmd->command_id . ' - ' . implode(', ', \$cmd->aliases) . PHP_EOL;
}
"
```

### Testar Geração PDF

```bash
php artisan tinker --execute="
\$generator = app('App\Services\Telegram\Reports\PDFReportGenerator');
\$result = \$generator->generate(123456, ['period' => 'today']);
echo 'Result: ' . json_encode(\$result) . PHP_EOL;
"
```

## 📋 TODO / Melhorias Futuras

- [ ] Gráficos e charts nos PDFs
- [ ] Relatórios agendados
- [ ] Templates personalizáveis
- [ ] Compressão de PDF
- [ ] Assinatura digital
- [ ] Relatórios por email
- [ ] Cache de relatórios frequentes
- [ ] Watermark personalizado

## 🐛 Solução de Problemas

### Erro: "File not found"

- Verificar permissões do diretório `storage/app/telegram/reports/`
- Verificar se o DomPDF está gerando o arquivo

### Erro: "Failed to send document"

- Verificar token do Telegram Bot
- Verificar tamanho do arquivo (máx 50MB)
- Verificar configuração de rede

### PDF não renderiza corretamente

- Verificar configuração do DomPDF
- Validar CSS do template
- Verificar encoding dos dados

### Comandos não reconhecidos

- Executar o seeder: `php artisan db:seed --class=TelegramCommandSeeder`
- Verificar cache de comandos
- Verificar logs de matching

---

**Desenvolvido para o sistema Rei do Óleo**  
Versão: 1.0.0  
Data: 2024
