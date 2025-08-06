# 📱 Sistema Unificado de Notificações

## 🎯 Visão Geral

O sistema unificado de notificações permite enviar mensagens através de múltiplos canais (WhatsApp, Telegram, etc.) usando uma única interface. O sistema automaticamente escolhe os canais disponíveis e envia as mensagens de forma transparente.

## 🏗️ Arquitetura

### **Padrão Strategy**

- **Interface**: `NotificationChannelInterface`
- **Implementações**: `WhatsAppChannel`, `TelegramChannel`
- **Serviço Unificado**: `UnifiedNotificationService`

### **Canais Suportados**

- ✅ **WhatsApp** - Via WhatsApp Business API
- ✅ **Telegram** - Via Telegram Bot API
- 🔄 **Extensível** - Fácil adição de novos canais

## 🔧 Configuração

### **1. Variáveis de Ambiente**

```env
# WhatsApp Configuration
WHATSAPP_ENABLED=true
WHATSAPP_API_URL=https://graph.facebook.com
WHATSAPP_ACCESS_TOKEN=your_access_token
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_VERSION=v18.0
WHATSAPP_DEPLOY_RECIPIENTS=5511999999999,5511888888888

# Telegram Configuration
TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_RECIPIENTS=123456789,987654321
```

### **2. Configurar Telegram Bot**

