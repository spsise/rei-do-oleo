# 🚀 Exemplo Prático - Sistema de Relatórios Telegram

## 📱 Cenário: Solicitar Relatório via Telegram

### **Passo a Passo Completo**

#### **1. Configuração Inicial**

```bash
# 1. Configurar variáveis de ambiente
echo "TELEGRAM_ENABLED=true" >> .env
echo "TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz" >> .env
echo "TELEGRAM_RECIPIENTS=123456789,987654321" >> .env

# 2. Configurar webhook
php artisan telegram:bot-setup --set-webhook --webhook-url=https://yourdomain.com/api/telegram/webhook

# 3. Testar configuração
php artisan telegram:bot-setup --test
```

#### **2. Usar o Bot no Telegram**

**Cenário**: Você está no aplicativo Telegram e quer saber como estão os serviços de hoje.

**Passo 1**: Abra o chat com seu bot (@seu_bot_username)

**Passo 2**: Digite um dos comandos:

```
/services hoje
```

ou em linguagem natural:

```
Como estão os serviços de hoje?
```

**Passo 3**: O bot responde automaticamente:

```
🔧 Relatório de Serviços - Hoje

📋 Resumo:
• Total: 8
• Concluídos: 5
• Em andamento: 2
• Pendentes: 1

💰 Receita:
• Total: R$ 2.150,00
• Média: R$ 430,00

📈 Performance:
• Concluídos hoje: 5
• Tempo médio: 95 min

📅 Gerado em: 15/01/2024 14:30:25
```

#### **3. Outros Exemplos de Uso**

**Exemplo 1: Relatório Geral da Semana**

```
Usuário: /report semana

Bot responde:
📊 Relatório Geral - Esta Semana

🔧 Serviços:
• Total: 45
• Agendados: 12
• Em andamento: 8
• Concluídos: 20
• Cancelados: 5

💰 Financeiro:
• Receita total: R$ 12.450,00
• Ticket médio: R$ 622,50

⏱️ Performance:
• Tempo médio: 120 min
• Pendentes: 12
• Concluídos hoje: 3

📅 Gerado em: 15/01/2024 14:30:25
```

**Exemplo 2: Status do Estoque**

```
Usuário: /products

Bot responde:
📦 Relatório de Produtos

📊 Resumo:
• Total de produtos: 156
• Com estoque baixo: 12

🏆 Top Produtos:
• Óleo 5W30 - 45 vendas
• Filtro de Ar - 38 vendas
• Pastilha de Freio - 32 vendas

📅 Gerado em: 15/01/2024 14:30:25
```

**Exemplo 3: Dashboard Geral**

```
Usuário: /dashboard

Bot responde:
📈 Dashboard Geral - Hoje

🔧 Serviços:
• Total: 8
• Agendados: 2
• Em andamento: 3
• Concluídos: 3

💰 Financeiro:
• Receita total: R$ 2.150,00
• Ticket médio: R$ 430,00

📦 Produtos:
• Total: 156
• Estoque baixo: 12

⏱️ Performance:
• Tempo médio: 95 min
• Pendentes: 2

📅 Gerado em: 15/01/2024 14:30:25
```

## 🛠️ Comandos de Teste via CLI

### **Teste Simulado (sem enviar mensagens)**

```bash
# Testar relatório geral
php artisan telegram:report general --period=today --test

# Testar relatório de serviços
php artisan telegram:report services --period=week --test

# Testar relatório de produtos
php artisan telegram:report products --test

# Testar dashboard
php artisan telegram:report dashboard --test
```

### **Teste Real (enviar mensagens)**

```bash
# Enviar relatório para todos os destinatários
php artisan telegram:report general --period=today

# Enviar para chat específico
php artisan telegram:report services --period=week --chat-id=123456789
```

## 📊 Fluxo Técnico Detalhado

### **1. Usuário envia mensagem**

```
Telegram App → Telegram Servers → Webhook → Laravel API
```

### **2. Processamento no Laravel**

```php
// 1. TelegramWebhookController recebe webhook
public function handle(Request $request) {
    $message = $request->input('message');
    $result = $this->telegramBotService->processMessage($message);
}

// 2. TelegramBotService processa comando
public function processMessage(array $message) {
    $command = $this->parseCommand($message['text']);

    switch ($command['type']) {
        case 'services':
            return $this->sendServicesReport($chatId, $command['params']);
        // ...
    }
}

// 3. Gera relatório usando ServiceService
private function sendServicesReport(int $chatId, array $params) {
    $dashboardData = $this->serviceService->getDashboardMetrics(null, $period);
    $message = $this->formatServicesReport($dashboardData, $period);
    return $this->telegramChannel->sendTextMessage($message, $chatId);
}
```

### **3. Resposta para o usuário**

```
Laravel API → Telegram Servers → Telegram App
```

## 🔍 Debug e Troubleshooting

