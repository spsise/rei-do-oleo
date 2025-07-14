#!/bin/bash

# 🚀 Quick Setup - Sistema Rei do Óleo (Otimizado)
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
║           🚀 REI DO ÓLEO - QUICK SETUP                   ║
║           Configuração Rápida do Ambiente                ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

cd /workspace

# 🔧 CORREÇÃO DEFINITIVA: Resolver problemas de permissão e cache
step "🔧 Corrigindo problemas de permissão e cache..."

# 1. Corrigir permissões do cache npm
if [ -d "/home/vscode/.cache/npm" ]; then
    log "Corrigindo permissões do cache npm..."
    sudo chown -R 1000:1000 "/home/vscode/.cache/npm" 2>/dev/null || true
    sudo chmod -R 755 "/home/vscode/.cache/npm" 2>/dev/null || true
fi

# 2. Limpar cache npm corrompido
log "Limpando cache npm corrompido..."
npm cache clean --force 2>/dev/null || true
rm -rf /home/vscode/.cache/npm/_cacache 2>/dev/null || true
rm -rf /home/vscode/.cache/npm/_logs 2>/dev/null || true

# 3. Configurar npm para evitar problemas de permissão
log "Configurando npm..."
npm config set cache /tmp/.npm-cache --global 2>/dev/null || true
npm config set prefix /home/vscode/.npm-global --global 2>/dev/null || true

# 4. Criar diretórios necessários com permissões corretas
mkdir -p /tmp/.npm-cache
mkdir -p /home/vscode/.npm-global
chown -R 1000:1000 /tmp/.npm-cache
chown -R 1000:1000 /home/vscode/.npm-global

success "✅ Problemas de permissão corrigidos"

# Função para testar conectividade MySQL com diferentes senhas
test_mysql_connection() {
    local password=$1
    mysql -h mysql -u root -p"$password" -e "SELECT 1;" >/dev/null 2>&1
    return $?
}

# Função para obter senha do MySQL
get_mysql_password() {
    # Lista de senhas para tentar
    local passwords=("root123" "" "password" "admin" "mysql" "123456" "secret")
    
    for password in "${passwords[@]}"; do
        if test_mysql_connection "$password"; then
            echo "$password"
            return 0
        fi
    done
    
    # Se nada funcionar, tentar detectar senha gerada nos logs
    local generated_password=$(docker logs rei-do-oleo_devcontainer-mysql-1 2>&1 | grep -o "GENERATED ROOT PASSWORD: [^[:space:]]*" | tail -1 | awk '{print $NF}')
    if [ -n "$generated_password" ]; then
        if test_mysql_connection "$generated_password"; then
            echo "$generated_password"
            return 0
        fi
    fi
    
    # Se ainda não funcionar, retornar erro
    return 1
}

# 1. Aguardar serviços estarem prontos (timeout reduzido)
step "🔄 Aguardando serviços estarem prontos..."
SERVICES_READY=false
for i in {1..60}; do
    if redis-cli -h redis ping >/dev/null 2>&1; then
        SERVICES_READY=true
        success "✅ Serviços Redis prontos!"
        break
    fi
    if [ $i -eq 60 ]; then
        warn "⚠️ Timeout aguardando Redis. Continuando..."
        break
    fi
    echo -n "."
    sleep 2
done
echo

# 2. Configurar Backend Laravel (se não existir)
step "📦 Configurando Backend Laravel..."
if [ ! -d "backend" ]; then
    log "Criando novo projeto Laravel..."
    composer create-project laravel/laravel:^11.0 backend --prefer-dist --no-interaction --no-dev
    
    # Instalar apenas dependências essenciais
    log "📚 Instalando dependências essenciais..."
    (cd backend && composer require laravel/sanctum --no-interaction)
    
    success "✅ Projeto Laravel criado com sucesso"
else
    log "Backend Laravel existente encontrado"
    if [ -f "backend/composer.json" ]; then
        (cd backend && composer install --no-interaction --no-dev)
        success "✅ Dependências do backend atualizadas"
    fi
fi

