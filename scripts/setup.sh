#!/bin/bash

# 🚀 Script de Setup - Sistema Rei do Óleo MVP
# Este script configura completamente o ambiente de desenvolvimento

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Função para logging
log() {
    echo -e "${GREEN}[SETUP]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

success() {
    echo -e "${PURPLE}[SUCCESS]${NC} $1"
}

# Função para verificar se comando existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Função para verificar se arquivo/diretório existe
file_exists() {
    [ -f "$1" ] || [ -d "$1" ]
}

# Função para executar comandos no backend
backend_exec() {
    (cd backend && "$@")
}

# Função para executar comandos no frontend
frontend_exec() {
    (cd frontend && "$@")
}

# Banner
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    🛠️  REI DO ÓLEO MVP                    ║
║                   Setup de Desenvolvimento                ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Verificar pré-requisitos
log "🔍 Verificando pré-requisitos..."

if ! command_exists "composer"; then
    error "Composer não encontrado. Instale o Composer primeiro."
    exit 1
fi

if ! command_exists "node"; then
    error "Node.js não encontrado. Instale o Node.js primeiro."
    exit 1
fi

if ! command_exists "npm"; then
    error "NPM não encontrado. Instale o NPM primeiro."
    exit 1
fi

# Verificar se estamos no diretório correto
if [ ! -f "docker-compose.yml" ]; then
    error "Execute este script na raiz do projeto!"
    exit 1
fi

log "✅ Pré-requisitos verificados com sucesso"

# 1. Configurar Backend Laravel
log "📦 Configurando Backend Laravel..."
if [ ! -d "backend" ]; then
    log "Criando projeto Laravel..."
    composer create-project laravel/laravel:^11.0 backend --prefer-dist --no-interaction

    # Instalar dependências específicas do projeto
    log "📚 Instalando dependências Laravel..."
    backend_exec composer require laravel/sanctum laravel/horizon spatie/laravel-permission
    backend_exec composer require spatie/laravel-query-builder spatie/laravel-backup
    backend_exec composer require barryvdh/laravel-cors league/flysystem-aws-s3-v3

    # Dependências de desenvolvimento
    backend_exec composer require --dev laravel/telescope barryvdh/laravel-debugbar
    backend_exec composer require --dev phpunit/phpunit mockery/mockery fakerphp/faker
    backend_exec composer require --dev friendsofphp/php-cs-fixer phpstan/phpstan
    backend_exec composer require --dev pestphp/pest

    success "✅ Projeto Laravel criado com sucesso"
else
    log "Backend Laravel já existe, verificando dependências..."
    backend_exec composer install --no-interaction
    success "✅ Dependências do backend atualizadas"
fi

# 2. Configurar Frontend React
log "⚛️ Configurando Frontend React..."
if [ ! -d "frontend" ]; then
    log "Criando projeto React com Vite..."
    npm create vite@latest frontend -- --template react-ts

    # Instalar dependências específicas do projeto
    log "📚 Instalando dependências React..."
    frontend_exec npm install
    frontend_exec npm install @tanstack/react-query react-router-dom axios
    frontend_exec npm install @headlessui/react @heroicons/react
    frontend_exec npm install tailwindcss @tailwindcss/forms @tailwindcss/typography
    frontend_exec npm install react-hook-form @hookform/resolvers yup
    frontend_exec npm install date-fns react-hot-toast @vite-pwa/vite-plugin
    frontend_exec npm install workbox-webpack-plugin

    # Dependências de desenvolvimento
    frontend_exec npm install --save-dev @types/react @types/react-dom
    frontend_exec npm install --save-dev @typescript-eslint/eslint-plugin @typescript-eslint/parser
    frontend_exec npm install --save-dev eslint eslint-plugin-react-hooks eslint-plugin-react-refresh
    frontend_exec npm install --save-dev prettier @testing-library/react @testing-library/jest-dom
    frontend_exec npm install --save-dev @testing-library/user-event vitest jsdom autoprefixer postcss

    success "✅ Projeto React criado com sucesso"
else
    log "Frontend React já existe, verificando dependências..."
    frontend_exec npm install
    success "✅ Dependências do frontend atualizadas"
fi

# 3. Configurar variáveis de ambiente
log "🔧 Configurando variáveis de ambiente..."

# Backend .env
if [ ! -f "backend/.env" ]; then
    log "Criando arquivo .env do backend..."
    cp backend/.env.example backend/.env

    # Configurar database
    sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' backend/.env
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' backend/.env
    sed -i 's/DB_PORT=3306/DB_PORT=3306/' backend/.env
    sed -i 's/DB_DATABASE=laravel/DB_DATABASE=reidooleo_dev/' backend/.env
    sed -i 's/DB_USERNAME=root/DB_USERNAME=reidooleo/' backend/.env
    sed -i 's/DB_PASSWORD=/DB_PASSWORD=reidooleo123/' backend/.env

    # Configurar Redis
    echo "" >> backend/.env
    echo "# Redis Configuration" >> backend/.env
    echo "REDIS_HOST=redis" >> backend/.env
    echo "REDIS_PASSWORD=null" >> backend/.env
    echo "REDIS_PORT=6379" >> backend/.env

    # Configurar Mail (MailHog)
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

    # Configurar Filesystem (MinIO)
    echo "" >> backend/.env
    echo "# Filesystem Configuration (MinIO)" >> backend/.env
    echo "FILESYSTEM_DISK=s3" >> backend/.env
    echo "AWS_ACCESS_KEY_ID=reidooleo" >> backend/.env
    echo "AWS_SECRET_ACCESS_KEY=reidooleo123" >> backend/.env
    echo "AWS_DEFAULT_REGION=us-east-1" >> backend/.env
    echo "AWS_BUCKET=reidooleo-storage" >> backend/.env
    echo "AWS_ENDPOINT=http://minio:9000" >> backend/.env
    echo "AWS_USE_PATH_STYLE_ENDPOINT=true" >> backend/.env

    # Configurações específicas da aplicação
    echo "" >> backend/.env
    echo "# Application Specific" >> backend/.env
    echo "APP_URL=http://api.reidooleo.local" >> backend/.env
    echo "FRONTEND_URL=http://frontend.reidooleo.local" >> backend/.env
    echo "SANCTUM_STATEFUL_DOMAINS=frontend.reidooleo.local,localhost:5173" >> backend/.env
    echo "SESSION_DOMAIN=.reidooleo.local" >> backend/.env

    success "✅ Arquivo .env do backend configurado!"
else
    info "ℹ️ Arquivo .env do backend já existe"
fi

# Frontend .env
if [ ! -f "frontend/.env" ]; then
    log "Criando arquivo .env do frontend..."
    cat > frontend/.env << EOF
# Frontend Environment Variables
VITE_APP_NAME="Rei do Óleo"
VITE_API_URL=http://api.reidooleo.local
VITE_APP_URL=http://frontend.reidooleo.local

# PWA Configuration
VITE_PWA_NAME="Rei do Óleo"
VITE_PWA_SHORT_NAME="ReiÓleo"
VITE_PWA_DESCRIPTION="Sistema de Gestão para Troca de Óleo Automotivo"
VITE_PWA_THEME_COLOR="#1f2937"
VITE_PWA_BACKGROUND_COLOR="#ffffff"

# Development
VITE_DEV_MODE=true
EOF
    success "✅ Arquivo .env do frontend configurado!"
else
    info "ℹ️ Arquivo .env do frontend já existe"
fi

# 4. Configurar Laravel
log "🎯 Configurando Laravel..."

# Gerar chave da aplicação se não existir
if ! grep -q "APP_KEY=" backend/.env || [ -z "$(grep APP_KEY= backend/.env | cut -d'=' -f2)" ]; then
    log "🔑 Gerando chave da aplicação..."
    backend_exec php artisan key:generate --force
    success "✅ Chave da aplicação gerada"
else
    info "ℹ️ Chave da aplicação já existe"
fi

# Aguardar banco de dados estar disponível
log "🗄️ Verificando conexão com banco de dados..."
DB_AVAILABLE=false
for i in {1..30}; do
    if backend_exec php artisan migrate:status &> /dev/null; then
        DB_AVAILABLE=true
        success "✅ Conexão com banco estabelecida!"
        break
    fi
    if [ $i -eq 30 ]; then
        warn "⚠️ Não foi possível conectar ao banco. Execute as migrações manualmente após o banco estar disponível."
        break
    fi
    echo -n "."
    sleep 2
done
echo

# Verificar se já existem migrações executadas
if [ "$DB_AVAILABLE" = true ]; then
    MIGRATION_COUNT=$(backend_exec php artisan migrate:status --pending 2>/dev/null | grep -c "Pending" || echo "0")

    if [ "$MIGRATION_COUNT" -gt 0 ] || ! backend_exec php artisan migrate:status &>/dev/null; then
        log "🔄 Executando migrações pendentes..."
        backend_exec php artisan migrate --force
        success "✅ Migrações executadas com sucesso"
    else
        info "ℹ️ Todas as migrações já foram executadas"
    fi

    # Verificar e publicar configurações dos pacotes apenas se necessário
    log "📄 Verificando configurações dos pacotes..."

    # Sanctum
    if [ ! -f "backend/config/sanctum.php" ]; then
        log "Publicando configurações do Sanctum..."
        backend_exec php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider" --force
    else
        info "ℹ️ Configurações do Sanctum já publicadas"
    fi

    # Spatie Permission
    if [ ! -f "backend/config/permission.php" ]; then
        log "Publicando configurações do Spatie Permission..."
        backend_exec php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider" --force
    else
        info "ℹ️ Configurações do Spatie Permission já publicadas"
    fi

    # Laravel Backup
    if [ ! -f "backend/config/backup.php" ]; then
        log "Publicando configurações do Laravel Backup..."
        backend_exec php artisan vendor:publish --tag="laravel-backup-config" --force
    else
        info "ℹ️ Configurações do Laravel Backup já publicadas"
    fi

    # Executar migrações novamente se houver novas migrações dos pacotes
    NEW_MIGRATION_COUNT=$(backend_exec php artisan migrate:status --pending 2>/dev/null | grep -c "Pending" || echo "0")
    if [ "$NEW_MIGRATION_COUNT" -gt 0 ]; then
        log "🔄 Executando novas migrações dos pacotes..."
        backend_exec php artisan migrate --force
    fi

    # Criar link de storage se não existir
    if [ ! -L "backend/public/storage" ]; then
        log "🔗 Criando link simbólico do storage..."
        backend_exec php artisan storage:link
        success "✅ Link do storage criado"
    else
        info "ℹ️ Link do storage já existe"
    fi

    # Limpar caches
    log "🧹 Limpando caches..."
    backend_exec php artisan config:clear
    backend_exec php artisan route:clear
    backend_exec php artisan view:clear
    backend_exec php artisan cache:clear
    success "✅ Caches limpos"
fi

# 5. Configurar ferramentas de qualidade de código
log "🔍 Configurando ferramentas de qualidade de código..."

# PHP CS Fixer
if [ ! -f ".php-cs-fixer.php" ]; then
    log "Configurando PHP CS Fixer..."
    cat > .php-cs-fixer.php << 'EOF'
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(['backend/app', 'backend/config', 'backend/database', 'backend/routes', 'backend/tests'])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'no_unused_imports' => true,
    'not_operator_with_successor_space' => true,
    'trailing_comma_in_multiline' => true,
    'phpdoc_scalar' => true,
    'unary_operator_spaces' => true,
    'binary_operator_spaces' => true,
    'blank_line_before_statement' => [
        'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
    ],
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_var_without_name' => true,
])->setFinder($finder);
EOF
    success "✅ PHP CS Fixer configurado"
