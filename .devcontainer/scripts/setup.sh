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

# Função para verificar se comando existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Função para executar comandos no backend
backend_exec() {
    (cd /workspace/backend && "$@")
}

# Função para executar comandos no frontend
frontend_exec() {
    (cd /workspace/frontend && "$@")
}

# Função para verificar se comando existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Função para executar comandos no backend
backend_exec() {
    (cd /workspace/backend && "$@")
}

# Função para executar comandos no frontend
frontend_exec() {
    (cd /workspace/frontend && "$@")
}

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
SERVICES_READY=false
for i in {1..60}; do
    if mysqladmin ping -h mysql -u root -proot123 --silent 2>/dev/null && \
       redis-cli -h redis ping >/dev/null 2>&1; then
        SERVICES_READY=true
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
    composer create-project laravel/laravel:^11.0 backend --prefer-dist --no-interaction

    # Instalar dependências específicas
    log "📚 Instalando dependências Laravel..."
    backend_exec composer require laravel/sanctum laravel/horizon spatie/laravel-permission
    backend_exec composer require spatie/laravel-query-builder spatie/laravel-backup
    backend_exec composer require barryvdh/laravel-cors league/flysystem-aws-s3-v3

    # Dependências de desenvolvimento
    backend_exec composer require --dev laravel/telescope barryvdh/laravel-debugbar
    backend_exec composer require --dev phpunit/phpunit mockery/mockery fakerphp/faker
    backend_exec composer require --dev friendsofphp/php-cs-fixer phpstan/phpstan
    backend_exec composer require --dev laravel/sail pestphp/pest

    success "✅ Projeto Laravel criado com sucesso"
else
    log "Backend Laravel existente encontrado"
    backend_exec composer install --no-interaction
    success "✅ Dependências do backend atualizadas"
fi

# 3. Configurar Frontend React
step "⚛️ Configurando Frontend React..."
if [ ! -d "frontend" ]; then
    log "Criando projeto React com Vite..."
    npm create vite@latest frontend -- --template react-ts

    # Instalar dependências básicas
    log "📚 Instalando dependências React..."
    frontend_exec npm install

    # Instalar dependências do projeto
    frontend_exec npm install @tanstack/react-query react-router-dom axios
    frontend_exec npm install @headlessui/react @heroicons/react
    frontend_exec npm install tailwindcss @tailwindcss/forms @tailwindcss/typography
    frontend_exec npm install react-hook-form @hookform/resolvers yup
    frontend_exec npm install date-fns react-hot-toast @vite-pwa/vite-plugin
    frontend_exec npm install workbox-precaching workbox-routing workbox-strategies

    # Dependências de desenvolvimento
    frontend_exec npm install --dev @vitejs/plugin-react-swc
    frontend_exec npm install --dev @types/react @types/react-dom @types/node
    frontend_exec npm install --dev @typescript-eslint/eslint-plugin @typescript-eslint/parser
    frontend_exec npm install --dev eslint eslint-plugin-react-hooks eslint-plugin-react-refresh
    frontend_exec npm install --dev prettier @testing-library/react @testing-library/jest-dom
    frontend_exec npm install --dev @testing-library/user-event vitest jsdom autoprefixer postcss
    frontend_exec npm install --dev typescript typescript-eslint globals

    success "✅ Projeto React criado com sucesso"
else
    log "Frontend React existente encontrado - verificando dependências..."

    # Verificar se package.json existe e tem dependências
    if [ -f "frontend/package.json" ]; then
        log "📦 Instalando dependências existentes do package.json..."
        frontend_exec npm ci --no-audit --prefer-offline || frontend_exec npm install --no-audit

        # Verificar se node_modules tem o plugin necessário
        if [ ! -d "frontend/node_modules/@vitejs/plugin-react-swc" ]; then
            log "🔧 Instalando plugin React SWC faltante..."
            frontend_exec npm install --save-dev @vitejs/plugin-react-swc
        fi



        success "✅ Dependências do frontend verificadas e atualizadas"
    else
        warn "⚠️ package.json não encontrado no frontend, reinstalando dependências..."
        frontend_exec npm install
        success "✅ Dependências do frontend instaladas"
    fi
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

    success "✅ Arquivo .env do backend configurado"
else
    info "ℹ️ Arquivo .env do backend já existe"
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
    success "✅ Arquivo .env do frontend configurado"
else
    info "ℹ️ Arquivo .env do frontend já existe"
fi

# 5. Configurar Laravel
step "🎯 Configurando Laravel..."

# Gerar chave da aplicação se não existir
if ! grep -q "APP_KEY=" backend/.env || [ -z "$(grep APP_KEY= backend/.env | cut -d'=' -f2)" ]; then
    log "🔑 Gerando chave da aplicação..."
    backend_exec php artisan key:generate --force
    success "✅ Chave da aplicação gerada"
else
    info "ℹ️ Chave da aplicação já existe"
fi

