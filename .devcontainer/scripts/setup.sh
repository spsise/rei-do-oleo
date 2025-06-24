#!/bin/bash

# 🚀 Setup Completo - Sistema Rei do Óleo MVP
# Script executado automaticamente na criação do Dev Container

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
log() { echo -e "${GREEN}[SETUP]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${PURPLE}[SUCCESS]${NC} $1"; }
step() { echo -e "${CYAN}[STEP]${NC} $1"; }

# Banner de início
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                🛠️  REI DO ÓLEO - DEV SETUP                ║
║           Configuração Completa do Ambiente               ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

cd /workspace

# 1. Aguardar serviços estarem prontos
step "🔄 Aguardando serviços estarem prontos..."
for i in {1..60}; do
    if mysqladmin ping -h mysql -u root -proot123 --silent 2>/dev/null && \
       redis-cli -h redis ping >/dev/null 2>&1; then
        success "✅ Serviços MySQL e Redis prontos!"
        break
    fi
    if [ $i -eq 60 ]; then
        warn "⚠️ Timeout aguardando serviços. Continuando..."
        break
    fi
    echo -n "."
    sleep 1
done
echo

# 2. Configurar Backend Laravel
step "📦 Configurando Backend Laravel..."
if [ ! -d "backend" ]; then
    log "Criando novo projeto Laravel..."
    composer create-project laravel/laravel:^11.0 backend --prefer-dist
    cd backend

    # Instalar dependências específicas
    log "📚 Instalando dependências Laravel..."
    composer require laravel/sanctum laravel/horizon spatie/laravel-permission
    composer require spatie/laravel-query-builder spatie/laravel-backup
    composer require barryvdh/laravel-cors league/flysystem-aws-s3-v3

    # Dependências de desenvolvimento
    composer require --dev laravel/telescope barryvdh/laravel-debugbar
    composer require --dev phpunit/phpunit mockery/mockery fakerphp/faker
    composer require --dev friendsofphp/php-cs-fixer phpstan/phpstan
    composer require --dev laravel/sail pestphp/pest

    cd /workspace
else
    log "Backend Laravel existente encontrado"
    cd backend && composer install && cd /workspace
fi

# 3. Configurar Frontend React
step "⚛️ Configurando Frontend React..."
if [ ! -d "frontend" ]; then
    log "Criando projeto React com Vite..."
    npm create vite@latest frontend -- --template react-ts
    cd frontend

    # Instalar dependências
    log "📚 Instalando dependências React..."
    npm install
    npm install @tanstack/react-query react-router-dom axios
    npm install @headlessui/react @heroicons/react
    npm install tailwindcss @tailwindcss/forms @tailwindcss/typography
    npm install react-hook-form @hookform/resolvers yup
    npm install date-fns react-hot-toast @vite-pwa/vite-plugin
    npm install workbox-precaching workbox-routing workbox-strategies

    # Dependências de desenvolvimento
    npm install --dev @types/react @types/react-dom
    npm install --dev @typescript-eslint/eslint-plugin @typescript-eslint/parser
    npm install --dev eslint eslint-plugin-react-hooks eslint-plugin-react-refresh
    npm install --dev prettier @testing-library/react @testing-library/jest-dom
    npm install --dev @testing-library/user-event vitest jsdom autoprefixer postcss

    cd /workspace
else
    log "Frontend React existente encontrado"
    cd frontend && npm install && cd /workspace
fi

# 4. Configurar variáveis de ambiente
step "🔧 Configurando variáveis de ambiente..."