### **Verificar se o bot está funcionando**

```bash
# 1. Verificar configuração
php artisan telegram:bot-setup --get-info

# 2. Testar conexão
php artisan telegram:bot-setup --test

# 3. Verificar logs
tail -f storage/logs/laravel.log | grep "Telegram"
```

### **Problemas comuns e soluções**

#### **Problema: Bot não responde**

```bash
# Solução 1: Verificar webhook
php artisan telegram:bot-setup --get-info

# Solução 2: Reconfigurar webhook
php artisan telegram:bot-setup --set-webhook --webhook-url=https://yourdomain.com/api/telegram/webhook

# Solução 3: Verificar logs
tail -f storage/logs/laravel.log | grep "webhook"
```

#### **Problema: "Acesso Negado"**

```bash
# Solução: Verificar chat IDs autorizados
php artisan telegram:debug --get-updates

# Atualizar .env com chat IDs corretos
TELEGRAM_RECIPIENTS=123456789,987654321
```

#### **Problema: Erro de token**

```bash
# Solução: Validar token
php artisan telegram:debug --validate-token

# Verificar se o token está correto no .env
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
```

## 📱 Interface do Usuário

### **Comandos Disponíveis**

```
🤖 Rei do Óleo - Bot de Relatórios

📊 Relatórios:
• /report - Relatório geral
• /report hoje - Relatório de hoje
• /report semana - Relatório da semana
• /report mês - Relatório do mês

🔧 Serviços:
• /services - Status dos serviços
• /services hoje - Serviços de hoje
• /services semana - Serviços da semana

📦 Produtos:
• /products - Status do estoque
• /products baixo - Produtos com estoque baixo

📈 Dashboard:
• /dashboard - Resumo geral
• /status - Status do sistema

💬 Linguagem Natural:
• "Envie um relatório de hoje"
• "Como estão os serviços da semana?"
• "Mostre o dashboard"

❓ Ajuda:
• /help - Esta mensagem
```

### **Exemplos de Linguagem Natural**

| **Usuário digita**        | **Bot interpreta como** | **Resposta**            |
| ------------------------- | ----------------------- | ----------------------- |
| "Envie um relatório"      | `/report hoje`          | Relatório geral de hoje |
| "Como estão os serviços?" | `/services hoje`        | Relatório de serviços   |
| "Mostre o dashboard"      | `/dashboard`            | Dashboard geral         |
| "Status do sistema"       | `/status`               | Status do sistema       |
| "Produtos em estoque"     | `/products`             | Relatório de produtos   |

## 🎯 Casos de Uso Reais

### **Caso 1: Gerente de Loja**

**Cenário**: Gerente quer verificar o desempenho da loja durante o dia.

**Ação**: Envia `/report hoje` no Telegram

**Resultado**: Recebe relatório completo com serviços, receita e performance.

### **Caso 2: Técnico**

**Cenário**: Técnico quer verificar serviços pendentes.

**Ação**: Envia `/services` no Telegram

**Resultado**: Recebe lista de serviços em andamento e pendentes.

### **Caso 3: Administrador**

**Cenário**: Administrador quer verificar estoque.

**Ação**: Envia `/products` no Telegram

**Resultado**: Recebe status do estoque e produtos com baixo estoque.

## 🔄 Automação

### **Relatórios Automáticos**

```bash
# Configurar cron job para relatórios diários
# Adicionar ao crontab:

# Relatório diário às 18:00
0 18 * * * cd /path/to/project && php artisan telegram:report general --period=today

# Relatório semanal às 9:00 de segunda-feira
0 9 * * 1 cd /path/to/project && php artisan telegram:report general --period=week
```

### **Alertas Automáticos**

```php
// Em um Job ou Event
public function handle() {
    // Verificar estoque baixo
    $lowStockProducts = $this->productService->getLowStockProducts();

    if ($lowStockProducts->count() > 0) {
        $this->telegramBotService->processMessage([
            'chat' => ['id' => '123456789'],
            'text' => '/products baixo',
            'from' => ['id' => 'system', 'first_name' => 'System']
        ]);
    }
}
```

## 📈 Métricas e Monitoramento

### **Métricas Importantes**

- **Mensagens recebidas por hora**
- **Comandos mais utilizados**
- **Taxa de sucesso dos relatórios**
- **Tempo de resposta do bot**

### **Logs de Monitoramento**

```bash
# Monitorar uso do bot
tail -f storage/logs/laravel.log | grep "Telegram message received"

# Monitorar erros
tail -f storage/logs/laravel.log | grep "Telegram.*error"

# Monitorar relatórios gerados
tail -f storage/logs/laravel.log | grep "Report generated"
```

---

**🎯 Este exemplo demonstra como o sistema permite solicitar relatórios de forma simples e intuitiva via Telegram, fornecendo informações em tempo real sobre o sistema Rei do Óleo.**
