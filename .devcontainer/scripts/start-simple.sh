#!/bin/bash

# 🚀 Start Script para Ambiente Simplificado
# Este script inicia os serviços essenciais do ambiente simplificado

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log "🚀 Iniciando ambiente de desenvolvimento simplificado..."

# Verificar se estamos no diretório correto
if [ ! -f "/workspace/backend/artisan" ]; then
    echo "Este script deve ser executado dentro do devcontainer"
    exit 1
fi

# Navegar para o backend
cd /workspace/backend

# Verificar se o banco está acessível
log "Verificando conexão com o banco de dados..."
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Conexão OK';" > /dev/null 2>&1; then
    success "✅ Banco de dados conectado"
else
    warning "⚠️  Banco de dados não está acessível ainda. Aguardando..."
    sleep 5
fi

# Limpar cache
log "Limpando cache do Laravel..."
php artisan config:clear
php artisan cache:clear

# Verificar migrações
log "Verificando status das migrações..."
php artisan migrate:status

# Navegar para o frontend
cd /workspace/frontend

# Verificar dependências do frontend
if [ ! -d "node_modules" ]; then
    log "Instalando dependências do frontend..."
    npm install
fi

# Verificar se o build funciona
log "Verificando build do frontend..."
npm run type-check

success "✅ Ambiente simplificado iniciado com sucesso!"
success "🚀 Backend Laravel: http://localhost:8000"
success "⚛️ Frontend React: http://localhost:3000"
success "🗄️ phpMyAdmin: http://localhost:8110"

echo ""
echo "📝 Para iniciar os servidores de desenvolvimento:"
echo "  Backend: cd /workspace/backend && php artisan serve"
echo "  Frontend: cd /workspace/frontend && npm run dev"
echo ""

warning "⚠️  Ambiente simplificado ativo - sem Redis, MailHog, Queue, Scheduler"

