#!/bin/bash

# 🔧 Troubleshooting Script - Resolução de Problemas do DevContainer
# Script para diagnosticar e resolver problemas comuns

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
log() { echo -e "${GREEN}[TROUBLESHOOT]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${PURPLE}[SUCCESS]${NC} $1"; }
step() { echo -e "${CYAN}[STEP]${NC} $1"; }

cd /workspace

echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                🔧 TROUBLESHOOTING                         ║
║           Diagnóstico e Resolução de Problemas            ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

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

# 1. Verificar estrutura do projeto
step "🔍 Verificando estrutura do projeto..."
if [ ! -d "backend" ]; then
    error "❌ Diretório backend não encontrado"
    exit 1
fi

if [ ! -d "frontend" ]; then
    error "❌ Diretório frontend não encontrado"
    exit 1
fi

if [ ! -f "package.json" ]; then
    error "❌ package.json não encontrado"
    exit 1
fi

success "✅ Estrutura do projeto OK"

# 2. Verificar dependências do backend
step "🐘 Verificando dependências do backend..."
if [ ! -d "backend/vendor" ]; then
    warn "⚠️ Vendor não encontrado, instalando dependências..."
    backend_exec composer install --no-interaction
else
    info "ℹ️ Vendor encontrado"
fi

# 3. Verificar dependências do frontend
step "⚛️ Verificando dependências do frontend..."
if [ ! -d "frontend/node_modules" ]; then
    warn "⚠️ node_modules não encontrado, instalando dependências..."
    frontend_exec npm install
else
    info "ℹ️ node_modules encontrado"
fi

# 4. Verificar dependências da raiz
step "📦 Verificando dependências da raiz..."
if [ ! -d "node_modules" ]; then
    warn "⚠️ node_modules da raiz não encontrado, instalando dependências..."
    npm install
else
    info "ℹ️ node_modules da raiz encontrado"
fi

# 5. Verificar configuração do Husky
step "🐕 Verificando configuração do Husky..."
if [ ! -d ".husky" ]; then
    warn "⚠️ Husky não configurado"
    info "ℹ️ Execute: npm run setup:husky"
else
    success "✅ Husky configurado"
fi

# 6. Verificar arquivos de configuração
step "⚙️ Verificando arquivos de configuração..."

# Backend .env
if [ ! -f "backend/.env" ]; then
    warn "⚠️ backend/.env não encontrado"
    if [ -f "backend/.env.example" ]; then
        log "📋 Copiando .env.example para .env..."
        cp backend/.env.example backend/.env
        success "✅ .env criado"
    else
        error "❌ .env.example não encontrado"
    fi
else
    success "✅ backend/.env OK"
fi

# Frontend .env
if [ ! -f "frontend/.env" ]; then
    warn "⚠️ frontend/.env não encontrado"
    if [ -f "frontend/.env.example" ]; then
        log "📋 Copiando .env.example para .env..."
        cp frontend/.env.example frontend/.env
        success "✅ .env criado"
    else
        info "ℹ️ .env.example não encontrado (opcional)"
    fi
else
    success "✅ frontend/.env OK"
fi

# 7. Verificar permissões
step "🔐 Verificando permissões..."
if [ -d ".husky" ]; then
    if [ ! -x ".husky/pre-commit" ]; then
        warn "⚠️ Hook pre-commit sem permissão de execução"
        chmod +x .husky/*
        success "✅ Permissões corrigidas"
    else
        success "✅ Permissões dos hooks OK"
    fi
fi

# 8. Verificar serviços
step "🔌 Verificando serviços..."
if command_exists "mysqladmin"; then
    if mysqladmin ping -h mysql -u root -proot123 --silent 2>/dev/null; then
        success "✅ MySQL OK"
    else
        warn "⚠️ MySQL não responde"
    fi
else
    info "ℹ️ mysqladmin não disponível"
fi

if command_exists "redis-cli"; then
    if redis-cli -h redis ping >/dev/null 2>&1; then
        success "✅ Redis OK"
    else
        warn "⚠️ Redis não responde"
    fi
else
    info "ℹ️ redis-cli não disponível"
fi

# 9. Verificar build do frontend
step "🏗️ Verificando build do frontend..."
if frontend_exec npm run build >/dev/null 2>&1; then
    success "✅ Build do frontend OK"
else
    warn "⚠️ Build do frontend falhou"
    info "ℹ️ Execute: cd frontend && npm run build"
fi

# 10. Verificar testes do backend
step "🧪 Verificando testes do backend..."
if backend_exec php artisan test --stop-on-failure >/dev/null 2>&1; then
    success "✅ Testes do backend OK"
else
    warn "⚠️ Testes do backend falharam"
    info "ℹ️ Execute: cd backend && php artisan test"
fi

# 11. Resumo final
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    📋 RESUMO FINAL                        ║
╠═══════════════════════════════════════════════════════════╣
EOF
echo -e "${NC}"

if [ -d ".husky" ]; then
    success "✅ Husky configurado"
else
    warn "⚠️ Husky não configurado - Execute: npm run setup:husky"
fi

if [ -f "backend/.env" ]; then
    success "✅ Backend configurado"
else
    warn "⚠️ Backend não configurado"
fi

if [ -d "frontend/node_modules" ]; then
    success "✅ Frontend configurado"
else
    warn "⚠️ Frontend não configurado"
fi

echo -e "${BLUE}"
cat << "EOF"
╠═══════════════════════════════════════════════════════════╣
║  🔧 Comandos úteis:                                       ║
║  npm run dev          - Iniciar desenvolvimento          ║
║  npm run setup:husky  - Configurar Husky manualmente     ║
║  npm run test         - Executar todos os testes         ║
║  npm run lint         - Executar análise de código       ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

success "🎉 Troubleshooting concluído!" 