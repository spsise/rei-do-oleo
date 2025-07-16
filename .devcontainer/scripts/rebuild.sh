#!/bin/bash

# 🚀 Rebuild Completo - Sistema Rei do Óleo
# Script para limpar tudo e reconstruir o ambiente

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
log() { echo -e "${GREEN}[REBUILD]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${PURPLE}[SUCCESS]${NC} $1"; }
step() { echo -e "${CYAN}[STEP]${NC} $1"; }

# Banner de início
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║           🚀 REI DO ÓLEO - REBUILD COMPLETO              ║
║           Limpeza Total e Reconstrução                   ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Confirmação do usuário
echo -e "${YELLOW}⚠️  ATENÇÃO: Este script irá limpar TODOS os dados do projeto!${NC}"
echo -e "${YELLOW}   Isso inclui:${NC}"
echo -e "${YELLOW}   • Todos os containers Docker${NC}"
echo -e "${YELLOW}   • Todos os volumes Docker${NC}"
echo -e "${YELLOW}   • Todos os dados do banco MySQL${NC}"
echo -e "${YELLOW}   • Cache do Composer e npm${NC}"
echo
read -p "Tem certeza que deseja continuar? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}❌ Rebuild cancelado pelo usuário${NC}"
    exit 1
fi

# Não mudar diretório se não estivermos no DevContainer
if [ -d "/workspace" ]; then
    cd /workspace
fi

# 1. Parar todos os containers
step "🛑 Parando todos os containers..."
docker stop $(docker ps -q --filter "name=rei-do-oleo") 2>/dev/null || true
docker stop $(docker ps -q --filter "name=devcontainer") 2>/dev/null || true
success "✅ Containers parados"

# 2. Remover containers
step "🗑️ Removendo containers..."
docker rm -f $(docker ps -aq --filter "name=rei-do-oleo") 2>/dev/null || true
docker rm -f $(docker ps -aq --filter "name=devcontainer") 2>/dev/null || true
success "✅ Containers removidos"

# 3. Remover volumes
step "🗑️ Removendo volumes..."
docker volume rm $(docker volume ls -q --filter "name=mysql") 2>/dev/null || true
docker volume rm $(docker volume ls -q --filter "name=redis") 2>/dev/null || true
docker volume rm $(docker volume ls -q --filter "name=devcontainer") 2>/dev/null || true
success "✅ Volumes removidos"

# 4. Limpar cache Docker
step "🧹 Limpando cache Docker..."
docker system prune -af --volumes
success "✅ Cache Docker limpo"

# 5. Limpar cache do projeto
step "🧹 Limpando cache do projeto..."
if [ -d "backend" ]; then
    rm -rf backend/vendor backend/node_modules backend/.env 2>/dev/null || true
    rm -rf backend/bootstrap/cache/* backend/storage/logs/* 2>/dev/null || true
fi

if [ -d "frontend" ]; then
    rm -rf frontend/node_modules frontend/.env 2>/dev/null || true
    rm -rf frontend/dist frontend/.vite 2>/dev/null || true
fi

success "✅ Cache do projeto limpo"

# 6. Verificar se DevContainer está configurado
step "🔍 Verificando configuração do DevContainer..."
if [ ! -f ".devcontainer/devcontainer.json" ]; then
    error "❌ Arquivo .devcontainer/devcontainer.json não encontrado!"
    exit 1
fi

if [ ! -f ".devcontainer/docker-compose.yml" ]; then
    error "❌ Arquivo .devcontainer/docker-compose.yml não encontrado!"
    exit 1
fi

success "✅ Configuração do DevContainer encontrada"

# 7. Rebuild do DevContainer
step "🔨 Iniciando rebuild do DevContainer..."
echo -e "${YELLOW}💡 Agora você precisa:${NC}"
echo -e "${YELLOW}   1. Fechar o VSCode${NC}"
echo -e "${YELLOW}   2. Reabrir o projeto no VSCode${NC}"
echo -e "${YELLOW}   3. Aguardar o DevContainer ser reconstruído${NC}"
echo -e "${YELLOW}   4. O script quick-setup.sh será executado automaticamente${NC}"
echo
echo -e "${BLUE}🚀 Comandos alternativos:${NC}"
echo -e "${GREEN}   # No VSCode (Ctrl+Shift+P):${NC}"
echo -e "${GREEN}   Dev Containers: Rebuild Container${NC}"
echo
echo -e "${GREEN}   # Ou via terminal:${NC}"
echo -e "${GREEN}   code .${NC}"
echo

# 8. Verificar se estamos dentro do DevContainer
if [ -f "/.devcontainer/environment" ]; then
    warn "⚠️ Você está dentro do DevContainer. Saindo para permitir rebuild..."
    echo -e "${YELLOW}💡 Saia do DevContainer e reabra o projeto no VSCode${NC}"
    exit 0
fi

# 9. Tentar abrir no VSCode
if command -v code >/dev/null 2>&1; then
    step "🚀 Abrindo projeto no VSCode..."
    code .
    success "✅ VSCode aberto. Aguarde o DevContainer ser reconstruído."
else
    warn "⚠️ Comando 'code' não encontrado. Abra o VSCode manualmente."
fi

echo
success "🎉 Rebuild iniciado com sucesso!"
echo
echo -e "${BLUE}📋 Próximos passos:${NC}"
echo -e "  1. Aguarde o DevContainer ser reconstruído"
echo -e "  2. O script quick-setup.sh será executado automaticamente"
echo -e "  3. Verifique as URLs e credenciais no final do setup"
echo
echo -e "${BLUE}🔧 Se houver problemas:${NC}"
echo -e "  • Execute: ${GREEN}bash .devcontainer/scripts/troubleshoot.sh${NC}"
echo -e "  • Verifique logs: ${GREEN}docker logs [container-name]${NC}"
echo -e "  • Rebuild manual: ${GREEN}Ctrl+Shift+P > Dev Containers: Rebuild Container${NC}" 