# 3. Configurar Frontend React (se não existir)
step "⚛️ Configurando Frontend React..."
if [ ! -d "frontend" ]; then
    log "Criando projeto React com Vite..."
    npm create vite@latest frontend -- --template react-ts --yes
    
    # 🔧 CORREÇÃO: Limpar node_modules corrompido antes de instalar
    if [ -d "frontend/node_modules" ]; then
        log "Removendo node_modules corrompido..."
        rm -rf frontend/node_modules
    fi
    
    # Instalar dependências básicas com configurações especiais
    log "📚 Instalando dependências React..."
    (cd frontend && npm install --no-audit --no-optional --prefer-offline --cache /tmp/.npm-cache)
    
    # Instalar apenas dependências essenciais
    (cd frontend && npm install @tanstack/react-query react-router-dom axios --no-audit --no-optional --prefer-offline --cache /tmp/.npm-cache)
    (cd frontend && npm install tailwindcss @tailwindcss/forms --no-audit --no-optional --prefer-offline --cache /tmp/.npm-cache)
    
    success "✅ Projeto React criado com sucesso"
else
    log "Frontend React existente encontrado"
    if [ -f "frontend/package.json" ]; then
        # 🔧 CORREÇÃO: Limpar node_modules corrompido antes de instalar
        if [ -d "frontend/node_modules" ]; then
            log "Removendo node_modules corrompido..."
            rm -rf frontend/node_modules
        fi
        
        # 🔧 CORREÇÃO: Limpar package-lock.json corrompido
        if [ -f "frontend/package-lock.json" ]; then
            log "Removendo package-lock.json corrompido..."
            rm -f frontend/package-lock.json
        fi
        
        log "Instalando dependências do frontend..."
        (cd frontend && npm install --no-audit --no-optional --prefer-offline --cache /tmp/.npm-cache)
        success "✅ Dependências do frontend atualizadas"
    fi
fi

# 4. Configurar variáveis de ambiente (rápido)
step "🔧 Configurando variáveis de ambiente..."

# Backend .env
if [ ! -f "backend/.env" ] && [ -f "backend/.env.example" ]; then
    log "Criando .env do backend..."
    cp backend/.env.example backend/.env
    
    # Configurações essenciais do banco
    sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' backend/.env
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' backend/.env
    sed -i 's/DB_DATABASE=laravel/DB_DATABASE=rei_do_oleo_dev/' backend/.env
    sed -i 's/DB_USERNAME=root/DB_USERNAME=rei_do_oleo/' backend/.env
    sed -i 's/DB_PASSWORD=/DB_PASSWORD=secret123/' backend/.env
    
    # Configurações Redis
    echo "REDIS_HOST=redis" >> backend/.env
    echo "REDIS_PORT=6379" >> backend/.env
    
    success "✅ .env do backend configurado"
fi

# Frontend .env
if [ ! -f "frontend/.env" ]; then
    log "Criando .env do frontend..."
    cat > frontend/.env << EOF
VITE_APP_NAME="Rei do Óleo"
VITE_API_URL=http://localhost:8000/api
VITE_APP_URL=http://localhost:3000
VITE_APP_ENV=development
EOF
    success "✅ .env do frontend configurado"
fi

# 5. Configurar banco de dados (rápido)
step "🗄️ Configurando banco de dados..."

# Aguardar MySQL estar totalmente pronto e descobrir senha
log "Aguardando MySQL estar totalmente inicializado..."
MYSQL_PASSWORD=""
for i in {1..60}; do
    # Tentar descobrir a senha
    if MYSQL_PASSWORD=$(get_mysql_password 2>/dev/null); then
        success "✅ MySQL está pronto! Senha descoberta."
        break
    fi
    
    if [ $i -eq 60 ]; then
        warn "⚠️ MySQL não está respondendo. Continuando sem configuração de banco..."
        MYSQL_PASSWORD=""
        break
    fi
    echo -n "."
    sleep 2
done
echo

