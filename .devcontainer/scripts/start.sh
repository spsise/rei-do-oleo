#!/bin/bash

# 🚀 Start Script - Sistema Rei do Óleo
# Inicia todos os serviços necessários para desenvolvimento

set -e

# Cores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[START]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }

cd /workspace

# Banner
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                🚀 INICIANDO SERVIÇOS                      ║
║                Sistema Rei do Óleo                        ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Verificar se os serviços estão rodando
log "🔍 Verificando status dos serviços..."

# Aguardar MySQL
for i in {1..30}; do
    if mysqladmin ping -h mysql -u root -proot123 --silent 2>/dev/null; then
        log "✅ MySQL está funcionando"
        break
    fi
    if [ $i -eq 30 ]; then
        warn "⚠️ MySQL não responde"
    fi
    sleep 1
done

# Aguardar Redis
for i in {1..10}; do
    if redis-cli -h redis ping >/dev/null 2>&1; then
        log "✅ Redis está funcionando"
        break
    fi
    if [ $i -eq 10 ]; then
        warn "⚠️ Redis não responde"
    fi
    sleep 1
done

# Verificar se as dependências estão instaladas
if [ -d "backend" ]; then
    cd backend
    if [ ! -d "vendor" ]; then
        log "📦 Instalando dependências do backend..."
        composer install
    fi
    
    # Verificar se precisa executar migrações
    if php artisan migrate:status 2>/dev/null | grep -q "No migrations found"; then
        log "🗄️ Executando migrações..."
        php artisan migrate
    fi
    
    cd /workspace
fi

if [ -d "frontend" ]; then
    cd frontend
    if [ ! -d "node_modules" ]; then
        log "📦 Instalando dependências do frontend..."
        npm install
    fi
    cd /workspace
fi

# Limpar caches do Laravel
if [ -d "backend" ]; then
    log "🧹 Limpando caches Laravel..."
    cd backend
    php artisan cache:clear >/dev/null 2>&1 || true
    php artisan config:clear >/dev/null 2>&1 || true
    php artisan route:clear >/dev/null 2>&1 || true
    php artisan view:clear >/dev/null 2>&1 || true
    cd /workspace
fi

log "✅ Todos os serviços estão prontos!"
info "🎯 Use 'npm run dev' para iniciar o desenvolvimento"
info "🌐 Acesse http://localhost:3000 para o frontend"
info "🔧 Acesse http://localhost:8000 para a API" 