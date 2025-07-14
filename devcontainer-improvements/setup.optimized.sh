#!/bin/bash

# 🚀 Setup Script Otimizado - Rei do Óleo DevContainer
# Seguindo as melhores práticas de Shell Scripting 2024/2025

set -euo pipefail  # Exit on error, undefined vars, pipe failures
IFS=$'\n\t'       # Secure Internal Field Separator

# ===============================
# CONFIGURAÇÕES GLOBAIS
# ===============================

readonly SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
readonly WORKSPACE_DIR="/workspace"
readonly LOG_FILE="/tmp/setup.log"
readonly TIMEOUT_SERVICES=120
readonly TIMEOUT_COMMANDS=60

# Cores para output
readonly RED='\033[0;31m'
readonly GREEN='\033[0;32m'
readonly YELLOW='\033[1;33m'
readonly BLUE='\033[0;34m'
readonly PURPLE='\033[0;35m'
readonly CYAN='\033[0;36m'
readonly NC='\033[0m' # No Color

# ===============================
# FUNÇÕES DE LOGGING
# ===============================

log() {
    local level="$1"
    shift
    local message="$*"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo -e "${timestamp} [${level}] ${message}" | tee -a "${LOG_FILE}"
}

info() { log "${BLUE}INFO${NC}" "$@"; }
warn() { log "${YELLOW}WARN${NC}" "$@"; }
error() { log "${RED}ERROR${NC}" "$@"; }
success() { log "${GREEN}SUCCESS${NC}" "$@"; }
step() { log "${CYAN}STEP${NC}" "$@"; }

# ===============================
# FUNÇÕES DE UTILIDADE
# ===============================

command_exists() {
    command -v "$1" >/dev/null 2>&1
}

wait_for_service() {
    local service="$1"
    local host="${2:-localhost}"
    local port="$3"
    local timeout="${4:-$TIMEOUT_SERVICES}"
    
    step "🔄 Aguardando serviço ${service} em ${host}:${port}..."
    
    if command_exists dockerize; then
        dockerize -wait "tcp://${host}:${port}" -timeout "${timeout}s"
    else
        local count=0
        while ! nc -z "${host}" "${port}" 2>/dev/null; do
            if [ $count -ge $timeout ]; then
                error "Timeout aguardando ${service}"
                return 1
            fi
            count=$((count + 1))
            sleep 1
        done
    fi
    
    success "✅ Serviço ${service} está disponível!"
}

run_with_timeout() {
    local timeout="$1"
    shift
    local cmd="$*"
    
    timeout "${timeout}" bash -c "${cmd}" || {
        error "Comando falhou ou excedeu timeout de ${timeout}s: ${cmd}"
        return 1
    }
}

backup_file() {
    local file="$1"
    if [[ -f "$file" ]]; then
        cp "$file" "${file}.backup.$(date +%Y%m%d_%H%M%S)"
        info "📋 Backup criado: ${file}.backup.*"
    fi
}

# ===============================
# FUNÇÕES DE VALIDAÇÃO
# ===============================

validate_environment() {
    step "🔍 Validando ambiente..."
    
    # Verificar se estamos em um devcontainer
    if [[ ! -f "/workspace/.devcontainer/devcontainer.json" ]]; then
        warn "⚠️ Não parece ser um devcontainer válido"
    fi
    
    # Verificar dependências essenciais
    local dependencies=("curl" "git" "php" "composer" "node" "npm")
    for dep in "${dependencies[@]}"; do
        if ! command_exists "$dep"; then
            error "❌ Dependência faltando: $dep"
            return 1
        fi
    done
    
    # Verificar espaço em disco
    local available_space=$(df /workspace | tail -1 | awk '{print $4}')
    if [[ $available_space -lt 1048576 ]]; then  # 1GB em KB
        warn "⚠️ Pouco espaço em disco disponível: $((available_space / 1024))MB"
    fi
    
    success "✅ Ambiente validado!"
}

