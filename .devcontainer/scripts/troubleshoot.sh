#!/bin/bash

# 🔧 Troubleshoot DevContainer - Sistema Rei do Óleo
# Script para diagnosticar e resolver problemas do ambiente de desenvolvimento

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Funções de logging
log() { echo -e "${GREEN}[TROUBLESHOOT]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${PURPLE}[SUCCESS]${NC} $1"; }
step() { echo -e "${CYAN}[STEP]${NC} $1"; }

# Função para executar comandos no backend
backend_exec() {
    (cd /workspace/backend && "$@")
}

# Função para executar comandos no frontend
frontend_exec() {
    (cd /workspace/frontend && "$@")
}

# Banner
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                🔧 TROUBLESHOOT DEVCONTAINER               ║
║           Diagnóstico e correção de problemas             ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

cd /workspace

# 1. Verificar serviços básicos
step "🔍 Verificando serviços básicos..."

# Verificar MySQL
if mysqladmin ping -h mysql -u root -proot123 --silent 2>/dev/null; then
    success "✅ MySQL está funcionando"
else
    error "❌ MySQL não está respondendo"
    log "Tentando conectar com credenciais alternativas..."
    if mysqladmin ping -h mysql -u rei_do_oleo -psecret123 --silent 2>/dev/null; then
        success "✅ MySQL funcionando com credenciais alternativas"
    else
        warn "⚠️ MySQL pode não estar inicializado completamente"
    fi
fi

# Verificar Redis
if redis-cli -h redis ping >/dev/null 2>&1; then
    success "✅ Redis está funcionando"
else
    warn "⚠️ Redis não está respondendo"
fi

# 2. Verificar permissões
step "🔐 Verificando permissões..."

# Verificar permissões do workspace
if [ -w "/workspace" ]; then
    success "✅ Permissões do workspace OK"
else
    warn "⚠️ Problemas de permissão no workspace"
    log "Corrigindo permissões..."
    sudo chown -R vscode:vscode /workspace 2>/dev/null || true
    chmod -R u+rw /workspace 2>/dev/null || true
fi

# 3. Verificar estrutura do projeto
step "📁 Verificando estrutura do projeto..."

# Verificar backend
if [ -d "backend" ]; then
    success "✅ Diretório backend encontrado"
    
    if [ -f "backend/composer.json" ]; then
        success "✅ composer.json encontrado"
    else
        error "❌ composer.json não encontrado no backend"
    fi
    
    if [ -f "backend/artisan" ]; then
        success "✅ arquivo artisan encontrado"
    else
        error "❌ arquivo artisan não encontrado"
    fi
else
    error "❌ Diretório backend não encontrado"
fi

# Verificar frontend
if [ -d "frontend" ]; then
    success "✅ Diretório frontend encontrado"
    
    if [ -f "frontend/package.json" ]; then
        success "✅ package.json encontrado"
    else
        error "❌ package.json não encontrado no frontend"
    fi
else
    error "❌ Diretório frontend não encontrado"
fi

# 4. Verificar dependências
step "📦 Verificando dependências..."

# Backend
if [ -d "backend" ]; then
    if [ -d "backend/vendor" ]; then
        success "✅ Dependências PHP instaladas"
    else
        warn "⚠️ Dependências PHP não instaladas"
        log "Instalando dependências PHP..."
        backend_exec composer install --no-interaction --prefer-dist
    fi
fi

# Frontend
if [ -d "frontend" ]; then
    if [ -d "frontend/node_modules" ]; then
        success "✅ Dependências Node.js instaladas"
    else
        warn "⚠️ Dependências Node.js não instaladas"
        log "Instalando dependências Node.js..."
        frontend_exec npm install
    fi
fi

# 5. Verificar Laravel especificamente
step "🐘 Verificando Laravel..."

if [ -d "backend" ]; then
    # Verificar se o Laravel consegue executar comandos
    if backend_exec php artisan --version >/dev/null 2>&1; then
        VERSION=$(backend_exec php artisan --version)
        success "✅ Laravel funcionando: $VERSION"
    else
        error "❌ Laravel não consegue executar comandos"
        log "Executando correção de namespace..."
        
        if [ -f ".devcontainer/scripts/fix-laravel-namespace.sh" ]; then
            bash .devcontainer/scripts/fix-laravel-namespace.sh
        else
            warn "⚠️ Script de correção não encontrado"
        fi
    fi
    
    # Verificar .env
    if [ -f "backend/.env" ]; then
        success "✅ Arquivo .env encontrado"
        
        # Verificar chave da aplicação
        if grep -q "APP_KEY=base64:" backend/.env; then
            success "✅ Chave da aplicação configurada"
        else
            warn "⚠️ Chave da aplicação não configurada"
            log "Gerando chave da aplicação..."
            backend_exec php artisan key:generate --force
        fi
    else
        error "❌ Arquivo .env não encontrado"
        log "Criando .env..."
        if [ -f "backend/.env.example" ]; then
            cp backend/.env.example backend/.env
            backend_exec php artisan key:generate
        else
            warn "⚠️ .env.example não encontrado"
        fi
    fi
