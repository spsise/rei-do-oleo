#!/bin/bash

# 🧪 Test Script - Sistema Rei do Óleo
# Executa todos os testes (PHPUnit + Jest)

set -e

# Cores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m'

log() { echo -e "${GREEN}[TEST]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${PURPLE}[SUCCESS]${NC} $1"; }

cd /workspace

# Banner
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                🧪 EXECUTANDO TESTES                       ║
║           Backend (PHPUnit) + Frontend (Jest)             ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Contadores
BACKEND_SUCCESS=0
FRONTEND_SUCCESS=0
LINT_SUCCESS=0

# 1. Testes Backend (Laravel PHPUnit)
if [ -d "backend" ]; then
    log "🐘 Executando testes do Backend (Laravel)..."
    cd backend
    
    # Configurar ambiente de teste
    if [ ! -f ".env.testing" ]; then
        cp .env .env.testing
        sed -i 's/DB_DATABASE=rei_do_oleo_dev/DB_DATABASE=rei_do_oleo_test/' .env.testing
        sed -i 's/APP_ENV=local/APP_ENV=testing/' .env.testing
    fi
    
    # Criar banco de teste se não existir
    mysql -h mysql -u root -proot123 -e "CREATE DATABASE IF NOT EXISTS rei_do_oleo_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true
    mysql -h mysql -u root -proot123 -e "GRANT ALL PRIVILEGES ON rei_do_oleo_test.* TO 'rei_do_oleo'@'%';" 2>/dev/null || true
    
    # Executar migrações no banco de teste
    php artisan migrate:fresh --env=testing --quiet
    
    # Executar testes
    if php artisan test --coverage; then
        success "✅ Testes Backend: PASSOU"
        BACKEND_SUCCESS=1
    else
        error "❌ Testes Backend: FALHOU"
    fi
    
    cd /workspace
else
    warn "⚠️ Diretório backend não encontrado"
fi

# 2. Testes Frontend (React + Vitest)
if [ -d "frontend" ]; then
    log "⚛️ Executando testes do Frontend (React)..."
    cd frontend
    
    if [ -f "package.json" ] && grep -q "vitest\|jest" package.json; then
        if npm test -- --run --coverage; then
            success "✅ Testes Frontend: PASSOU"
            FRONTEND_SUCCESS=1
        else
            error "❌ Testes Frontend: FALHOU"
        fi
    else
        warn "⚠️ Configuração de testes não encontrada no frontend"
    fi
    
    cd /workspace
else
    warn "⚠️ Diretório frontend não encontrado"
fi

# 3. Análise de Código (Linting)
log "🔍 Executando análise de código..."

# PHPStan (Backend)
if [ -d "backend" ] && [ -f "phpstan.neon" ]; then
    info "🔍 PHPStan (Backend)..."
    if ./vendor/bin/phpstan analyse --no-progress; then
        success "✅ PHPStan: PASSOU"
        ((LINT_SUCCESS++))
    else
        error "❌ PHPStan: FALHOU"
    fi
fi

# ESLint (Frontend)
if [ -d "frontend" ] && [ -f "frontend/.eslintrc.js" ]; then
    info "🔍 ESLint (Frontend)..."
    cd frontend
    if npm run lint; then
        success "✅ ESLint: PASSOU"
        ((LINT_SUCCESS++))
    else
        error "❌ ESLint: FALHOU"
    fi
    cd /workspace
fi

# 4. Verificação de Formatação
log "🎨 Verificando formatação do código..."

# PHP CS Fixer
if [ -f ".php-cs-fixer.php" ]; then
    info "🎨 PHP CS Fixer..."
    if ./vendor/bin/php-cs-fixer fix --dry-run --diff; then
        success "✅ Formatação PHP: OK"
    else
        warn "⚠️ Formatação PHP: Precisa de correção"
        info "Execute: ./vendor/bin/php-cs-fixer fix"
    fi
fi

# Prettier
if [ -f ".prettierrc" ] && [ -d "frontend" ]; then
    info "🎨 Prettier..."
    cd frontend
    if npm run format:check 2>/dev/null || npx prettier --check . 2>/dev/null; then
        success "✅ Formatação Frontend: OK"
    else
        warn "⚠️ Formatação Frontend: Precisa de correção"
        info "Execute: cd frontend && npm run format"
    fi
    cd /workspace
fi

# 5. Relatório Final
echo
echo -e "${BLUE}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                    📊 RELATÓRIO FINAL                     ║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════════════════════╝${NC}"

total_tests=0
passed_tests=0

if [ -d "backend" ]; then
    total_tests=$((total_tests + 1))
    if [ $BACKEND_SUCCESS -eq 1 ]; then
        echo -e "  ${GREEN}✅ Backend Tests: PASSOU${NC}"
        passed_tests=$((passed_tests + 1))
    else
        echo -e "  ${RED}❌ Backend Tests: FALHOU${NC}"
    fi
fi

if [ -d "frontend" ]; then
    total_tests=$((total_tests + 1))
    if [ $FRONTEND_SUCCESS -eq 1 ]; then
        echo -e "  ${GREEN}✅ Frontend Tests: PASSOU${NC}"
        passed_tests=$((passed_tests + 1))
    else
        echo -e "  ${RED}❌ Frontend Tests: FALHOU${NC}"
    fi
fi

echo
if [ $passed_tests -eq $total_tests ] && [ $total_tests -gt 0 ]; then
    success "🎉 TODOS OS TESTES PASSARAM! ($passed_tests/$total_tests)"
    exit 0
else
    error "💥 ALGUNS TESTES FALHARAM ($passed_tests/$total_tests)"
    exit 1
fi 