else
    info "ℹ️ PHP CS Fixer já configurado"
fi

# PHPStan
if [ ! -f "phpstan.neon" ]; then
    log "Configurando PHPStan..."
    cat > phpstan.neon << 'EOF'
parameters:
    level: 5
    paths:
        - backend/app
        - backend/config
        - backend/database
        - backend/routes
    excludes_analyse:
        - backend/app/Console/Kernel.php
        - backend/app/Exceptions/Handler.php
    checkMissingIterableValueType: false
    checkGenericClassInNonGenericObjectType: false
    bootstrapFiles:
        - backend/vendor/autoload.php
EOF
    success "✅ PHPStan configurado"
else
    info "ℹ️ PHPStan já configurado"
fi

# 6. Configurar Git Hooks e scripts
log "🔗 Configurando Git Hooks e scripts..."
if [ ! -f "package.json" ]; then
    log "Criando package.json raiz..."
    cat > package.json << EOF
{
  "name": "rei-do-oleo",
  "version": "1.0.0",
  "description": "Sistema de Gestão para Troca de Óleo Automotivo",
  "private": true,
  "workspaces": [
    "frontend"
  ],
  "scripts": {
    "dev": "concurrently \"npm run dev:backend\" \"npm run dev:frontend\"",
    "dev:backend": "cd backend && php artisan serve --host=0.0.0.0",
    "dev:frontend": "cd frontend && npm run dev",
    "build": "cd frontend && npm run build",
    "test": "concurrently \"npm run test:backend\" \"npm run test:frontend\"",
    "test:backend": "cd backend && php artisan test",
    "test:frontend": "cd frontend && npm test",
    "lint": "concurrently \"npm run lint:backend\" \"npm run lint:frontend\"",
    "lint:backend": "./vendor/bin/phpstan analyse",
    "lint:frontend": "cd frontend && npm run lint",
    "fix": "concurrently \"npm run fix:backend\" \"npm run fix:frontend\"",
    "fix:backend": "./vendor/bin/php-cs-fixer fix",
    "fix:frontend": "cd frontend && npm run lint:fix",
    "setup": "bash scripts/setup.sh",
    "start": "bash scripts/start.sh",
    "backup": "bash scripts/backup.sh"
  },
  "devDependencies": {
    "concurrently": "^8.2.2",
    "husky": "^8.0.3",
    "lint-staged": "^15.2.0"
  }
}
EOF
    npm install
    success "✅ Package.json criado e dependências instaladas"