# Se conseguiu conectar ao MySQL, configurar database
if [ -n "$MYSQL_PASSWORD" ]; then
    log "Usando senha MySQL: $MYSQL_PASSWORD"
    
    # Verificar se o database já existe
    if mysql -h mysql -u root -p"$MYSQL_PASSWORD" -e "USE rei_do_oleo_dev;" 2>/dev/null; then
        log "Database já existe"
    else
        log "Criando database..."
        mysql -h mysql -u root -p"$MYSQL_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS rei_do_oleo_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        mysql -h mysql -u root -p"$MYSQL_PASSWORD" -e "GRANT ALL PRIVILEGES ON rei_do_oleo_dev.* TO 'rei_do_oleo'@'%';"
        mysql -h mysql -u root -p"$MYSQL_PASSWORD" -e "FLUSH PRIVILEGES;"
        success "✅ Database criado"
    fi
else
    warn "⚠️ Não foi possível conectar ao MySQL. Database não configurado."
fi

# 6. Executar migrations (se backend existir)
if [ -d "backend" ] && [ -f "backend/artisan" ]; then
    step "🔄 Executando migrations..."
    (cd backend && php artisan migrate --force --no-interaction) || warn "⚠️ Migrations falharam, mas continuando..."
fi

# 7. Gerar chave da aplicação (se backend existir)
if [ -d "backend" ] && [ -f "backend/artisan" ]; then
    step "🔑 Gerando chave da aplicação..."
    (cd backend && php artisan key:generate --force --no-interaction) || warn "⚠️ Geração de chave falhou, mas continuando..."
fi

# 8. Configurar Tailwind (se frontend existir)
if [ -d "frontend" ] && [ -f "frontend/package.json" ]; then
    step "🎨 Configurando Tailwind CSS..."
    if [ ! -f "frontend/tailwind.config.js" ]; then
        (cd frontend && npx tailwindcss init -p)
        success "✅ Tailwind configurado"
    fi
fi

# 9. Configurar aliases úteis
step "⚙️ Configurando aliases..."
cat >> /home/vscode/.zshrc << 'EOF'

# 🚀 Laravel Aliases
alias art="php artisan"
alias serve="php artisan serve --host=0.0.0.0 --port=8000"
alias migrate="php artisan migrate"
alias migrate:fresh="php artisan migrate:fresh --seed"

# ⚛️ React Aliases
alias dev="npm run dev"
alias build="npm run build"

# 🗄️ Database Aliases
alias mysql-cli="mysql -h mysql -u rei_do_oleo -psecret123 rei_do_oleo_dev"
alias redis-cli="redis-cli -h redis"

# 🔧 Utility Aliases
alias ll="ls -alF"
alias ..="cd .."
alias ...="cd ../.."
EOF

# 10. Finalização
step "🎉 Finalizando setup..."
echo
success "✅ Setup rápido concluído com sucesso!"
echo
echo -e "${BLUE}🚀 Próximos passos:${NC}"
echo -e "  1. Backend: ${GREEN}cd backend && php artisan serve${NC}"
echo -e "  2. Frontend: ${GREEN}cd frontend && npm run dev${NC}"
echo -e "  3. Database: ${GREEN}mysql-cli${NC}"
echo -e "  4. Redis: ${GREEN}redis-cli${NC}"
echo
echo -e "${BLUE}📊 URLs disponíveis:${NC}"
echo -e "  • Backend API: ${GREEN}http://localhost:8000${NC}"
echo -e "  • Frontend: ${GREEN}http://localhost:3000${NC}"
echo -e "  • phpMyAdmin: ${GREEN}http://localhost:8110${NC}"
echo -e "  • Redis Commander: ${GREEN}http://localhost:6410${NC}"
echo -e "  • MailHog: ${GREEN}http://localhost:8030${NC}"
echo
echo -e "${BLUE}🔧 Credenciais:${NC}"
if [ -n "$MYSQL_PASSWORD" ]; then
    echo -e "  • MySQL Root: ${GREEN}root / $MYSQL_PASSWORD${NC}"
else
    echo -e "  • MySQL Root: ${YELLOW}Verificar logs do container${NC}"
fi
echo -e "  • MySQL App: ${GREEN}rei_do_oleo / secret123${NC}"
echo -e "  • Redis: ${GREEN}sem senha${NC}"
echo -e "  • Redis Commander: ${GREEN}admin / secret123${NC}"
echo
success "🎯 Ambiente pronto para desenvolvimento!" 