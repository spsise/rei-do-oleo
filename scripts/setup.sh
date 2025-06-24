#!/bin/bash

# 🚀 Script de Setup - Sistema Rei do Óleo MVP
# Este script configura completamente o ambiente de desenvolvimento

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

# Banner
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    🛠️  REI DO ÓLEO MVP                    ║
║                   Setup de Desenvolvimento                ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Verificar se estamos no diretório correto
if [ ! -f "docker-compose.yml" ]; then
    error "Execute este script na raiz do projeto!"
    exit 1
fi

log "Iniciando configuração do projeto..."

# 1. Configurar Backend Laravel
log "📦 Configurando Backend Laravel..."
if [ ! -d "backend" ]; then
    log "Criando projeto Laravel..."
    composer create-project laravel/laravel:^11.0 backend
    cd backend
    
    # Instalar dependências específicas do projeto
    log "Instalando dependências Laravel..."
    composer require laravel/sanctum
    composer require laravel/horizon
    composer require spatie/laravel-permission
    composer require spatie/laravel-query-builder
    composer require spatie/laravel-backup
    composer require barryvdh/laravel-cors
    composer require league/flysystem-aws-s3-v3
    
    # Dependências de desenvolvimento
    composer require --dev laravel/telescope
    composer require --dev barryvdh/laravel-debugbar
    composer require --dev phpunit/phpunit
    composer require --dev mockery/mockery
    composer require --dev fakerphp/faker
    composer require --dev friendsofphp/php-cs-fixer
    composer require --dev phpstan/phpstan
    
    cd ..
else
    log "Backend Laravel já existe, atualizando dependências..."
    cd backend
    composer install
    cd ..
fi

# 2. Configurar Frontend React
log "⚛️ Configurando Frontend React..."
if [ ! -d "frontend" ]; then
    log "Criando projeto React com Vite..."
    npm create vite@latest frontend -- --template react-ts
    cd frontend
    
    # Instalar dependências específicas do projeto
    log "Instalando dependências React..."
    npm install
    npm install @tanstack/react-query
    npm install react-router-dom
    npm install @headlessui/react
    npm install @heroicons/react
    npm install tailwindcss
    npm install @tailwindcss/forms
    npm install @tailwindcss/typography
    npm install axios
    npm install react-hook-form
    npm install @hookform/resolvers
    npm install yup
    npm install date-fns
    npm install react-hot-toast
    npm install workbox-webpack-plugin
    npm install @vite-pwa/vite-plugin
    
    # Dependências de desenvolvimento
    npm install --save-dev @types/react
    npm install --save-dev @types/react-dom
    npm install --save-dev @typescript-eslint/eslint-plugin
    npm install --save-dev @typescript-eslint/parser
    npm install --save-dev eslint
    npm install --save-dev eslint-plugin-react-hooks
    npm install --save-dev eslint-plugin-react-refresh
    npm install --save-dev prettier
    npm install --save-dev @testing-library/react
    npm install --save-dev @testing-library/jest-dom
    npm install --save-dev @testing-library/user-event
    npm install --save-dev vitest
    npm install --save-dev jsdom
    
    cd ..
else
    log "Frontend React já existe, atualizando dependências..."
    cd frontend
    npm install
    cd ..
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
    
    log "Arquivo .env do backend configurado!"
else
    log "Arquivo .env do backend já existe"
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
    log "Arquivo .env do frontend configurado!"
else
    log "Arquivo .env do frontend já existe"
fi

# 4. Configurar Laravel
log "🎯 Configurando Laravel..."
cd backend

# Gerar chave da aplicação
if ! grep -q "APP_KEY=" .env || [ -z "$(grep APP_KEY= .env | cut -d'=' -f2)" ]; then
    log "Gerando chave da aplicação..."
    php artisan key:generate
fi

# Aguardar banco de dados
log "Aguardando conexão com banco de dados..."
for i in {1..30}; do
    if php artisan migrate:status &> /dev/null; then
        log "Conexão com banco estabelecida!"
        break
    fi
    if [ $i -eq 30 ]; then
        warn "Não foi possível conectar ao banco. Execute 'php artisan migrate' manualmente após o banco estar disponível."
        break
    fi
    sleep 2
done

