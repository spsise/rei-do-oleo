# 🔄 Message Flow Tracking System

Sistema de rastreamento completo de mensagens do Telegram que monitora cada etapa do processamento e identifica onde as mensagens param ou falham.

## 🎯 Funcionalidades

- ✅ **Rastreamento completo** de cada método executado
- ❌ **Identificação de métodos faltantes**
- 📊 **Relatórios detalhados** para desenvolvedores
- 👤 **Relatórios amigáveis** para usuários
- 💾 **Cache de traces** para análise posterior
- ⚙️ **Totalmente configurável** via .env

## 🚀 Instalação

### 1. Verificar se os arquivos foram criados

```bash
# Verificar se a interface foi criada
ls -la app/Contracts/MessageFlowTrackerInterface.php

# Verificar se o serviço foi criado
ls -la app/Services/MessageFlowTrackerService.php

# Verificar se o trait foi criado
ls -la app/Traits/TrackableMessage.php

# Verificar se o provider foi criado
ls -la app/Providers/MessageFlowServiceProvider.php

# Verificar se a configuração foi criada
ls -la config/message-flow.php
```

### 2. Verificar se o provider foi registrado

```bash
# Verificar se está no config/app.php
grep -n "MessageFlowServiceProvider" config/app.php
```

### 3. Limpar cache e config

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

## ⚙️ Configuração

### Variáveis de Ambiente

Adicione ao seu arquivo `.env`:

```env
# ============================================================================
# MESSAGE FLOW TRACKING CONFIGURATION
# ============================================================================

# Enable/Disable Message Flow Tracking
MESSAGE_FLOW_TRACKING_ENABLED=true

# Output Configuration
MESSAGE_FLOW_SEND_TO_USER=true
MESSAGE_FLOW_LOG_TO_FILE=true
MESSAGE_FLOW_CACHE_TRACES=true

# Performance Thresholds (milliseconds)
MESSAGE_FLOW_SLOW_THRESHOLD=100
MESSAGE_FLOW_VERY_SLOW_THRESHOLD=500

# Memory Thresholds (MB)
MESSAGE_FLOW_MEMORY_WARNING=50
MESSAGE_FLOW_MEMORY_CRITICAL=100

# Storage TTL (minutes)
MESSAGE_FLOW_CACHE_TTL=1440
MESSAGE_FLOW_DB_TTL=10080
MESSAGE_FLOW_FILE_TTL=43200

# Limits
MESSAGE_FLOW_MAX_TRACES=100
MESSAGE_FLOW_MAX_STAGES=50
MESSAGE_FLOW_CLEANUP_FREQ=3600

# Report Configuration
MESSAGE_FLOW_USER_REPORT=true
MESSAGE_FLOW_DEV_REPORT=true
MESSAGE_FLOW_PERF_REPORT=true
MESSAGE_FLOW_INCLUDE_INSIGHTS=true
MESSAGE_FLOW_INCLUDE_RECOMMENDATIONS=true
MESSAGE_FLOW_INCLUDE_ERRORS=true
```

## 🧪 Testando

### Comando Artisan

```bash
# Teste básico
php artisan message-flow:test

# Teste com payload customizado
php artisan message-flow:test --payload='{"update_id":123,"message":{"text":"Hello"}}'
```

### Teste via Webhook

1. Envie uma mensagem para o bot
2. Verifique os logs do Laravel
3. Se habilitado, o usuário receberá o relatório

## 📊 Como Funciona

### 1. Início do Tracking

```php
// No controller
$this->flowTracker->startTracking(uniqid('webhook_', true));
```

### 2. Tracking de Métodos

```php
// Início do método
$this->trackMethod('processTextMessage', ['message' => $message]);

// Fim do método
$this->endMethod('processTextMessage', ['result' => $result]);
```

### 3. Geração de Relatórios

```php
// Relatório completo para desenvolvedores
$flowReport = $this->flowTracker->generateFlowReport($payload);

// Relatório resumido para usuários
$userReport = $this->flowTracker->generateUserReport($payload);
```

### 4. Finalização

```php
// Salva trace e limpa dados
$this->flowTracker->endTracking();
```

## 🔍 Exemplo de Relatório

### Relatório Completo (Desenvolvedor)