else
    log "Atualizando dependências do package.json..."
    npm install
    info "ℹ️ Package.json já existe, dependências atualizadas"
fi

# Instalar e configurar Husky
if [ ! -d ".husky" ]; then
    log "Configurando Husky para Git Hooks..."
    npx husky install
    npx husky add .husky/pre-commit "npx lint-staged"

    # Configurar lint-staged
    cat > .lintstagedrc.json << EOF
{
  "backend/**/*.php": [
    "./vendor/bin/php-cs-fixer fix",
    "./vendor/bin/phpstan analyse --no-progress"
  ],
  "frontend/**/*.{js,jsx,ts,tsx}": [
    "cd frontend && npm run lint:fix",
    "cd frontend && npm run type-check"
  ]
}
EOF
    success "✅ Husky configurado"
else
    info "ℹ️ Husky já configurado"
fi

# 7. Configurar hosts locais (opcional)
log "🌐 Configurando hosts locais..."
if command_exists "sudo"; then
    if ! grep -q "frontend.reidooleo.local" /etc/hosts 2>/dev/null; then
        info "Adicionando entradas ao /etc/hosts (pode solicitar senha)..."
        echo "127.0.0.1 frontend.reidooleo.local" | sudo tee -a /etc/hosts >/dev/null
        echo "127.0.0.1 api.reidooleo.local" | sudo tee -a /etc/hosts >/dev/null
        success "✅ Hosts locais configurados"
    else
        info "ℹ️ Hosts locais já configurados"
    fi