1. **Criar Bot**:

   - Acesse [@BotFather](https://t.me/botfather) no Telegram
   - Envie `/newbot`
   - Siga as instruções para criar o bot
   - Copie o token gerado

2. **Obter Chat IDs**:
   - Adicione o bot aos chats/grupos
   - Acesse: `https://api.telegram.org/bot{YOUR_TOKEN}/getUpdates`
   - Copie os `chat_id` dos destinatários

## 🚀 Como Usar

### **1. Uso Simples - Uma Linha**

```php
use App\Services\UnifiedNotificationService;

// Enviar para todos os canais disponíveis
$notificationService = app(UnifiedNotificationService::class);
$result = $notificationService->sendMessage("Sua mensagem aqui");
```

### **2. Uso com Canais Específicos**

```php
// Enviar apenas para WhatsApp
$result = $notificationService->sendMessage(
    "Mensagem apenas para WhatsApp",
    null,
    ['whatsapp']
);

// Enviar apenas para Telegram
$result = $notificationService->sendMessage(
    "Mensagem apenas para Telegram",
    null,
    ['telegram']
);

// Enviar para ambos
$result = $notificationService->sendMessage(
    "Mensagem para ambos",
    null,
    ['whatsapp', 'telegram']
);
```

### **3. Uso com Destinatário Específico**

```php
// Enviar para número específico
$result = $notificationService->sendMessage(
    "Mensagem personalizada",
    "5511999999999"
);
```

### **4. Tipos de Notificação**

#### **Mensagem Simples**

```php
$result = $notificationService->sendMessage("Olá, mundo!");
```

#### **Alerta do Sistema**

```php
$result = $notificationService->sendSystemAlert(
    "Título do Alerta",
    "Mensagem do alerta",
    "warning" // info, warning, error, success
);
```

#### **Notificação de Deploy**

```php
$deployData = [
    'status' => 'success',
    'branch' => 'main',
    'commit' => 'abc123',
    'message' => 'Deploy realizado com sucesso',
    'timestamp' => now()->format('d/m/Y H:i:s'),
    'output' => 'Build completed'
];

$result = $notificationService->sendDeployNotification($deployData);
```

## 📡 API Endpoints

### **1. Enviar Mensagem**

```http
POST /api/unified-notifications/send-message
Content-Type: application/json

{
    "message": "Sua mensagem",
    "recipient": "5511999999999",
    "channels": ["whatsapp", "telegram"]
}
```

### **2. Alerta do Sistema**

```http
POST /api/unified-notifications/system-alert
Content-Type: application/json

{
    "title": "Título do Alerta",
    "message": "Mensagem do alerta",
    "level": "warning",
    "channels": ["whatsapp", "telegram"]
}
```

### **3. Notificação de Deploy**

```http
POST /api/unified-notifications/deploy
Content-Type: application/json

{
    "status": "success",
    "branch": "main",
    "commit": "abc123",
    "message": "Deploy realizado",
    "output": "Build completed",
    "channels": ["whatsapp", "telegram"]
}
```

### **4. Testar Canais**

```http
GET /api/unified-notifications/test-channels
GET /api/unified-notifications/channels
GET /api/unified-notifications/test-channel/whatsapp
GET /api/unified-notifications/test-channel/telegram
```

## 🛠️ Comandos Artisan

### **1. Enviar Mensagem**

```bash
# Mensagem simples
php artisan notify:send "Sua mensagem"

# Com destinatário específico
php artisan notify:send "Mensagem personalizada" --recipient=5511999999999

# Para canais específicos
php artisan notify:send "Mensagem" --channels=whatsapp --channels=telegram
```

### **2. Alerta do Sistema**

```bash
php artisan notify:send "Mensagem do alerta" \
  --type=system-alert \
  --title="Título do Alerta" \
  --level=warning
```

### **3. Notificação de Deploy**

```bash
php artisan notify:send "Deploy realizado" \
  --type=deploy \
  --status=success \
  --branch=main \
  --commit=abc123
```

## 📋 Exemplos de Uso

### **1. Em Controllers**

```php
use App\Services\UnifiedNotificationService;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // ... lógica do pedido

        // Notificar sobre novo pedido
        $notificationService = app(UnifiedNotificationService::class);
        $notificationService->sendSystemAlert(
            "Novo Pedido",
            "Pedido #{$order->id} criado por {$order->customer->name}",
            "success"
        );
    }
}
```

### **2. Em Jobs**

```php
use App\Services\UnifiedNotificationService;

class ProcessOrderJob implements ShouldQueue
{
    public function handle(UnifiedNotificationService $notificationService)
    {
        // ... processamento

        // Notificar conclusão
        $notificationService->sendMessage(
            "Pedido processado com sucesso",
            null,
            ['whatsapp'] // Apenas WhatsApp
        );
    }
}
```

### **3. Em Events**

```php
use App\Services\UnifiedNotificationService;

class OrderCreated
{
    public function handle(UnifiedNotificationService $notificationService)
    {
        $notificationService->sendSystemAlert(
            "Pedido Criado",
            "Novo pedido recebido",
            "info"
        );
    }
}
```

## 🔍 Troubleshooting

### **1. Verificar Canais Disponíveis**

```bash
php artisan notify:send "Teste" --channels=whatsapp,telegram
```

### **2. Testar Conexões**

```bash
# Testar todos os canais
curl http://localhost:8000/api/unified-notifications/test-channels

# Testar canal específico
curl http://localhost:8000/api/unified-notifications/test-channel/whatsapp
```

### **3. Verificar Logs**

```bash
tail -f storage/logs/laravel.log | grep "Unified notification"
```

## 🎨 Formato das Mensagens

### **WhatsApp**

```
🚀 DEPLOY NOTIFICATION

✅ Status: SUCESSO
🌿 Branch: main
🔗 Commit: abc123
💬 Message: Deploy realizado
⏰ Timestamp: 15/01/2024 14:30:25

Sistema: Rei do Óleo
```

### **Telegram**

```
🚀 *DEPLOY NOTIFICATION*

✅ *Status:* SUCESSO
🌿 *Branch:* main
🔗 *Commit:* abc123
💬 *Message:* Deploy realizado
⏰ *Timestamp:* 15/01/2024 14:30:25

Sistema: Rei do Óleo
```

## 🔮 Extensibilidade

### **Adicionar Novo Canal**

1. **Criar Implementação**:

```php
class SlackChannel implements NotificationChannelInterface
{
    public function sendTextMessage(string $message, ?string $recipient = null): array
    {
        // Implementação do Slack
    }

    // ... outros métodos
}
```

2. **Registrar no Serviço**:

```php
// Em UnifiedNotificationService::registerChannels()
if (config('services.slack.enabled', true)) {
    $this->channels['slack'] = app(SlackChannel::class);
}
```

3. **Adicionar Configuração**:

```env
SLACK_ENABLED=true
SLACK_WEBHOOK_URL=your_webhook_url
SLACK_CHANNEL=#notifications
```

## ✅ Benefícios

- **Simplicidade**: Uma interface para todos os canais
- **Flexibilidade**: Escolha quais canais usar
- **Extensibilidade**: Fácil adição de novos canais
- **Robustez**: Fallback automático se um canal falhar
- **Monitoramento**: Logs detalhados de todas as operações
