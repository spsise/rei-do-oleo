#!/bin/bash

# 🛠️ Setup Script para Ambiente Simplificado
# Este script configura o ambiente de desenvolvimento sem Redis, MailHog, etc.

set -e

echo "🚀 Configurando ambiente de desenvolvimento simplificado..."

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

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Verificar se estamos no diretório correto
if [ ! -f "/workspace/backend/artisan" ]; then
    error "Este script deve ser executado dentro do devcontainer"
    exit 1
fi

log "Configurando ambiente Laravel simplificado..."

# Navegar para o backend
cd /workspace/backend

# Copiar configuração simplificada se não existir .env
if [ ! -f ".env" ]; then
    log "Criando arquivo .env para ambiente simplificado..."
    cp /workspace/.devcontainer/env.simple.example .env
    
    # Gerar chave da aplicação
    log "Gerando chave da aplicação..."
    php artisan key:generate
else
    warning "Arquivo .env já existe. Verificando configurações..."
    
    # Verificar se as configurações estão corretas para ambiente simplificado
    if grep -q "CACHE_DRIVER=redis" .env; then
        log "Atualizando configurações para ambiente simplificado..."
        sed -i 's/CACHE_DRIVER=redis/CACHE_DRIVER=file/' .env
        sed -i 's/SESSION_DRIVER=redis/SESSION_DRIVER=file/' .env
        sed -i 's/QUEUE_CONNECTION=redis/QUEUE_CONNECTION=sync/' .env
        sed -i 's/MAIL_MAILER=smtp/MAIL_MAILER=log/' .env
    fi
fi

# Instalar dependências do Composer
log "Instalando dependências do Composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Limpar cache
log "Limpando cache do Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Executar migrações
log "Executando migrações do banco de dados..."
php artisan migrate --force

# Executar seeders se existirem
if [ -d "database/seeders" ] && [ "$(ls -A database/seeders)" ]; then
    log "Executando seeders..."
    php artisan db:seed --force
fi

# Configurar permissões de storage
log "Configurando permissões de storage..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Navegar para o frontend
cd /workspace/frontend

# Instalar dependências do NPM
log "Instalando dependências do Frontend..."
npm install

# Configurar frontend para ambiente simplificado
log "Configurando variáveis de ambiente do frontend..."
if [ ! -f ".env" ]; then
    cat > .env << EOF
VITE_APP_NAME="Rei do Óleo - Simple Dev"
VITE_API_URL=http://localhost:8000/api
VITE_APP_URL=http://localhost:3000
VITE_APP_ENV=development
EOF
fi

# Verificar se o build funciona
log "Verificando build do frontend..."
npm run type-check
npm run lint

success "✅ Ambiente simplificado configurado com sucesso!"
success "🚀 Backend Laravel: http://localhost:8000"
success "⚛️ Frontend React: http://localhost:3000"
success "🗄️ phpMyAdmin: http://localhost:8110"
success "📊 MySQL: localhost:3310"

echo ""
echo "📝 Comandos úteis:"
echo "  Backend: cd /workspace/backend && php artisan serve"
echo "  Frontend: cd /workspace/frontend && npm run dev"
echo "  Testes: cd /workspace/backend && php artisan test"
echo "  Migrações: cd /workspace/backend && php artisan migrate"
echo ""

warning "⚠️  Ambiente simplificado ativo:"
warning "  - Cache: File (sem Redis)"
warning "  - Sessões: File (sem Redis)"
warning "  - Queue: Síncrono (sem Redis)"
warning "  - Email: Log (sem MailHog)"
warning "  - Storage: Local (sem MinIO)"

