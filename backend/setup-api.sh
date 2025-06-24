#!/bin/bash

echo "🚀 Configurando Backend Laravel 12 - Rei do Óleo API"
echo "=================================================="

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para logs
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar se o PHP está instalado
if ! command -v php &> /dev/null; then
    log_error "PHP não está instalado. Instale PHP 8.2+ primeiro."
    exit 1
fi

# Verificar se o Composer está instalado
if ! command -v composer &> /dev/null; then
    log_error "Composer não está instalado. Instale o Composer primeiro."
    exit 1
fi

log_info "Instalando dependências do Composer..."
composer install --no-interaction

log_info "Configurando arquivo de ambiente..."
if [ ! -f .env ]; then
    cp .env.example .env
    log_info "Arquivo .env criado a partir do .env.example"
else
    log_warn "Arquivo .env já existe, pulando..."
fi

log_info "Gerando chave da aplicação..."
php artisan key:generate --no-interaction

log_info "Verificando configurações..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

log_info "Verificando estrutura de diretórios..."
mkdir -p storage/logs
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p bootstrap/cache

log_info "Configurando permissões..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo ""
echo "✅ Backend configurado com sucesso!"
echo ""
echo "📋 Próximos passos:"
echo "1. Configure as variáveis de ambiente no arquivo .env"
echo "2. Configure o banco de dados MySQL"
echo "3. Configure o Redis"
echo "4. Execute as migrations: php artisan migrate"
echo "5. Inicie o servidor: php artisan serve"
echo ""
echo "📚 Documentação: README_API.md"
echo "🌐 Health Check: http://localhost:8000/api/health"
echo ""
echo "🎯 Endpoints principais:"
echo "   - POST /api/v1/auth/register - Registro"
echo "   - POST /api/v1/auth/login - Login"
echo "   - GET  /api/v1/auth/me - Perfil do usuário"
echo "   - GET  /api/v1/users - Lista de usuários"
echo ""
echo "🔧 Para desenvolvimento:"
echo "   php artisan serve --host=0.0.0.0 --port=8000"
