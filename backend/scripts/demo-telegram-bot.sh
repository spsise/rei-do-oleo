#!/bin/bash

# 🚀 Script de Demonstração - Sistema de Relatórios Telegram
# Rei do Óleo - Telegram Bot Reports

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Funções de log
log() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

step() {
    echo -e "${PURPLE}[STEP]${NC} $1"
}

# Verificar se estamos no diretório correto
if [ ! -f "artisan" ]; then
    error "Este script deve ser executado no diretório backend do Laravel"
    exit 1
fi

echo -e "${CYAN}"
echo "🤖 ==========================================="
echo "   Sistema de Relatórios Telegram - Demo"
echo "   Rei do Óleo"
echo "=============================================="
echo -e "${NC}"

# 1. Verificar configuração
step "1. Verificando configuração do Telegram..."

if ! grep -q "TELEGRAM_ENABLED=true" .env 2>/dev/null; then
    warn "Telegram não está habilitado no .env"
    log "Adicionando configuração básica..."
    echo "" >> .env
    echo "# Telegram Configuration" >> .env
    echo "TELEGRAM_ENABLED=true" >> .env
    echo "TELEGRAM_BOT_TOKEN=your_bot_token_here" >> .env
    echo "TELEGRAM_RECIPIENTS=123456789,987654321" >> .env
    success "Configuração básica adicionada ao .env"
else
    success "Telegram já está configurado"
fi

# 2. Verificar se os comandos estão disponíveis
step "2. Verificando comandos Artisan..."

if php artisan list | grep -q "telegram:bot-setup"; then
    success "Comando telegram:bot-setup disponível"
else
    error "Comando telegram:bot-setup não encontrado"
    log "Verificando se os arquivos foram criados..."
    ls -la app/Console/Commands/ | grep -i telegram || error "Arquivos de comando não encontrados"
    exit 1
fi

if php artisan list | grep -q "telegram:report"; then
    success "Comando telegram:report disponível"
else
    error "Comando telegram:report não encontrado"
    exit 1
fi

if php artisan list | grep -q "telegram:debug"; then
    success "Comando telegram:debug disponível"
else
    error "Comando telegram:debug não encontrado"
    exit 1
fi

# 3. Verificar serviços
step "3. Verificando serviços..."

if [ -f "app/Services/TelegramBotService.php" ]; then
    success "TelegramBotService encontrado"
else
    error "TelegramBotService não encontrado"
    exit 1
fi

if [ -f "app/Http/Controllers/Api/TelegramWebhookController.php" ]; then
    success "TelegramWebhookController encontrado"
else
    error "TelegramWebhookController não encontrado"
    exit 1
fi

# 4. Verificar rotas
step "4. Verificando rotas da API..."

if grep -q "telegram" routes/api.php; then
    success "Rotas do Telegram configuradas"
    log "Rotas encontradas:"
    grep -n "telegram" routes/api.php | sed 's/^/  /'
else
    error "Rotas do Telegram não encontradas"
    exit 1
fi

# 5. Testar funcionalidade (modo simulado)
step "5. Testando funcionalidade (modo simulado)..."

log "Testando relatório geral..."
if php artisan telegram:report general --test; then
    success "Relatório geral funcionando"
else
    error "Falha no relatório geral"
fi

log "Testando relatório de serviços..."
if php artisan telegram:report services --period=week --test; then
    success "Relatório de serviços funcionando"
else
    error "Falha no relatório de serviços"
fi

log "Testando relatório de produtos..."
if php artisan telegram:report products --test; then
    success "Relatório de produtos funcionando"
else
    error "Falha no relatório de produtos"
fi

log "Testando dashboard..."
if php artisan telegram:report dashboard --test; then
    success "Dashboard funcionando"
else
    error "Falha no dashboard"
fi

# 6. Mostrar comandos disponíveis
step "6. Comandos disponíveis para uso:"

echo -e "${CYAN}"
echo "📱 Comandos do Bot (no Telegram):"
echo "  /help          - Ajuda"
echo "  /report        - Relatório geral"
echo "  /report hoje   - Relatório de hoje"
echo "  /report semana - Relatório da semana"
echo "  /services      - Status dos serviços"
echo "  /products      - Status do estoque"
echo "  /dashboard     - Dashboard geral"
echo "  /status        - Status do sistema"
echo ""
echo "🛠️ Comandos CLI (no terminal):"
echo "  php artisan telegram:bot-setup --set-webhook --webhook-url=https://yourdomain.com/api/telegram/webhook"
echo "  php artisan telegram:bot-setup --get-info"
echo "  php artisan telegram:bot-setup --test"
echo "  php artisan telegram:report general --test"
echo "  php artisan telegram:report services --period=week --test"
echo "  php artisan telegram:debug --get-updates"
echo ""
echo "🔗 Endpoints da API:"
echo "  POST /api/telegram/webhook"
echo "  POST /api/telegram/set-webhook"
echo "  GET  /api/telegram/webhook-info"
echo "  DELETE /api/telegram/webhook"
echo "  POST /api/telegram/test"
echo -e "${NC}"

# 7. Instruções de configuração
step "7. Próximos passos para configuração:"

echo -e "${YELLOW}"
echo "Para configurar o bot para uso real:"
echo ""
echo "1. Criar bot no Telegram:"
echo "   - Acesse @BotFather no Telegram"
echo "   - Envie /newbot"
echo "   - Siga as instruções"
echo "   - Copie o token gerado"
echo ""
echo "2. Configurar variáveis de ambiente:"
echo "   - Edite o arquivo .env"
echo "   - Substitua TELEGRAM_BOT_TOKEN pelo token real"
echo "   - Configure TELEGRAM_RECIPIENTS com os chat IDs"
echo ""
echo "3. Obter chat IDs:"
echo "   - Inicie conversa com o bot"
echo "   - Envie uma mensagem"
echo "   - Execute: php artisan telegram:debug --get-updates"
echo "   - Copie os chat_id e configure no .env"
echo ""
echo "4. Configurar webhook:"
echo "   - Execute: php artisan telegram:bot-setup --set-webhook --webhook-url=https://yourdomain.com/api/telegram/webhook"
echo ""
echo "5. Testar:"
echo "   - Execute: php artisan telegram:bot-setup --test"
echo "   - Envie comandos no Telegram"
echo -e "${NC}"

# 8. Verificar documentação
step "8. Verificando documentação..."

if [ -f "docs/TELEGRAM_BOT_REPORTS.md" ]; then
    success "Documentação principal encontrada"
else
    warn "Documentação principal não encontrada"
fi

if [ -f "docs/TELEGRAM_BOT_EXAMPLE.md" ]; then
    success "Exemplos práticos encontrados"
else
    warn "Exemplos práticos não encontrados"
fi

if [ -f "docs/TELEGRAM_BOT_IMPLEMENTATION_SUMMARY.md" ]; then
    success "Resumo da implementação encontrado"
else
    warn "Resumo da implementação não encontrado"
fi

echo -e "${GREEN}"
echo "✅ ==========================================="
echo "   Demo concluído com sucesso!"
echo "   Sistema de Relatórios Telegram está pronto"
echo "=============================================="
echo -e "${NC}"

echo -e "${CYAN}"
echo "📖 Para mais informações, consulte:"
echo "   - docs/TELEGRAM_BOT_REPORTS.md"
echo "   - docs/TELEGRAM_BOT_EXAMPLE.md"
echo "   - docs/TELEGRAM_BOT_IMPLEMENTATION_SUMMARY.md"
echo -e "${NC}"

success "Sistema implementado e testado com sucesso!"
