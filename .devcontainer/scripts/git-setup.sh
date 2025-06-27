#!/bin/bash

# 🔧 Git Configuration Script - Sistema Rei do Óleo
# Script para configuração completa do Git no ambiente de desenvolvimento

set -e

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Funções de logging
log() { echo -e "${GREEN}[GIT-SETUP]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${PURPLE}[SUCCESS]${NC} $1"; }

# Banner
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                🔧 CONFIGURAÇÃO DO GIT                    ║
║              Sistema Rei do Óleo - DevOps                ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Configurar usuário Git
log "👤 Configurando usuário Git..."

# Usar valores das variáveis de ambiente ou padrões
USER_NAME="${GIT_USER_NAME:-Sebastião Apolinario}"
USER_EMAIL="${GIT_USER_EMAIL:-spsise@gmail.com}"

# Configurações básicas do usuário
git config --global user.name "$USER_NAME"
git config --global user.email "$USER_EMAIL"
git config --global init.defaultBranch main
git config --global pull.rebase false
git config --global core.autocrlf input
git config --global core.editor "code --wait"

# Configurações úteis
git config --global color.ui auto
git config --global push.default simple

# Aliases úteis
git config --global alias.st status
git config --global alias.co checkout
git config --global alias.br branch
git config --global alias.ci commit
git config --global alias.graph "log --oneline --graph --decorate --all"

success "✅ Git configurado: $USER_NAME <$USER_EMAIL>"

# Verificar configuração
log "🔍 Configuração atual:"
echo "👤 Usuário: $(git config --global user.name)"
echo "📧 Email: $(git config --global user.email)"
echo "🌿 Branch padrão: $(git config --global init.defaultBranch)"

success "🎉 Configuração do Git concluída com sucesso!"