else
    warn "⚠️ Adicione manualmente ao seu /etc/hosts:"
    warn "127.0.0.1 frontend.reidooleo.local"
    warn "127.0.0.1 api.reidooleo.local"
fi

# 8. Finalização
success "🎉 Setup concluído com sucesso!"
echo -e "${GREEN}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    🎉 SETUP CONCLUÍDO!                   ║
╠═══════════════════════════════════════════════════════════╣
║  Para iniciar o desenvolvimento, execute:                ║
║  npm run dev                                             ║
║                                                           ║
║  URLs de acesso:                                         ║
║  🌐 Frontend: http://frontend.reidooleo.local            ║
║  🔧 API: http://api.reidooleo.local                      ║
║  📧 MailHog: http://localhost:8025                       ║
║  🗄️ Adminer: http://localhost:8081                       ║
║  📊 Redis: http://localhost:8082                         ║
║  📦 MinIO: http://localhost:9001                         ║
╠═══════════════════════════════════════════════════════════╣
║  Scripts disponíveis:                                    ║
║  npm run dev      - Iniciar desenvolvimento             ║
║  npm run test     - Executar todos os testes            ║
║  npm run lint     - Executar análise de código          ║
║  npm run fix      - Corrigir problemas de formatação    ║
║  npm run build    - Build de produção do frontend       ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

info "🚀 Execute 'npm run dev' para iniciar os serviços de desenvolvimento!"
