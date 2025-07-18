# 📱 Configuração WhatsApp Business API

## Visão Geral

Este sistema integra com a WhatsApp Business API para enviar notificações automáticas sobre deploys e outros eventos importantes do sistema.

## 🔧 Configuração

### 1. Variáveis de Ambiente

Adicione as seguintes variáveis no arquivo `.env`:

```env
# WhatsApp Business API Configuration
WHATSAPP_API_URL=https://graph.facebook.com
WHATSAPP_ACCESS_TOKEN=your_access_token_here
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id_here
WHATSAPP_VERSION=v18.0
WHATSAPP_DEPLOY_RECIPIENTS=5511999999999,5511888888888
```

### 2. Configuração da WhatsApp Business API

1. **Criar conta no Facebook Developers**:

   - Acesse [developers.facebook.com](https://developers.facebook.com)
   - Crie uma nova aplicação
   - Adicione o produto "WhatsApp Business API"

2. **Configurar número de telefone**:

   - Vá para "WhatsApp > Getting Started"
   - Adicione um número de telefone
   - Anote o `Phone Number ID`

3. **Gerar Access Token**:

   - Vá para "WhatsApp > Getting Started"
   - Clique em "Generate Token"
   - Copie o token gerado

4. **Configurar webhook** (opcional):
   - Para receber mensagens, configure o webhook
   - URL: `https://seu-dominio.com/api/webhook/whatsapp`

### 3. Números de Destinatários

Configure os números que receberão as notificações:

```env
# Separados por vírgula, sem espaços
WHATSAPP_DEPLOY_RECIPIENTS=5511999999999,5511888888888,5511777777777
```

**Formato dos números**:

- Código do país (55 para Brasil)
- DDD (11, 21, etc.)
- Número do telefone
- Exemplo: `5511999999999`

## 🚀 Uso

### 1. Notificações Automáticas de Deploy

As notificações são enviadas automaticamente quando:

- ✅ Deploy realizado com sucesso
- ❌ Deploy falhou
- ⚠️ Exceção durante o deploy

### 2. Comandos Artisan

#### Testar Conexão

```bash
php artisan whatsapp:test
```

#### Testar Notificação de Deploy

```bash
php artisan whatsapp:test --deploy
```

#### Enviar Mensagem Customizada

```bash
php artisan whatsapp:test --message="Teste de mensagem" --phone=5511999999999
```

### 3. API Endpoints

#### Testar Conexão

```http
GET /api/notifications/whatsapp/test-connection
```

#### Enviar Mensagem Customizada

```http
POST /api/notifications/whatsapp/custom
Content-Type: application/json

{
    "message": "Sua mensagem aqui",
    "phone_number": "5511999999999"
}
```

#### Enviar Alerta do Sistema

```http
POST /api/notifications/whatsapp/system-alert
Content-Type: application/json

{
    "title": "Título do Alerta",
    "message": "Mensagem do alerta",
    "level": "info|warning|error|success"
}
```

#### Enviar Notificação de Pedido

```http
POST /api/notifications/whatsapp/order
Content-Type: application/json

{
    "order_id": 123,
    "customer_name": "João Silva",
    "total": 150.50,
    "items_count": 3
}
```

#### Enviar Alerta de Estoque

```http
POST /api/notifications/whatsapp/stock-alert
Content-Type: application/json

{
    "product_name": "Óleo 5W30",
    "current_quantity": 5,
    "min_quantity": 10,
    "product_code": "OIL001"
}
```

### 4. Uso em Código

#### Usando o Trait

```php
use App\Traits\HasWhatsAppNotifications;

class OrderController extends Controller
{
    use HasWhatsAppNotifications;

    public function store(Request $request)
    {
        // ... lógica do pedido

        // Enviar notificação
        $this->sendWhatsAppNotification("Novo pedido criado!");

        // Ou usar métodos específicos
        $this->sendOrderWhatsAppNotification([
            'id' => $order->id,
            'customer_name' => $order->customer->name,
            'total' => $order->total,
            'items_count' => $order->items->count()
        ]);
    }
}
```

#### Usando o Service Diretamente

```php
use App\Services\WhatsAppService;

class ProductController extends Controller
{
    public function updateStock(Request $request, $id)
    {
        // ... lógica de atualização

        if ($product->stock < $product->min_stock) {
            $whatsappService = app(WhatsAppService::class);

            $whatsappService->sendDeployNotification([
                'status' => 'warning',
                'branch' => 'stock-alert',
                'commit' => 'low-stock',
                'message' => "Produto {$product->name} com estoque baixo",
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);
        }
    }
}
```

## 📋 Exemplos de Mensagens

### Deploy Sucesso

```
🚀 DEPLOY NOTIFICATION

✅ Status: SUCESSO
🌿 Branch: hostinger-hom
🔗 Commit: abc123def456
💬 Message: Fix: Corrige bug no login
⏰ Timestamp: 15/01/2024 14:30:25

Sistema: Rei do Óleo
```

### Deploy Erro

```
🚀 DEPLOY NOTIFICATION

❌ Status: ERRO
🌿 Branch: hostinger-hom
🔗 Commit: abc123def456
💬 Message: Add: Nova funcionalidade
⏰ Timestamp: 15/01/2024 14:30:25

Sistema: Rei do Óleo
```

### Alerta do Sistema

```
🚨 SYSTEM ALERT

⚠️ Título do Alerta
💬 Mensagem do alerta
⏰ 15/01/2024 14:30:25

Sistema: Rei do Óleo
```

### Novo Pedido

```
🛒 NOVO PEDIDO

📋 Pedido #123
👤 Cliente: João Silva
💰 Total: R$ 150,50
📦 Itens: 3
⏰ 15/01/2024 14:30:25

Sistema: Rei do Óleo
```

### Alerta de Estoque

```
📦 ALERTA DE ESTOQUE

⚠️ Produto: Óleo 5W30
📊 Quantidade Atual: 5
🔴 Quantidade Mínima: 10
📋 Código: OIL001
⏰ 15/01/2024 14:30:25

Sistema: Rei do Óleo
```

## 🔍 Troubleshooting

### Erro de Conexão

```bash
# Verificar configurações
php artisan whatsapp:test

# Verificar logs
tail -f storage/logs/laravel.log | grep WhatsApp
```

### Erro de Autenticação

- Verifique se o `WHATSAPP_ACCESS_TOKEN` está correto
- Confirme se o token não expirou
- Verifique se a aplicação tem permissões corretas

### Mensagens Não Enviadas

- Verifique se os números estão no formato correto
- Confirme se os números estão cadastrados no WhatsApp Business
- Verifique os logs para detalhes do erro

### Rate Limiting

- A WhatsApp Business API tem limites de envio
- Máximo de 1000 mensagens por segundo
- Implemente retry logic se necessário

## 📊 Monitoramento

### Logs

As notificações são logadas em:

- `storage/logs/laravel.log`
- Filtro: `grep WhatsApp storage/logs/laravel.log`

### Métricas

- Mensagens enviadas com sucesso
- Mensagens com erro
- Tempo de resposta da API
- Rate limiting

## 🔒 Segurança

- Nunca commite tokens no código
- Use variáveis de ambiente
- Implemente rate limiting
- Monitore logs de acesso
- Use HTTPS para webhooks

## 📞 Suporte

Para problemas com a WhatsApp Business API:

- [Documentação Oficial](https://developers.facebook.com/docs/whatsapp)
- [Status da API](https://developers.facebook.com/status/)
- [Comunidade](https://developers.facebook.com/community/)
