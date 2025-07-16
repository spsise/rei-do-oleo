#!/bin/bash

# 🚀 Setup de Variáveis de Ambiente - Rei do Óleo
# Script para configurar automaticamente arquivos .env em novos ambientes

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
log() { echo -e "${GREEN}[ENV-SETUP]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${PURPLE}[SUCCESS]${NC} $1"; }
step() { echo -e "${CYAN}[STEP]${NC} $1"; }

# Banner
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║           🔧 SETUP DE VARIÁVEIS DE AMBIENTE               ║
║              Rei do Óleo - Auto Configuration             ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

cd /workspace

# ==================================================
# BACKEND ENVIRONMENT SETUP
# ==================================================
step "🐘 Configurando ambiente do Backend (Laravel)..."

# Verificar se .env.example existe
if [ ! -f "backend/.env.example" ]; then
    error "❌ Arquivo backend/.env.example não encontrado!"
    exit 1
fi

# Criar .env do backend se não existir
if [ ! -f "backend/.env" ]; then
    log "📝 Criando backend/.env a partir do .env.example..."
    cp backend/.env.example backend/.env

    # Configurações específicas para desenvolvimento
    log "🔧 Aplicando configurações de desenvolvimento..."

    # Database Configuration
    sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' backend/.env
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' backend/.env
    sed -i 's/DB_PORT=3306/DB_PORT=3306/' backend/.env
    sed -i 's/DB_DATABASE=laravel/DB_DATABASE=rei_do_oleo_dev/' backend/.env
    sed -i 's/DB_USERNAME=root/DB_USERNAME=rei_do_oleo/' backend/.env
    sed -i 's/DB_PASSWORD=/DB_PASSWORD=secret123/' backend/.env

    # Redis Configuration
    echo "" >> backend/.env
    echo "# Redis Configuration" >> backend/.env
    echo "REDIS_HOST=redis" >> backend/.env
    echo "REDIS_PASSWORD=null" >> backend/.env
    echo "REDIS_PORT=6379" >> backend/.env

    # Mail Configuration (MailHog)
    echo "" >> backend/.env
    echo "# Mail Configuration (MailHog)" >> backend/.env
    echo "MAIL_MAILER=smtp" >> backend/.env
    echo "MAIL_HOST=mailhog" >> backend/.env
    echo "MAIL_PORT=1025" >> backend/.env
    echo "MAIL_USERNAME=null" >> backend/.env
    echo "MAIL_PASSWORD=null" >> backend/.env
    echo "MAIL_ENCRYPTION=null" >> backend/.env
    echo "MAIL_FROM_ADDRESS=\"noreply@reidooleo.com\"" >> backend/.env
    echo "MAIL_FROM_NAME=\"\${APP_NAME}\"" >> backend/.env

    # MinIO S3 Configuration
    echo "" >> backend/.env
    echo "# MinIO S3 Configuration" >> backend/.env
    echo "FILESYSTEM_DISK=s3" >> backend/.env
    echo "AWS_ACCESS_KEY_ID=reidooleo" >> backend/.env
    echo "AWS_SECRET_ACCESS_KEY=secret123456" >> backend/.env
    echo "AWS_DEFAULT_REGION=us-east-1" >> backend/.env
    echo "AWS_BUCKET=rei-do-oleo-storage" >> backend/.env
    echo "AWS_ENDPOINT=http://minio:9000" >> backend/.env
    echo "AWS_USE_PATH_STYLE_ENDPOINT=true" >> backend/.env

    success "✅ backend/.env criado e configurado"
else
    info "ℹ️ backend/.env já existe"
fi

# Criar .env.testing se não existir
if [ ! -f "backend/.env.testing" ]; then
    log "📝 Criando backend/.env.testing..."
    if [ -f "backend/.env.testing.example" ]; then
        cp backend/.env.testing.example backend/.env.testing
        log "🔧 Aplicando configurações de teste..."

        # Database Configuration para testes
        sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' backend/.env.testing
        sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' backend/.env.testing
        sed -i 's/DB_PORT=3306/DB_PORT=3306/' backend/.env.testing
        sed -i 's/DB_DATABASE=laravel/DB_DATABASE=rei_do_oleo_test/' backend/.env.testing
        sed -i 's/DB_USERNAME=root/DB_USERNAME=rei_do_oleo/' backend/.env.testing
        sed -i 's/DB_PASSWORD=/DB_PASSWORD=secret123/' backend/.env.testing

        success "✅ backend/.env.testing criado e configurado"
    else
        warn "⚠️ backend/.env.testing.example não encontrado, criando básico..."
        cat > backend/.env.testing << 'EOF'