# Executar migrações
if php artisan migrate:status &> /dev/null; then
    log "Executando migrações..."
    php artisan migrate
    
    # Publicar configurações dos pacotes
    log "Publicando configurações dos pacotes..."
    php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
    php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
    php artisan vendor:publish --tag="laravel-backup-config"
    
    # Executar migrações novamente (para novos pacotes)
    php artisan migrate
    
    # Criar link de storage
    php artisan storage:link
    
    # Otimizações de desenvolvimento
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
fi

cd ..

# 5. Configurar Git Hooks
log "🔗 Configurando Git Hooks..."
if [ ! -f "package.json" ]; then
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
    "dev": "concurrently \"cd backend && php artisan serve --host=0.0.0.0\" \"cd frontend && npm run dev\"",
    "build": "cd frontend && npm run build",
    "test": "concurrently \"cd backend && php artisan test\" \"cd frontend && npm test\"",
    "lint": "concurrently \"cd backend && ./vendor/bin/phpstan analyse\" \"cd frontend && npm run lint\"",
    "fix": "concurrently \"cd backend && ./vendor/bin/php-cs-fixer fix\" \"cd frontend && npm run lint:fix\"",
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
fi

# Instalar Husky
if [ ! -d ".husky" ]; then
    log "Configurando Husky para Git Hooks..."
    npx husky install
    npx husky add .husky/pre-commit "npx lint-staged"
    npx husky add .husky/commit-msg "npx commitlint --edit \$1"
    
    # Configurar lint-staged
    cat > .lintstagedrc.json << EOF
{
  "backend/**/*.php": [
    "./backend/vendor/bin/php-cs-fixer fix",
    "./backend/vendor/bin/phpstan analyse --no-progress"
  ],
  "frontend/**/*.{js,jsx,ts,tsx}": [
    "cd frontend && npm run lint:fix",
    "cd frontend && npm run type-check"
  ]
}
EOF
fi

# 6. Criar arquivos de documentação
log "📖 Criando documentação inicial..."
if [ ! -f "docs/README.md" ]; then
    mkdir -p docs
    cat > docs/README.md << EOF
# 📚 Documentação - Sistema Rei do Óleo

## 🚀 Início Rápido

\`\`\`bash
# Clonar repositório
git clone <repo-url>
cd rei-do-oleo

# Executar setup
bash scripts/setup.sh

# Iniciar desenvolvimento
bash scripts/start.sh
\`\`\`

## 🏗️ Arquitetura

- **Backend**: Laravel 12 + MySQL + Redis
- **Frontend**: React 18 + TypeScript + Vite + PWA
- **Containerização**: Docker + Docker Compose
- **CI/CD**: GitHub Actions

## 📁 Estrutura do Projeto

Veja [STRUCTURE.md](STRUCTURE.md) para detalhes da estrutura.

## 🔧 Desenvolvimento

Veja [DEVELOPMENT.md](DEVELOPMENT.md) para guia de desenvolvimento.

## 🚀 Deploy

Veja [DEPLOYMENT.md](DEPLOYMENT.md) para instruções de deploy.
EOF
fi

# 7. Configurar hosts locais
log "🌐 Configurando hosts locais..."
if command -v sudo &> /dev/null; then
    if ! grep -q "frontend.reidooleo.local" /etc/hosts; then
        info "Adicionando entradas ao /etc/hosts (pode solicitar senha)..."
        echo "127.0.0.1 frontend.reidooleo.local" | sudo tee -a /etc/hosts
        echo "127.0.0.1 api.reidooleo.local" | sudo tee -a /etc/hosts
    fi
else
    warn "Adicione manualmente ao seu /etc/hosts:"
    warn "127.0.0.1 frontend.reidooleo.local"
    warn "127.0.0.1 api.reidooleo.local"
fi

# 8. Finalização
log "✅ Setup concluído com sucesso!"
echo -e "${GREEN}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    🎉 SETUP CONCLUÍDO!                   ║
╠═══════════════════════════════════════════════════════════╣
║  Para iniciar o desenvolvimento, execute:                ║
║  bash scripts/start.sh                                   ║
║                                                           ║
║  URLs de acesso:                                         ║
║  🌐 Frontend: http://frontend.reidooleo.local            ║
║  🔧 API: http://api.reidooleo.local                      ║
║  📧 MailHog: http://localhost:8025                       ║
║  🗄️ Adminer: http://localhost:8081                       ║
║  📊 Redis: http://localhost:8082                         ║
║  📦 MinIO: http://localhost:9001                         ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

info "Execute 'bash scripts/start.sh' para iniciar os serviços!" 