```
🔄 **Rastreamento da Mensagem**

📋 **Informações Gerais**
• ID da Mensagem: `msg_64f8a1b2c3d4e`
• Tipo: 📝 TEXT
• Conteúdo: "Desconhecida"
• Timestamp: 14:30:25.123456

✅ **Métodos Executados**
1. **TelegramMessageProcessorService.processWebhookPayload**
   ⏱️ Tempo: 2.45ms
   📥 Entrada: payload: {...}
   📤 Saída: result: {...}

2. **TelegramMessageProcessorService.processTextMessage**
   ⏱️ Tempo: 1.23ms
   📥 Entrada: message: {...}
   📤 Saída: result: {...}

❌ **Métodos NÃO Executados (Esperados)**
• **TelegramBotService.processMessage** - ❌ NÃO EXECUTADO
• **TelegramCommandParser.parseCommand** - ❌ NÃO EXECUTADO
• **TelegramCommandHandlerManager.handleCommand** - ❌ NÃO EXECUTADO
• **TelegramChannel.sendTextMessage** - ❌ NÃO EXECUTADO

⚠️ **Análise do Problema**
A mensagem não foi processada completamente porque:
• Falha no serviço principal do bot
• Falha na interpretação do comando
• Falha no gerenciamento de comandos
• Falha no envio da resposta

🔧 **Dicas para Debug**
• Verifique logs do Laravel para erros
• Confirme se todos os serviços estão ativos
• Verifique permissões e configurações
```

### Relatório Resumido (Usuário)

```
❌ Sua mensagem não foi processada completamente.

**Etapas executadas:** 2/6

**Etapas que falharam:**
• Validação da mensagem
• Processamento do texto

💡 **Sugestões:**
• Tente novamente em alguns instantes
• Use comandos simples como /start ou /help
• Se o problema persistir, contate o suporte
```

## 🛠️ Personalização

### Adicionar Novos Métodos ao Tracking

Edite o arquivo `app/Services/MessageFlowTrackerService.php`:

```php
private array $expectedMethods = [
    'text' => [
        'TelegramMessageProcessorService.processWebhookPayload',
        'TelegramMessageProcessorService.processTextMessage',
        'TelegramBotService.processMessage',
        'TelegramCommandParser.parseCommand',
        'TelegramCommandHandlerManager.handleCommand',
        'TelegramChannel.sendTextMessage',
        // Adicione novos métodos aqui
        'NovoService.novoMetodo'
    ],
    // ... outros tipos
];
```

### Adicionar Novos Tipos de Mensagem

```php
'image' => [
    'TelegramMessageProcessorService.processWebhookPayload',
    'TelegramMessageProcessorService.processImageMessage',
    'ImageProcessingService.processImage',
    'TelegramChannel.sendTextMessage'
],
```

## 📝 Logs

### Logs do Sistema

```bash
# Ver logs do Laravel
tail -f storage/logs/laravel.log | grep "Message flow"

# Exemplo de log:
# [2024-01-15 14:30:25] local.INFO: Message flow: Method executed {"message_id":"msg_64f8a1b2c3d4e","method":"TelegramMessageProcessorService.processWebhookPayload","input_data":{"payload":{...}}}
```

### Cache de Traces

```bash
# Ver traces no cache
php artisan tinker
>>> Cache::get('message_trace:msg_64f8a1b2c3d4e')
```

## 🚨 Troubleshooting

### Problema: Sistema não está funcionando

1. **Verificar se está habilitado:**

   ```bash
   php artisan tinker
   >>> config('message-flow.enabled')
   ```

2. **Verificar se o provider foi registrado:**

   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

3. **Verificar logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Problema: Relatórios não são enviados

1. **Verificar configuração:**

   ```env
   MESSAGE_FLOW_SEND_TO_USER=true
   ```

2. **Verificar se o bot tem permissão para enviar mensagens**

3. **Verificar logs de erro**

## 🔄 Próximos Passos

- [ ] Implementar tracking em outros serviços
- [ ] Adicionar métricas de performance
- [ ] Criar dashboard de monitoramento
- [ ] Implementar alertas automáticos
- [ ] Adicionar suporte a outros tipos de mensagem

## 📞 Suporte

Para dúvidas ou problemas:

1. Verifique os logs do Laravel
2. Execute o comando de teste: `php artisan message-flow:test`
3. Verifique a configuração no arquivo `.env`
4. Consulte este documento

---

**🎯 Sistema implementado com sucesso! Agora você pode rastrear exatamente por onde suas mensagens passam!**
