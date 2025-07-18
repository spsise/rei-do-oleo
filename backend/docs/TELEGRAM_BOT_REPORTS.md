# 🤖 Sistema de Relatórios via Telegram Bot

## 🎯 Visão Geral

O sistema de relatórios via Telegram bot permite solicitar e receber relatórios do sistema Rei do Óleo diretamente no aplicativo Telegram. O bot processa comandos e linguagem natural para gerar relatórios em tempo real.

## 🏗️ Arquitetura

### **Componentes Principais**

- **TelegramBotService**: Serviço principal para processamento de mensagens
- **TelegramWebhookController**: Controller para receber webhooks do Telegram
- **TelegramChannel**: Canal de envio de mensagens (já existente)
- **Comandos Artisan**: Para configuração e testes

### **Fluxo de Funcionamento**

1. **Usuário envia mensagem** → Telegram
2. **Telegram envia webhook** → API Laravel
3. **TelegramWebhookController** → Processa webhook
4. **TelegramBotService** → Interpreta comando
5. **Serviços de negócio** → Geram dados
6. **TelegramChannel** → Envia resposta
7. **Usuário recebe relatório** → Telegram

## 🔧 Configuração

### **1. Variáveis de Ambiente**

```env
# Telegram Configuration
TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_RECIPIENTS=123456789,987654321
```

### **2. Criar Bot no Telegram**

