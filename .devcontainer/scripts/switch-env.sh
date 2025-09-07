#!/bin/bash

# 🔄 Script para Alternar Entre Ambientes
# Este script facilita a alternância entre ambiente completo e simplificado

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Função para log
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Verificar se estamos no diretório correto
if [ ! -d ".devcontainer" ]; then
    error "Este script deve ser executado na raiz do projeto"
    exit 1
fi

# Função para mostrar ajuda
show_help() {
    echo -e "${PURPLE}🔄 Script de Alternância de Ambiente${NC}"
    echo ""
    echo "Uso: $0 [OPÇÃO]"
    echo ""
    echo "Opções:"
    echo "  simple     - Alternar para ambiente simplificado"
    echo "  full       - Alternar para ambiente completo"
    echo "  status     - Mostrar status atual do ambiente"
    echo "  help       - Mostrar esta ajuda"
    echo ""
    echo "Exemplos:"
    echo "  $0 simple    # Usar ambiente simplificado"
    echo "  $0 full      # Usar ambiente completo"
    echo "  $0 status    # Verificar ambiente atual"
}

# Função para verificar status atual
check_status() {
    log "Verificando status atual do ambiente..."
    
    if [ -f ".devcontainer/devcontainer.json" ] && [ -f ".devcontainer/docker-compose.yml" ]; then
        # Verificar qual configuração está ativa
        if grep -q "docker-compose.simple.yml" .devcontainer/devcontainer.json 2>/dev/null; then
            echo -e "  ${GREEN}✅ Ambiente Simplificado Ativo${NC}"
            echo "  - Configuração: devcontainer.simple.json"
            echo "  - Docker Compose: docker-compose.simple.yml"
        elif grep -q "docker-compose.yml" .devcontainer/devcontainer.json 2>/dev/null; then
            echo -e "  ${GREEN}✅ Ambiente Completo Ativo${NC}"
            echo "  - Configuração: devcontainer.json (completo)"
            echo "  - Docker Compose: docker-compose.yml (completo)"
        else
            echo -e "  ${YELLOW}⚠️  Configuração Indefinida${NC}"
        fi
    else
        echo -e "  ${RED}❌ Arquivos de configuração não encontrados${NC}"
    fi
    
    echo ""
    echo "Arquivos disponíveis:"
    ls -la .devcontainer/devcontainer*.json 2>/dev/null || echo "  Nenhum arquivo devcontainer encontrado"
    ls -la .devcontainer/docker-compose*.yml 2>/dev/null || echo "  Nenhum arquivo docker-compose encontrado"
}

# Função para alternar para ambiente simplificado
switch_to_simple() {
    log "Alternando para ambiente simplificado..."
    
    # Fazer backup dos arquivos atuais
    if [ -f ".devcontainer/devcontainer.json" ]; then
        cp .devcontainer/devcontainer.json .devcontainer/devcontainer.backup.json
        log "Backup criado: devcontainer.backup.json"
    fi
    
    if [ -f ".devcontainer/docker-compose.yml" ]; then
        cp .devcontainer/docker-compose.yml .devcontainer/docker-compose.backup.yml
        log "Backup criado: docker-compose.backup.yml"
    fi
    
    # Renomear arquivos completos
    if [ -f ".devcontainer/devcontainer.json" ]; then
        mv .devcontainer/devcontainer.json .devcontainer/devcontainer.full.json
        log "Arquivo completo renomeado para: devcontainer.full.json"
    fi
    
    if [ -f ".devcontainer/docker-compose.yml" ]; then
        mv .devcontainer/docker-compose.yml .devcontainer/docker-compose.full.yml
        log "Arquivo completo renomeado para: docker-compose.full.yml"
    fi
    
    # Ativar arquivos simplificados
    if [ -f ".devcontainer/devcontainer.simple.json" ]; then
        mv .devcontainer/devcontainer.simple.json .devcontainer/devcontainer.json
        log "Arquivo simplificado ativado: devcontainer.json"
    else
        error "Arquivo devcontainer.simple.json não encontrado"
        return 1
    fi
    
    if [ -f ".devcontainer/docker-compose.simple.yml" ]; then
        mv .devcontainer/docker-compose.simple.yml .devcontainer/docker-compose.yml
        log "Arquivo simplificado ativado: docker-compose.yml"
    else
        error "Arquivo docker-compose.simple.yml não encontrado"
        return 1
    fi
    
    success "✅ Ambiente simplificado ativado!"
    echo ""
    echo "📋 Próximos passos:"
    echo "  1. Feche o VS Code se estiver aberto"
    echo "  2. Abra o projeto novamente: code ."
    echo "  3. Escolha 'Reopen in Container' quando solicitado"
    echo ""
    echo "🌐 URLs do ambiente simplificado:"
    echo "  - Backend: http://localhost:8000"
    echo "  - Frontend: http://localhost:3000"
    echo "  - phpMyAdmin: http://localhost:8110"
}