# Backend .env
if [ ! -f "backend/.env" ]; then
    log "Criando .env do backend..."
    cp backend/.env.example backend/.env

    # Configurações do banco
    sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' backend/.env
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' backend/.env
    sed -i 's/DB_PORT=3306/DB_PORT=3306/' backend/.env
    sed -i 's/DB_DATABASE=laravel/DB_DATABASE=rei_do_oleo_dev/' backend/.env
    sed -i 's/DB_USERNAME=root/DB_USERNAME=rei_do_oleo/' backend/.env
    sed -i 's/DB_PASSWORD=/DB_PASSWORD=secret123/' backend/.env

    # Configurações Redis
    echo "" >> backend/.env
    echo "# Redis Configuration" >> backend/.env
    echo "REDIS_HOST=redis" >> backend/.env
    echo "REDIS_PASSWORD=null" >> backend/.env
    echo "REDIS_PORT=6379" >> backend/.env

    # Configurações de Mail
    echo "" >> backend/.env
    echo "# Mail Configuration" >> backend/.env
    echo "MAIL_MAILER=smtp" >> backend/.env
    echo "MAIL_HOST=mailhog" >> backend/.env
    echo "MAIL_PORT=1025" >> backend/.env
    echo "MAIL_USERNAME=null" >> backend/.env
    echo "MAIL_PASSWORD=null" >> backend/.env
    echo "MAIL_ENCRYPTION=null" >> backend/.env

    # Configurações MinIO
    echo "" >> backend/.env
    echo "# MinIO S3 Configuration" >> backend/.env
    echo "FILESYSTEM_DISK=s3" >> backend/.env
    echo "AWS_ACCESS_KEY_ID=reidooleo" >> backend/.env
    echo "AWS_SECRET_ACCESS_KEY=secret123456" >> backend/.env
    echo "AWS_DEFAULT_REGION=us-east-1" >> backend/.env
    echo "AWS_BUCKET=rei-do-oleo-storage" >> backend/.env
    echo "AWS_ENDPOINT=http://minio:9000" >> backend/.env
    echo "AWS_USE_PATH_STYLE_ENDPOINT=true" >> backend/.env
fi

# Frontend .env
if [ ! -f "frontend/.env" ]; then
    log "Criando .env do frontend..."
    cat > frontend/.env << 'EOF'
# 🌐 Frontend Environment Variables
VITE_APP_NAME="Rei do Óleo"
VITE_API_URL=http://localhost:8000
VITE_APP_URL=http://localhost:3000

# 📱 PWA Configuration
VITE_PWA_NAME="Rei do Óleo"
VITE_PWA_SHORT_NAME="ReiÓleo"
VITE_PWA_DESCRIPTION="Sistema de Gestão para Troca de Óleo Automotivo"
VITE_PWA_THEME_COLOR="#1f2937"
VITE_PWA_BACKGROUND_COLOR="#ffffff"

# 🔧 Development
VITE_DEV_MODE=true
VITE_DEV_TOOLS=true
EOF
fi

# 5. Configurar Laravel
step "🎯 Configurando Laravel..."
cd backend

# Gerar chave da aplicação
log "🔑 Gerando chave da aplicação..."
php artisan key:generate

# Aguardar banco estar pronto e executar migrações
log "🗄️ Configurando banco de dados..."
for i in {1..30}; do
    if php artisan migrate:status &>/dev/null; then
        log "✅ Conexão com banco estabelecida!"
        break
    fi
    if [ $i -eq 30 ]; then
        warn "⚠️ Não foi possível conectar ao banco"
        cd /workspace
        exit 0
    fi
    sleep 2
done

# Executar migrações
log "🔄 Executando migrações..."
php artisan migrate

# Publicar configurações
log "📄 Publicando configurações..."
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --quiet
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --quiet

# Executar migrações novamente
php artisan migrate

# Criar storage link
php artisan storage:link

# Limpar caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

cd /workspace

# 6. Configurar ferramentas de qualidade
step "🔍 Configurando ferramentas de qualidade..."

# PHP CS Fixer
if [ ! -f ".php-cs-fixer.php" ]; then
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
fi

# PHPStan
if [ ! -f "phpstan.neon" ]; then
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
EOF
fi

# ESLint Frontend
if [ ! -f "frontend/.eslintrc.js" ] && [ -d "frontend" ]; then
    cat > frontend/.eslintrc.js << 'EOF'
