#!/bin/bash

echo "🔧 Configurando Variáveis de Ambiente do Docker"
echo "==============================================="

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir com cores
print_status() {
    local status=$1
    local message=$2
    case $status in
        "OK")
            echo -e "${GREEN}✅ $message${NC}"
            ;;
        "WARN")
            echo -e "${YELLOW}⚠️  $message${NC}"
            ;;
        "INFO")
            echo -e "${BLUE}ℹ️  $message${NC}"
            ;;
    esac
}

# 1. Verificar se o arquivo .bashrc existe
echo ""
print_status "INFO" "Configurando variáveis de ambiente permanentes..."

# 2. Adicionar variáveis ao .bashrc
BASH_CONFIG_FILE="$HOME/.bashrc"
DOCKER_VARS="# Docker Development Environment Variables
export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1"

# Verificar se as variáveis já existem
if grep -q "DOCKER_BUILDKIT=1" "$BASH_CONFIG_FILE"; then
    print_status "WARN" "Variáveis do Docker já estão configuradas no .bashrc"
else
    echo "" >> "$BASH_CONFIG_FILE"
    echo "$DOCKER_VARS" >> "$BASH_CONFIG_FILE"
    print_status "OK" "Variáveis do Docker adicionadas ao .bashrc"
fi

# 3. Adicionar variáveis ao .zshrc se existir
ZSH_CONFIG_FILE="$HOME/.zshrc"
if [ -f "$ZSH_CONFIG_FILE" ]; then
    if grep -q "DOCKER_BUILDKIT=1" "$ZSH_CONFIG_FILE"; then
        print_status "WARN" "Variáveis do Docker já estão configuradas no .zshrc"
    else
        echo "" >> "$ZSH_CONFIG_FILE"
        echo "$DOCKER_VARS" >> "$ZSH_CONFIG_FILE"
        print_status "OK" "Variáveis do Docker adicionadas ao .zshrc"
    fi
else
    print_status "INFO" "Arquivo .zshrc não encontrado, pulando configuração"
fi

# 4. Configurar variáveis para a sessão atual
export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1

# 5. Verificar se o docker-compose está no PATH
echo ""
print_status "INFO" "Verificando docker-compose..."
if command -v docker-compose > /dev/null 2>&1; then
    print_status "OK" "docker-compose está disponível"
    DOCKER_COMPOSE_VERSION=$(docker-compose --version)
    print_status "INFO" "Versão: $DOCKER_COMPOSE_VERSION"
else
    print_status "WARN" "docker-compose não está disponível"
    echo "💡 Execute: sudo curl -L \"https://github.com/docker/compose/releases/latest/download/docker-compose-\$(uname -s)-\$(uname -m)\" -o /usr/local/bin/docker-compose && sudo chmod +x /usr/local/bin/docker-compose"
fi

# 6. Verificar permissões do usuário
echo ""
print_status "INFO" "Verificando permissões do usuário..."
if groups $USER | grep -q docker; then
    print_status "OK" "Usuário $USER está no grupo docker"
else
    print_status "WARN" "Usuário $USER não está no grupo docker"
    echo "💡 Execute: sudo usermod -aG docker $USER"
    echo "💡 Depois: newgrp docker"
fi

# 7. Criar alias para facilitar o uso
echo ""
print_status "INFO" "Configurando aliases úteis..."

# Adicionar aliases ao .bashrc
ALIASES="# Docker Development Aliases
alias dc='docker-compose'
alias dcb='docker-compose build'
alias dcu='docker-compose up'
alias dcd='docker-compose down'
alias dcr='docker-compose restart'
alias dcl='docker-compose logs'
alias dce='docker-compose exec'"

if grep -q "alias dc='docker-compose'" "$BASH_CONFIG_FILE"; then
    print_status "WARN" "Aliases do Docker já estão configurados no .bashrc"
else
    echo "" >> "$BASH_CONFIG_FILE"
    echo "$ALIASES" >> "$BASH_CONFIG_FILE"
    print_status "OK" "Aliases do Docker adicionados ao .bashrc"
fi

# Adicionar aliases ao .zshrc se existir
if [ -f "$ZSH_CONFIG_FILE" ]; then
    if grep -q "alias dc='docker-compose'" "$ZSH_CONFIG_FILE"; then
        print_status "WARN" "Aliases do Docker já estão configurados no .zshrc"
    else
        echo "" >> "$ZSH_CONFIG_FILE"
        echo "$ALIASES" >> "$ZSH_CONFIG_FILE"
        print_status "OK" "Aliases do Docker adicionados ao .zshrc"
    fi
fi

# 8. Resumo da configuração
echo ""
echo "📋 RESUMO DA CONFIGURAÇÃO"
echo "========================="
print_status "OK" "Variáveis de ambiente configuradas"
print_status "OK" "Aliases úteis adicionados"
echo ""
echo "🎯 Para aplicar as mudanças:"
echo "1. Feche e abra um novo terminal, OU"
echo "2. Execute: source ~/.bashrc"
echo ""
echo "🔧 Comandos úteis disponíveis:"
echo "- dc: docker-compose (atalho)"
echo "- dcb: docker-compose build"
echo "- dcu: docker-compose up"
echo "- dcd: docker-compose down"
echo "- dcr: docker-compose restart"
echo "- dcl: docker-compose logs"
echo "- dce: docker-compose exec"
echo ""
echo "📚 Para mais informações:"
echo "- Execute: bash .devcontainer/scripts/diagnose-docker.sh"
echo "- Execute: bash .devcontainer/scripts/init-devcontainer.sh"