# Aguardar banco estar pronto e verificar migrações
log "🗄️ Verificando banco de dados..."
DB_AVAILABLE=false
for i in {1..30}; do
    if backend_exec php artisan migrate:status &>/dev/null; then
        DB_AVAILABLE=true
        success "✅ Conexão com banco estabelecida!"
        break
    fi
    if [ $i -eq 30 ]; then
        warn "⚠️ Não foi possível conectar ao banco"
        break
    fi
    echo -n "."
    sleep 2
done
echo

# Configurar banco de dados apenas se disponível
if [ "$DB_AVAILABLE" = true ]; then
    # Verificar se há migrações pendentes
    MIGRATION_COUNT=$(backend_exec php artisan migrate:status --pending 2>/dev/null | grep -c "Pending" || echo "0")

    if [ "$MIGRATION_COUNT" -gt 0 ] || ! backend_exec php artisan migrate:status &>/dev/null; then
        log "🔄 Executando migrações pendentes..."
        backend_exec php artisan migrate --force
        success "✅ Migrações executadas com sucesso"
    else
        info "ℹ️ Todas as migrações já foram executadas"
    fi

    # Verificar e publicar configurações apenas se necessário
    log "📄 Verificando configurações dos pacotes..."

    # Sanctum
    if [ ! -f "backend/config/sanctum.php" ]; then
        log "Publicando configurações do Sanctum..."
        backend_exec php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider" --force --quiet
    else
        info "ℹ️ Configurações do Sanctum já publicadas"
    fi

    # Spatie Permission
    if [ ! -f "backend/config/permission.php" ]; then
        log "Publicando configurações do Spatie Permission..."
        backend_exec php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider" --force --quiet
    else
        info "ℹ️ Configurações do Spatie Permission já publicadas"
    fi

    # Executar migrações novamente se houver novas migrações dos pacotes
    NEW_MIGRATION_COUNT=$(backend_exec php artisan migrate:status --pending 2>/dev/null | grep -c "Pending" || echo "0")
    if [ "$NEW_MIGRATION_COUNT" -gt 0 ]; then
        log "🔄 Executando novas migrações dos pacotes..."
        backend_exec php artisan migrate --force
    fi

    # Criar storage link se não existir
    if [ ! -L "backend/public/storage" ]; then
        log "🔗 Criando link simbólico do storage..."
        backend_exec php artisan storage:link
        success "✅ Link do storage criado"
    else
        info "ℹ️ Link do storage já existe"
    fi

    # Limpar caches
    log "🧹 Limpando caches..."
    backend_exec php artisan cache:clear
    backend_exec php artisan config:clear
    backend_exec php artisan route:clear
    backend_exec php artisan view:clear
    success "✅ Caches limpos"
fi

# 6. Configurar ferramentas de qualidade
step "🔍 Configurando ferramentas de qualidade..."

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

# ESLint Frontend
if [ ! -f "frontend/.eslintrc.js" ] && [ -d "frontend" ]; then
    log "Configurando ESLint para o frontend..."
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
    success "✅ ESLint configurado"
else
    info "ℹ️ ESLint já configurado"
fi

# Prettier
if [ ! -f ".prettierrc" ]; then
    log "Configurando Prettier..."
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
    success "✅ Prettier configurado"
else
    info "ℹ️ Prettier já configurado"
fi