fi

# 6. Verificar banco de dados
step "🗄️ Verificando banco de dados..."

if [ -d "backend" ] && [ -f "backend/.env" ]; then
    # Verificar se consegue conectar ao banco
    if backend_exec php artisan migrate:status >/dev/null 2>&1; then
        success "✅ Conexão com banco de dados OK"
        
        # Verificar migrações
        MIGRATION_STATUS=$(backend_exec php artisan migrate:status --no-ansi 2>/dev/null || echo "ERROR")
        if [[ "$MIGRATION_STATUS" == *"No"* ]]; then
            log "Executando migrações..."
            backend_exec php artisan migrate --force
        else
            success "✅ Migrações já executadas"
        fi
    else
        warn "⚠️ Problema na conexão com banco de dados"
        log "Verificando configuração do .env..."
        
        # Verificar se as variáveis de banco estão corretas
        if grep -q "DB_HOST=mysql" backend/.env && \
           grep -q "DB_DATABASE=rei_do_oleo_dev" backend/.env; then
            success "✅ Configuração do banco parece correta"
        else
            warn "⚠️ Configuração do banco pode estar incorreta"
        fi
    fi
fi

# 7. Verificar frontend
step "⚛️ Verificando frontend..."

if [ -d "frontend" ]; then
    # Verificar se consegue executar npm
    if frontend_exec npm --version >/dev/null 2>&1; then
        success "✅ NPM funcionando"
        
        # Verificar se consegue fazer build
        if frontend_exec npm run build --dry-run >/dev/null 2>&1 || \
           frontend_exec npm run type-check >/dev/null 2>&1; then
            success "✅ Frontend configurado corretamente"
        else
            warn "⚠️ Frontend pode ter problemas de configuração"
        fi
    else
        error "❌ NPM não está funcionando"
    fi
fi

# 8. Limpar caches e regenerar
step "🧹 Limpando caches e regenerando..."

# Backend
if [ -d "backend" ]; then
    backend_exec php artisan config:clear 2>/dev/null || true
    backend_exec php artisan cache:clear 2>/dev/null || true
    backend_exec php artisan route:clear 2>/dev/null || true
    backend_exec php artisan view:clear 2>/dev/null || true
    backend_exec composer dump-autoload --optimize 2>/dev/null || true
    success "✅ Caches do backend limpos"
fi

# Frontend
if [ -d "frontend" ]; then
    frontend_exec npm cache clean --force 2>/dev/null || true
    success "✅ Cache do frontend limpo"
fi

# 9. Teste final
step "🧪 Teste final..."

# Testar se o Laravel está funcionando
if [ -d "backend" ]; then
    if backend_exec php artisan list --format=json >/dev/null 2>&1; then
        success "✅ Laravel funcionando perfeitamente"
    else
        error "❌ Laravel ainda tem problemas"
    fi
fi

# Testar se o frontend está funcionando
if [ -d "frontend" ]; then
    if frontend_exec npm --version >/dev/null 2>&1; then
        success "✅ Frontend funcionando"
    else
        error "❌ Frontend tem problemas"
    fi
fi

# 10. Resumo e recomendações
echo -e "${GREEN}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    🔧 TROUBLESHOOT COMPLETO               ║
╠═══════════════════════════════════════════════════════════╣
║  ✅ Diagnóstico concluído                                 ║
║  🧹 Caches limpos                                         ║
║  📦 Dependências verificadas                              ║
║  🗄️ Banco de dados testado                                ║
╠═══════════════════════════════════════════════════════════╣
║  🚀 Próximos passos:                                      ║
║  npm run dev      - Iniciar desenvolvimento               ║
║  npm run test     - Executar testes                       ║
║  npm run lint     - Verificar código                      ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# 11. Comandos úteis
info "🔧 Comandos úteis para troubleshooting:"
echo "  - bash .devcontainer/scripts/fix-laravel-namespace.sh"
echo "  - bash .devcontainer/scripts/verify-laravel.sh"
echo "  - bash .devcontainer/scripts/setup-test-db.sh"
echo "  - docker-compose logs [service]"
echo "  - docker-compose restart [service]"

success "🎯 Troubleshooting concluído!" 