APP_NAME="Rei do Óleo API - Testing"
APP_ENV=testing
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=rei_do_oleo_test
DB_USERNAME=rei_do_oleo
DB_PASSWORD=secret123

CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync

MAIL_MAILER=array
EOF
        success "✅ backend/.env.testing criado"
    fi
else
    info "ℹ️ backend/.env.testing já existe"
fi

# ==================================================
# FRONTEND ENVIRONMENT SETUP
# ==================================================
step "⚛️ Configurando ambiente do Frontend (React)..."

# Verificar se .env.example existe
if [ ! -f "frontend/.env.example" ]; then
    error "❌ Arquivo frontend/.env.example não encontrado!"
    exit 1
fi

# Criar .env do frontend se não existir
if [ ! -f "frontend/.env" ]; then
    log "📝 Criando frontend/.env a partir do .env.example..."
    cp frontend/.env.example frontend/.env
    success "✅ frontend/.env criado"
else
    info "ℹ️ frontend/.env já existe"
fi

# ==================================================
# VERIFICAÇÃO E VALIDAÇÃO
# ==================================================
step "🔍 Verificando configurações..."

# Verificar se os arquivos foram criados
if [ -f "backend/.env" ] && [ -f "frontend/.env" ]; then
    success "✅ Todos os arquivos de ambiente foram criados com sucesso!"

    # Mostrar resumo
    echo ""
    echo -e "${CYAN}📋 RESUMO DA CONFIGURAÇÃO:${NC}"
    echo -e "  🐘 Backend (.env): ${GREEN}✅ Criado${NC}"
    echo -e "  🐘 Backend (.env.testing): ${GREEN}✅ Criado${NC}"
    echo -e "  ⚛️ Frontend (.env): ${GREEN}✅ Criado${NC}"
    echo ""
    echo -e "${YELLOW}⚠️  PRÓXIMOS PASSOS:${NC}"
    echo -e "  1. Execute 'cd backend && php artisan key:generate'"
    echo -e "  2. Execute 'cd backend && php artisan migrate'"
    echo -e "  3. Execute 'cd frontend && npm install'"
    echo -e "  4. Execute 'cd frontend && npm run dev'"
    echo ""
else
    error "❌ Erro na criação dos arquivos de ambiente"
    exit 1
fi

# ==================================================
# CONFIGURAÇÕES ADICIONAIS
# ==================================================
step "⚙️ Configurações adicionais..."

# Gerar chave da aplicação Laravel se necessário
if [ -f "backend/.env" ] && [ -d "backend" ]; then
    cd backend

    # Verificar se APP_KEY está vazio
    if grep -q "APP_KEY=" .env && [ -z "$(grep APP_KEY= .env | cut -d'=' -f2)" ]; then
        log "🔑 Gerando chave da aplicação Laravel..."
        php artisan key:generate --force --quiet
        success "✅ Chave da aplicação gerada"
    else
        info "ℹ️ Chave da aplicação já existe"
    fi

    cd ..
fi

# Verificar se composer.json tem o script de setup
if [ -f "backend/composer.json" ]; then
    if ! grep -q "post-install-cmd" backend/composer.json; then
        log "📝 Adicionando script post-install-cmd ao composer.json..."
        # Adicionar script se não existir
        sed -i '/"scripts": {/a\        "post-install-cmd": ["@php -r \"file_exists(\".env\") || copy(\".env.example\", \".env\");\""],' backend/composer.json
        success "✅ Script post-install-cmd adicionado"
    fi
fi

# Verificar se package.json do frontend tem scripts de setup
if [ -f "frontend/package.json" ]; then
    if ! grep -q "setup:env" frontend/package.json; then
        log "📝 Adicionando script setup:env ao package.json..."
        # Adicionar script se não existir
        sed -i '/"scripts": {/a\        "setup:env": "cp .env.example .env",' frontend/package.json
        success "✅ Script setup:env adicionado"
    fi
fi

echo ""
echo -e "${GREEN}🎉 Setup de variáveis de ambiente concluído com sucesso!${NC}"
echo -e "${BLUE}💡 Dica: Execute este script sempre que clonar o projeto em um novo ambiente${NC}"
echo ""