# 7. Configurar Git Hooks com Husky
step "🔗 Configurando Git Hooks..."
if [ ! -f "package.json" ]; then
    log "Criando package.json raiz..."
    cat > package.json << 'EOF'
{
  "name": "rei-do-oleo",
  "version": "1.0.0",
  "description": "Sistema de Gestão para Troca de Óleo Automotivo",
  "private": true,
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
    success "✅ Package.json criado e dependências instaladas"
else
    log "Atualizando dependências do package.json..."
    npm install
    info "ℹ️ Package.json já existe, dependências atualizadas"
fi

# Instalar e configurar Husky (opcional no devcontainer)
if [ ! -d ".husky" ]; then
    log "Configurando Husky para Git Hooks..."

    # Tentar configurar Husky, mas não falhar se não conseguir
    if npx husky install 2>/dev/null; then
        if npx husky add .husky/pre-commit "npx lint-staged" 2>/dev/null; then
            success "✅ Husky configurado com sucesso"
        else
            warn "⚠️ Não foi possível adicionar hook pre-commit do Husky"
        fi
    else
        warn "⚠️ Husky não pôde ser configurado (possível problema de permissão no devcontainer)"
        info "ℹ️ Git hooks podem ser configurados manualmente depois"
    fi

    # Configurar lint-staged mesmo se Husky falhar
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
    success "✅ Configuração lint-staged criada"
else
    info "ℹ️ Husky já configurado"
fi

# 8. Criar bucket no MinIO
step "📦 Configurando MinIO Storage..."
if [ "$SERVICES_READY" = true ]; then
    sleep 5  # Aguardar MinIO estar pronto
    if command_exists "mc"; then
        log "Configurando bucket no MinIO..."
        mc alias set minio http://minio:9000 reidooleo secret123456 >/dev/null 2>&1 || true
        mc mb minio/rei-do-oleo-storage >/dev/null 2>&1 || true
        mc policy set public minio/rei-do-oleo-storage >/dev/null 2>&1 || true
        success "✅ MinIO configurado"
    else
        info "ℹ️ MinIO client não disponível, configuração manual necessária"
    fi
fi

# 9. Verificação final do ambiente
step "🔍 Verificação final do ambiente..."

# Verificar se as dependências críticas do frontend estão instaladas
if [ -d "frontend/node_modules/@vitejs/plugin-react-swc" ]; then
    success "✅ Dependências críticas do frontend verificadas"
else
    warn "⚠️ Algumas dependências do frontend podem estar faltando"
    log "Reinstalando dependências do frontend..."
    frontend_exec npm install --no-workspaces
fi

# 10. Finalização
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
║  🔧 Para iniciar desenvolvimento:                        ║
║  npm run dev      - Iniciar ambos servidores            ║
║                                                          ║
║  📋 Scripts de manutenção:                               ║
║  npm run test     - Executar todos os testes            ║
║  npm run lint     - Executar análise de código          ║
║  npm run fix      - Corrigir problemas de formatação    ║
║  npm run build    - Build de produção do frontend       ║
║  npm run setup:git - Configurar Git manualmente         ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

info "🎯 Ambiente de desenvolvimento totalmente configurado!"

# 11. Configurar Git Global
step "🔐 Configurando Git Global..."
if [ -n "$GIT_USER_NAME" ] && [ -n "$GIT_USER_EMAIL" ]; then
    log "Configurando usuário Git: $GIT_USER_NAME <$GIT_USER_EMAIL>"
    git config --global user.name "$GIT_USER_NAME"
    git config --global user.email "$GIT_USER_EMAIL"
    git config --global init.defaultBranch main
    git config --global pull.rebase false
    git config --global core.autocrlf input
    git config --global core.editor "code --wait"
    success "✅ Git configurado com sucesso"
else
    warn "⚠️ Variáveis GIT_USER_NAME e GIT_USER_EMAIL não definidas"
    info "ℹ️ Configure manualmente com:"
    info "    git config --global user.name \"Seu Nome\""
    info "    git config --global user.email \"seu@email.com\""
fi

# 12. Configurar SSH para Git
step "🔐 Configurando SSH para Git..."
if [ -f "/workspace/.devcontainer/scripts/ssh-setup.sh" ]; then
    bash /workspace/.devcontainer/scripts/ssh-setup.sh
else
    info "ℹ️ Script SSH não encontrado, configure manualmente se necessário"
fi

info "🚀 Execute 'npm run dev' para iniciar os serviços de desenvolvimento!"

# ---
# Corrigir permissões de todos os diretórios para o usuário vscode
step "🔐 Corrigindo permissões dos diretórios..."
log "Corrigindo permissões do diretório frontend..."
chown -R vscode:vscode /workspace/frontend
chmod -R u+rw /workspace/frontend

log "Corrigindo permissões do diretório backend..."
chown -R vscode:vscode /workspace/backend
chmod -R u+rw /workspace/backend

log "Corrigindo permissões do diretório scripts..."
chown -R vscode:vscode /workspace/scripts
chmod -R u+rw /workspace/scripts

log "Corrigindo permissões do diretório docs..."
chown -R vscode:vscode /workspace/docs
chmod -R u+rw /workspace/docs

log "Corrigindo permissões do diretório .devcontainer..."
chown -R vscode:vscode /workspace/.devcontainer
chmod -R u+rw /workspace/.devcontainer

log "Corrigindo permissões do diretório .github..."
chown -R vscode:vscode /workspace/.github
chmod -R u+rw /workspace/.github

log "Corrigindo permissões do diretório docker..."
chown -R vscode:vscode /workspace/docker
chmod -R u+rw /workspace/docker

log "Corrigindo permissões do diretório .husky..."
chown -R vscode:vscode /workspace/.husky
chmod -R u+rw /workspace/.husky

log "Corrigindo permissões do diretório .vscode..."
chown -R vscode:vscode /workspace/.vscode
chmod -R u+rw /workspace/.vscode

# Corrigir permissões de arquivos importantes na raiz
log "Corrigindo permissões de arquivos na raiz..."
chown vscode:vscode /workspace/package.json /workspace/package-lock.json 2>/dev/null || true
chown vscode:vscode /workspace/docker-compose.yml /workspace/docker-compose.prod.yml 2>/dev/null || true
chown vscode:vscode /workspace/.prettierrc /workspace/.editorconfig 2>/dev/null || true
chown vscode:vscode /workspace/.php-cs-fixer.php /workspace/phpstan.neon 2>/dev/null || true
chmod u+rw /workspace/package.json /workspace/package-lock.json 2>/dev/null || true
chmod u+rw /workspace/docker-compose.yml /workspace/docker-compose.prod.yml 2>/dev/null || true
chmod u+rw /workspace/.prettierrc /workspace/.editorconfig 2>/dev/null || true
chmod u+rw /workspace/.php-cs-fixer.php /workspace/phpstan.neon 2>/dev/null || true

success "✅ Permissões corrigidas para todos os diretórios e arquivos importantes"
