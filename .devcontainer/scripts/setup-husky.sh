#!/bin/bash

# 🐕 Husky Setup Script - Configuração Manual de Git Hooks
# Script para configurar Husky quando o setup automático falhar

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funções de logging
log() { echo -e "${GREEN}[HUSKY]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }
success() { echo -e "${BLUE}[SUCCESS]${NC} $1"; }

cd /workspace

echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                🐕 HUSKY SETUP MANUAL                      ║
║           Configuração de Git Hooks                       ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Verificar se estamos no diretório correto
if [ ! -f "package.json" ]; then
    error "❌ package.json não encontrado. Execute este script na raiz do projeto."
    exit 1
fi

# Verificar se Husky está instalado
if ! npm list husky >/dev/null 2>&1; then
    log "📦 Instalando Husky..."
    npm install husky --save-dev
fi

# Verificar se estamos em um repositório Git
if [ ! -d ".git" ]; then
    warn "⚠️ Diretório .git não encontrado. Inicializando repositório Git..."
    git init
fi

# Configurar Husky
log "🔧 Configurando Husky..."
if npx husky install; then
    success "✅ Husky instalado com sucesso"
else
    error "❌ Falha ao instalar Husky"
    exit 1
fi

# Adicionar hook pre-commit
log "📝 Adicionando hook pre-commit..."
if npx husky add .husky/pre-commit "npx lint-staged"; then
    success "✅ Hook pre-commit adicionado"
else
    error "❌ Falha ao adicionar hook pre-commit"
    exit 1
fi

# Verificar se .lintstagedrc.json existe
if [ ! -f ".lintstagedrc.json" ]; then
    log "📄 Criando configuração lint-staged..."
    cat > .lintstagedrc.json << 'EOF'
{
  "backend/**/*.php": [
    "./vendor/bin/php-cs-fixer fix",
    "./vendor/bin/phpstan analyse --no-progress"
  ],
  "frontend/**/*.{js,jsx,ts,tsx}": [
    "cd frontend && npm run lint:fix"
  ],
  "**/*.{json,md,yml,yaml}": [
    "prettier --write"
  ]
}
EOF
    success "✅ Configuração lint-staged criada"
fi

# Dar permissão de execução aos hooks
if [ -d ".husky" ]; then
    log "🔐 Configurando permissões dos hooks..."
    chmod +x .husky/*
    success "✅ Permissões configuradas"
fi

# Verificação final
if [ -f ".husky/pre-commit" ]; then
    success "🎉 Husky configurado com sucesso!"
    echo -e "${GREEN}"
    cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    ✅ HUSKY PRONTO!                       ║
╠═══════════════════════════════════════════════════════════╣
║  🔧 Git hooks configurados:                              ║
║  • pre-commit: Executa lint-staged                       ║
║                                                          ║
║  📋 Para testar:                                         ║
║  git add . && git commit -m "test: test commit"          ║
║                                                          ║
║  🔍 Para desabilitar temporariamente:                    ║
║  git commit --no-verify -m "skip hooks"                  ║
╚═══════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
else
    error "❌ Falha na configuração final do Husky"
    exit 1
fi 