# Função para alternar para ambiente completo
switch_to_full() {
    log "Alternando para ambiente completo..."
    
    # Fazer backup dos arquivos atuais
    if [ -f ".devcontainer/devcontainer.json" ]; then
        cp .devcontainer/devcontainer.json .devcontainer/devcontainer.backup.json
        log "Backup criado: devcontainer.backup.json"
    fi
    
    if [ -f ".devcontainer/docker-compose.yml" ]; then
        cp .devcontainer/docker-compose.yml .devcontainer/docker-compose.backup.yml
        log "Backup criado: docker-compose.backup.yml"
    fi
    
    # Renomear arquivos simplificados
    if [ -f ".devcontainer/devcontainer.json" ]; then
        mv .devcontainer/devcontainer.json .devcontainer/devcontainer.simple.json
        log "Arquivo simplificado renomeado para: devcontainer.simple.json"
    fi
    
    if [ -f ".devcontainer/docker-compose.yml" ]; then
        mv .devcontainer/docker-compose.yml .devcontainer/docker-compose.simple.yml
        log "Arquivo simplificado renomeado para: docker-compose.simple.yml"
    fi
    
    # Ativar arquivos completos
    if [ -f ".devcontainer/devcontainer.full.json" ]; then
        mv .devcontainer/devcontainer.full.json .devcontainer/devcontainer.json
        log "Arquivo completo ativado: devcontainer.json"
    else
        error "Arquivo devcontainer.full.json não encontrado"
        return 1
    fi
    
    if [ -f ".devcontainer/docker-compose.full.yml" ]; then
        mv .devcontainer/docker-compose.full.yml .devcontainer/docker-compose.yml
        log "Arquivo completo ativado: docker-compose.yml"
    else
        error "Arquivo docker-compose.full.yml não encontrado"
        return 1
    fi
    
    success "✅ Ambiente completo ativado!"
    echo ""
    echo "📋 Próximos passos:"
    echo "  1. Feche o VS Code se estiver aberto"
    echo "  2. Abra o projeto novamente: code ."
    echo "  3. Escolha 'Reopen in Container' quando solicitado"
    echo ""
    echo "🌐 URLs do ambiente completo:"
    echo "  - Backend: http://localhost:8000"
    echo "  - Frontend: http://localhost:3000"
    echo "  - phpMyAdmin: http://localhost:8110"
    echo "  - MailHog: http://localhost:8030"
    echo "  - Redis Commander: http://localhost:6410"
    echo "  - MinIO: http://localhost:9001"
}

# Processar argumentos
case "${1:-}" in
    "simple")
        switch_to_simple
        ;;
    "full")
        switch_to_full
        ;;
    "status")
        check_status
        ;;
    "help"|"-h"|"--help")
        show_help
        ;;
    "")
        echo -e "${YELLOW}⚠️  Nenhuma opção especificada${NC}"
        echo ""
        show_help
        ;;
    *)
        error "Opção inválida: $1"
        echo ""
        show_help
        exit 1
        ;;
esac