# ===============================
# FUNÇÕES DE SETUP
# ===============================

wait_for_services() {
    step "🔄 Aguardando serviços de infraestrutura..."
    
    local services=(
        "mysql:mysql:3306"
        "redis:redis:6379"
    )
    
    for service_info in "${services[@]}"; do
        IFS=':' read -r name host port <<< "$service_info"
        wait_for_service "$name" "$host" "$port" || {
            error "Falha ao aguardar serviço: $name"
            return 1
        }
    done
}

setup_git_config() {
    step "🔧 Configurando Git..."
    
    # Configurar Git se não estiver configurado
    if [[ -z "$(git config --global --get user.name)" ]]; then
        git config --global user.name "${GIT_USER_NAME:-Developer}"
        git config --global user.email "${GIT_USER_EMAIL:-dev@example.com}"
    fi
    
    # Configurações de segurança e performance
    git config --global init.defaultBranch main
    git config --global pull.rebase true
    git config --global fetch.prune true
    git config --global core.autocrlf input
    git config --global core.safecrlf true
    
    success "✅ Git configurado!"
}

setup_backend() {
    step "📦 Configurando Backend Laravel..."
    
    cd "${WORKSPACE_DIR}"
    
    # Verificar se o backend já existe
    if [[ ! -d "backend" ]]; then
        info "📥 Criando novo projeto Laravel..."
        run_with_timeout $TIMEOUT_COMMANDS "composer create-project laravel/laravel:^12.0 backend --no-interaction --prefer-dist"
    fi
    
    cd backend
    
    # Verificar se composer.json existe
    if [[ ! -f "composer.json" ]]; then
        error "❌ Arquivo composer.json não encontrado!"
        return 1
    fi
    
    # Instalar/atualizar dependências
    info "📚 Instalando dependências PHP..."
    run_with_timeout $TIMEOUT_COMMANDS "composer install --no-dev --optimize-autoloader --no-interaction"
    
    # Instalar dependências específicas do projeto
    local laravel_packages=(
        "laravel/sanctum"
        "laravel/horizon"
        "spatie/laravel-permission"
        "spatie/laravel-query-builder"
        "darkaonline/l5-swagger"
        "predis/predis"
    )
    
    for package in "${laravel_packages[@]}"; do
        if ! composer show "$package" >/dev/null 2>&1; then
            info "📦 Instalando $package..."
            run_with_timeout $TIMEOUT_COMMANDS "composer require $package --no-interaction"
        fi
    done
    
    # Verificar se .env existe
    if [[ ! -f ".env" ]]; then
        if [[ -f ".env.example" ]]; then
            cp .env.example .env
            info "📄 Arquivo .env criado a partir de .env.example"
        else
            error "❌ Arquivo .env.example não encontrado!"
            return 1
        fi
    fi
    
    # Configurar chave da aplicação
    if ! grep -q "APP_KEY=base64:" .env; then
        info "🔑 Gerando chave da aplicação..."
        php artisan key:generate --no-interaction
    fi
    
    # Configurar banco de dados
    info "🗄️ Configurando banco de dados..."
    
    # Aguardar MySQL estar pronto
    wait_for_service "mysql" "mysql" "3306"
    
    # Executar migrações
    if php artisan migrate:status >/dev/null 2>&1; then
        php artisan migrate --force --no-interaction
    else
        warn "⚠️ Migrações ainda não disponíveis"
    fi
    
    # Executar seeders se existirem
    if [[ -d "database/seeders" ]] && [[ -n "$(ls -A database/seeders)" ]]; then
        info "🌱 Executando seeders..."
        php artisan db:seed --force --no-interaction || warn "⚠️ Seeders falharam"
    fi
    
    # Limpar e otimizar cache
    info "🧹 Limpando cache..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    success "✅ Backend configurado!"
}