module.exports = {
  root: true,
  env: { browser: true, es2020: true },
  extends: [
    'eslint:recommended',
    '@typescript-eslint/recommended',
    'plugin:react-hooks/recommended',
  ],
  ignorePatterns: ['dist', '.eslintrc.js'],
  parser: '@typescript-eslint/parser',
  plugins: ['react-refresh'],
  rules: {
    'react-refresh/only-export-components': [
      'warn',
      { allowConstantExport: true },
    ],
  },
}
EOF
fi

# Prettier
if [ ! -f ".prettierrc" ]; then
    cat > .prettierrc << 'EOF'
{
  "semi": true,
  "trailingComma": "es5",
  "singleQuote": true,
  "printWidth": 80,
  "tabWidth": 2,
  "useTabs": false
}
EOF
fi

# 7. Configurar Git Hooks com Husky
step "🔗 Configurando Git Hooks..."
if [ ! -f "package.json" ]; then
    cat > package.json << 'EOF'
{
  "name": "rei-do-oleo",
  "version": "1.0.0",
  "description": "Sistema de Gestão para Troca de Óleo Automotivo",
  "private": true,
  "workspaces": ["frontend"],
  "scripts": {
    "dev": "concurrently \"npm run dev:backend\" \"npm run dev:frontend\"",
    "dev:backend": "cd backend && php artisan serve --host=0.0.0.0 --port=8000",
    "dev:frontend": "cd frontend && npm run dev -- --host 0.0.0.0 --port 3000",
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
    "setup": "bash .devcontainer/scripts/setup.sh",
    "start": "bash .devcontainer/scripts/start.sh",
    "reset": "bash .devcontainer/scripts/reset.sh",
    "backup": "bash .devcontainer/scripts/backup.sh"
  },
  "devDependencies": {
    "concurrently": "^8.2.2",
    "husky": "^8.0.3",
    "lint-staged": "^15.2.0"
  }
}
EOF
    npm install
fi

# Instalar e configurar Husky
if [ ! -d ".husky" ]; then
    npx husky install
    npx husky add .husky/pre-commit "npx lint-staged"

    # Configurar lint-staged
    cat > .lintstagedrc.json << 'EOF'
{
  "backend/**/*.php": [
    "./vendor/bin/php-cs-fixer fix",
    "./vendor/bin/phpstan analyse --no-progress"
  ],
  "frontend/**/*.{js,jsx,ts,tsx}": [
    "cd frontend && npm run lint:fix"
  ]
}
EOF
fi

# 8. Criar bucket no MinIO
step "📦 Configurando MinIO Storage..."
sleep 5  # Aguardar MinIO estar pronto
if command -v mc >/dev/null 2>&1; then
    mc alias set minio http://minio:9000 reidooleo secret123456 >/dev/null 2>&1 || true
    mc mb minio/rei-do-oleo-storage >/dev/null 2>&1 || true
    mc policy set public minio/rei-do-oleo-storage >/dev/null 2>&1 || true
fi

# 9. Finalização
success "🎉 Setup completo realizado com sucesso!"
echo -e "${GREEN}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    ✅ AMBIENTE PRONTO!                    ║
╠═══════════════════════════════════════════════════════════╣
║  🚀 Laravel API: http://localhost:8000                   ║
║  ⚛️ React Frontend: http://localhost:3000                ║
║  💾 phpMyAdmin: http://localhost:8080                    ║
║  🔍 Redis Commander: http://localhost:6380               ║
║  📧 MailHog: http://localhost:8025                       ║
║  📦 MinIO Console: http://localhost:9001                 ║
╠═══════════════════════════════════════════════════════════╣
║  Para iniciar desenvolvimento:                           ║
║  npm run dev                                             ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

info "🎯 Ambiente de desenvolvimento totalmente configurado!"

# 10. Configurar SSH para Git
step "🔐 Configurando SSH para Git..."
bash /workspace/.devcontainer/scripts/ssh-setup.sh

info "🔧 Execute 'npm run dev' para iniciar os serviços"