1. **Acesse [@BotFather](https://t.me/botfather)**
2. **Envie `/newbot`**
3. **Siga as instruções para criar o bot**
4. **Copie o token gerado**

### **3. Obter Chat IDs**

```bash
# 1. Inicie conversa com o bot
# 2. Envie uma mensagem qualquer
# 3. Execute o comando para obter chat IDs
php artisan telegram:debug --get-updates
```

### **4. Configurar Webhook**

```bash
# Configurar webhook (substitua pela sua URL)
php artisan telegram:bot-setup --set-webhook --webhook-url=https://yourdomain.com/api/telegram/webhook
```

## 🚀 Como Usar

### **Comandos Disponíveis**

#### **📊 Relatórios Gerais**

- `/report` - Relatório geral de hoje
- `/report hoje` - Relatório de hoje
- `/report semana` - Relatório da semana
- `/report mês` - Relatório do mês

#### **🔧 Relatórios de Serviços**

- `/services` - Status dos serviços de hoje
- `/services hoje` - Serviços de hoje
- `/services semana` - Serviços da semana
- `/services mês` - Serviços do mês

#### **📦 Relatórios de Produtos**

- `/products` - Status do estoque
- `/products baixo` - Produtos com estoque baixo

#### **📈 Dashboard**

- `/dashboard` - Resumo geral do sistema
- `/status` - Status do sistema

#### **❓ Ajuda**

- `/help` - Lista todos os comandos disponíveis

### **Linguagem Natural**

O bot também entende linguagem natural:

- **"Envie um relatório de hoje"** → `/report hoje`
- **"Como estão os serviços da semana?"** → `/services semana`
- **"Mostre o dashboard"** → `/dashboard`
- **"Status do sistema"** → `/status`

## 📋 Exemplos de Uso

### **Exemplo 1: Relatório Geral**

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

### **Exemplo 2: Relatório de Serviços**

```
Usuário: /services hoje

Bot responde:
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

### **Exemplo 3: Relatório de Produtos**

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

## 🛠️ Comandos Artisan

### **Configuração do Bot**

```bash
# Configurar webhook
php artisan telegram:bot-setup --set-webhook --webhook-url=https://yourdomain.com/api/telegram/webhook

# Verificar informações do webhook
php artisan telegram:bot-setup --get-info

# Deletar webhook
php artisan telegram:bot-setup --delete-webhook

# Testar bot
php artisan telegram:bot-setup --test
```

### **Geração de Relatórios**

```bash
# Gerar relatório geral (teste)
php artisan telegram:report general --period=today --test

# Gerar relatório de serviços (produção)
php artisan telegram:report services --period=week

# Gerar relatório de produtos
php artisan telegram:report products

# Enviar para chat específico
php artisan telegram:report dashboard --chat-id=123456789
```

### **Debug e Troubleshooting**

```bash
# Debug do bot
php artisan telegram:debug --get-updates
php artisan telegram:debug --send-test
php artisan telegram:debug --validate-token
```

## 🔌 API Endpoints

### **Webhook do Telegram**

- `POST /api/telegram/webhook` - Recebe mensagens do Telegram

### **Gerenciamento de Webhook**

- `POST /api/telegram/set-webhook` - Configurar webhook
- `GET /api/telegram/webhook-info` - Informações do webhook
- `DELETE /api/telegram/webhook` - Deletar webhook
- `POST /api/telegram/test` - Testar bot

## 🔒 Segurança

### **Autorização**

- Apenas usuários autorizados podem usar o bot
- Chat IDs devem estar configurados em `TELEGRAM_RECIPIENTS`
- Verificação automática de autorização em cada comando

### **Rate Limiting**

- Implementado rate limiting no webhook
- Proteção contra spam e ataques

### **Logs**

- Todas as mensagens são logadas
- Erros são registrados para debugging
- Auditoria completa de comandos executados

## 🧪 Testes

### **Teste Manual**

1. **Configure o bot**:

   ```bash
   php artisan telegram:bot-setup --set-webhook --webhook-url=https://yourdomain.com/api/telegram/webhook
   ```

2. **Teste o bot**:

   ```bash
   php artisan telegram:bot-setup --test
   ```

3. **Envie comandos no Telegram**:
   - `/help`
   - `/report`
   - `/services`
   - `/products`

### **Teste via CLI**

```bash
# Teste de relatório geral
php artisan telegram:report general --test

# Teste de relatório de serviços
php artisan telegram:report services --period=week --test

# Teste de relatório de produtos
php artisan telegram:report products --test
```

## 🔍 Troubleshooting

### **Problemas Comuns**

#### **1. Bot não responde**

```bash
# Verificar configuração
php artisan telegram:bot-setup --get-info

# Verificar logs
tail -f storage/logs/laravel.log | grep "Telegram"
```

#### **2. Webhook não configurado**

```bash
# Configurar webhook
php artisan telegram:bot-setup --set-webhook --webhook-url=https://yourdomain.com/api/telegram/webhook
```

#### **3. Token inválido**

```bash
# Validar token
php artisan telegram:debug --validate-token
```

#### **4. Chat ID não autorizado**

```bash
# Verificar chat IDs
php artisan telegram:debug --get-updates

# Atualizar .env
TELEGRAM_RECIPIENTS=123456789,987654321
```

### **Logs Importantes**

```bash
# Logs do Telegram
tail -f storage/logs/laravel.log | grep "Telegram"

# Logs de webhook
tail -f storage/logs/laravel.log | grep "webhook"

# Logs de erro
tail -f storage/logs/laravel.log | grep "ERROR"
```

## 📊 Monitoramento

### **Métricas Importantes**

- **Mensagens recebidas por hora**
- **Comandos mais utilizados**
- **Taxa de sucesso dos relatórios**
- **Tempo de resposta do bot**

### **Alertas**

- **Bot offline**
- **Erros de webhook**
- **Falhas na geração de relatórios**
- **Usuários não autorizados**

## 🔮 Funcionalidades Futuras

### **Planejadas**

- [ ] **Relatórios personalizados**
- [ ] **Agendamento de relatórios**
- [ ] **Exportação em PDF**
- [ ] **Gráficos e visualizações**
- [ ] **Alertas automáticos**
- [ ] **Integração com outros canais**

### **Melhorias**

- [ ] **Cache de relatórios**
- [ ] **Compressão de mensagens**
- [ ] **Suporte a múltiplos idiomas**
- [ ] **Comandos de voz**
- [ ] **Interface web para configuração**

## 📝 Exemplos de Configuração

### **Configuração Completa (.env)**

```env
# Telegram Bot Configuration
TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_RECIPIENTS=123456789,987654321,555666777

# Webhook URL (HTTPS obrigatório)
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/api/telegram/webhook
```

### **Configuração de Produção**

```bash
# 1. Configurar webhook
php artisan telegram:bot-setup --set-webhook --webhook-url=https://yourdomain.com/api/telegram/webhook

# 2. Verificar configuração
php artisan telegram:bot-setup --get-info

# 3. Testar bot
php artisan telegram:bot-setup --test

# 4. Testar relatórios
php artisan telegram:report general --test
php artisan telegram:report services --test
php artisan telegram:report products --test
```

## 🎯 Resumo Rápido

### **Comandos Essenciais**

```bash
# Configuração
php artisan telegram:bot-setup --set-webhook --webhook-url=https://yourdomain.com/api/telegram/webhook

# Testes
php artisan telegram:bot-setup --test
php artisan telegram:report general --test

# Debug
php artisan telegram:debug --get-updates
```

### **Comandos do Bot**

- `/help` - Ajuda
- `/report` - Relatório geral
- `/services` - Relatório de serviços
- `/products` - Relatório de produtos
- `/dashboard` - Dashboard geral
- `/status` - Status do sistema

### **URLs Importantes**

- **Webhook**: `POST /api/telegram/webhook`
- **Configuração**: `POST /api/telegram/set-webhook`
- **Informações**: `GET /api/telegram/webhook-info`
- **Teste**: `POST /api/telegram/test`

---

**📖 Para mais informações, consulte a documentação específica de cada componente.**