setup_frontend() {
    step "⚛️ Configurando Frontend React..."
    
    cd "${WORKSPACE_DIR}"
    
    # Verificar se o frontend já existe
    if [[ ! -d "frontend" ]]; then
        info "📥 Criando novo projeto React com TypeScript..."
        run_with_timeout $TIMEOUT_COMMANDS "npm create vite@latest frontend -- --template react-ts --yes"
    fi
    
    cd frontend
    
    # Verificar se package.json existe
    if [[ ! -f "package.json" ]]; then
        error "❌ Arquivo package.json não encontrado!"
        return 1
    fi
    
    # Instalar dependências
    info "📚 Instalando dependências Node.js..."
    run_with_timeout $TIMEOUT_COMMANDS "npm ci"
    
    # Instalar dependências específicas do projeto
    local react_packages=(
        "@tanstack/react-query"
        "axios"
        "react-router-dom"
        "react-hook-form"
        "@hookform/resolvers"
        "yup"
        "react-hot-toast"
        "date-fns"
        "@headlessui/react"
        "@heroicons/react"
    )
    
    local dev_packages=(
        "@types/react"
        "@types/react-dom"
        "@vitejs/plugin-react"
        "typescript"
        "eslint"
        "prettier"
        "@typescript-eslint/eslint-plugin"
        "@typescript-eslint/parser"
        "tailwindcss"
        "autoprefixer"
        "postcss"
        "vite"
        "vitest"
        "@testing-library/react"
        "@testing-library/jest-dom"
        "@testing-library/user-event"
    )
    
    # Instalar dependências de produção
    if [[ ${#react_packages[@]} -gt 0 ]]; then
        info "📦 Instalando dependências React..."
        run_with_timeout $TIMEOUT_COMMANDS "npm install ${react_packages[*]} --save"
    fi
    
    # Instalar dependências de desenvolvimento
    if [[ ${#dev_packages[@]} -gt 0 ]]; then
        info "🛠️ Instalando dependências de desenvolvimento..."
        run_with_timeout $TIMEOUT_COMMANDS "npm install ${dev_packages[*]} --save-dev"
    fi
    
    # Configurar TailwindCSS se não estiver configurado
    if [[ ! -f "tailwind.config.js" ]]; then
        info "🎨 Configurando TailwindCSS..."
        npx tailwindcss init -p
    fi
    
    # Build inicial para verificar se tudo está funcionando
    info "🏗️ Fazendo build inicial..."
    run_with_timeout $TIMEOUT_COMMANDS "npm run build"
    
    success "✅ Frontend configurado!"
}

setup_development_tools() {
    step "🔧 Configurando ferramentas de desenvolvimento..."
    
    # Configurar Xdebug
    if command_exists php && php -m | grep -q xdebug; then
        info "🐛 Configurando Xdebug..."
        
        # Verificar se as configurações do Xdebug estão corretas
        local xdebug_config="/etc/php/8.3/mods-available/xdebug.ini"
        if [[ -f "$xdebug_config" ]]; then
            if ! grep -q "xdebug.mode=debug" "$xdebug_config"; then
                echo "xdebug.mode=debug,develop,coverage" | sudo tee -a "$xdebug_config"
                echo "xdebug.start_with_request=yes" | sudo tee -a "$xdebug_config"
                echo "xdebug.client_host=host.docker.internal" | sudo tee -a "$xdebug_config"
                echo "xdebug.client_port=9003" | sudo tee -a "$xdebug_config"
            fi
        fi
    fi
    
    # Configurar aliases úteis
    local aliases_file="/home/vscode/.bash_aliases"
    if [[ ! -f "$aliases_file" ]]; then
        info "📝 Criando aliases úteis..."
        cat > "$aliases_file" << 'EOF'
# Laravel aliases
alias art='php artisan'
alias tinker='php artisan tinker'
alias migrate='php artisan migrate'
alias seed='php artisan db:seed'
alias fresh='php artisan migrate:fresh --seed'

# Frontend aliases
alias dev='npm run dev'
alias build='npm run build'
alias test='npm test'
alias lint='npm run lint'

# Git aliases
alias gst='git status'
alias gco='git checkout'
alias gcm='git commit -m'
alias gps='git push'
alias gpl='git pull'

# Docker aliases
alias dps='docker ps'
alias dlog='docker logs'
alias dexec='docker exec -it'
EOF
    fi
    
    success "✅ Ferramentas de desenvolvimento configuradas!"
}

cleanup_and_optimize() {
    step "🧹 Limpeza e otimização final..."
    
    # Limpar cache do Composer
    if command_exists composer; then
        composer clear-cache 2>/dev/null || true
    fi
    
    # Limpar cache do NPM
    if command_exists npm; then
        npm cache clean --force 2>/dev/null || true
    fi
    
    # Limpar arquivos temporários
    rm -rf /tmp/setup_temp_* 2>/dev/null || true
    
    # Otimizar permissões
    sudo chown -R vscode:vscode /home/vscode/.cache 2>/dev/null || true
    sudo chown -R vscode:vscode /workspace 2>/dev/null || true
    
    success "✅ Limpeza concluída!"
}

# ===============================
# FUNÇÃO PRINCIPAL
# ===============================

main() {
    # Banner de início
    echo -e "${BLUE}"
    cat << 'EOF'
╔═══════════════════════════════════════════════════════════╗
║                🛠️  REI DO ÓLEO - DEV SETUP                ║
║           Configuração Completa do Ambiente               ║
║                     Versão 2.0.0                         ║
╚═══════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    # Inicializar log
    echo "Setup iniciado em $(date)" > "${LOG_FILE}"
    info "📋 Log do setup: ${LOG_FILE}"
    
    # Executar etapas do setup
    local steps=(
        "validate_environment"
        "wait_for_services"
        "setup_git_config"
        "setup_backend"
        "setup_frontend"
        "setup_development_tools"
        "cleanup_and_optimize"
    )
    
    local total_steps=${#steps[@]}
    local current_step=0
    
    for step_function in "${steps[@]}"; do
        current_step=$((current_step + 1))
        info "📍 Executando etapa ${current_step}/${total_steps}: ${step_function}"
        
        if ! "$step_function"; then
            error "❌ Falha na etapa: ${step_function}"
            error "📋 Verifique o log: ${LOG_FILE}"
            exit 1
        fi
    done
    
    # Finalização
    success "🎉 Setup concluído com sucesso!"
    info "🚀 Ambiente de desenvolvimento está pronto!"
    info "📋 Log completo: ${LOG_FILE}"
    
    # Mostrar informações finais
    echo -e "\n${GREEN}┌─────────────────────────────────────────────────────────────┐${NC}"
    echo -e "${GREEN}│                    🎯 PRÓXIMOS PASSOS                       │${NC}"
    echo -e "${GREEN}├─────────────────────────────────────────────────────────────┤${NC}"
    echo -e "${GREEN}│  1. Backend Laravel: http://localhost:8000                 │${NC}"
    echo -e "${GREEN}│  2. Frontend React:  http://localhost:3000                 │${NC}"
    echo -e "${GREEN}│  3. Executar: cd backend && php artisan serve             │${NC}"
    echo -e "${GREEN}│  4. Executar: cd frontend && npm run dev                   │${NC}"
    echo -e "${GREEN}│  5. Verificar: docker-compose ps                           │${NC}"
    echo -e "${GREEN}└─────────────────────────────────────────────────────────────┘${NC}"
}

# ===============================
# TRATAMENTO DE ERROS
# ===============================

trap 'error "❌ Setup interrompido na linha $LINENO"' ERR
trap 'warn "⚠️ Setup interrompido pelo usuário"' INT TERM

# ===============================
# EXECUÇÃO
# ===============================

# Verificar se o script está sendo executado diretamente
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi