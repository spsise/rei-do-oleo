#!/bin/bash

# 🐳 Build DevContainer Script - Rei do Óleo
# Script para buildar o DevContainer com logs detalhados

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
log() { echo -e "${GREEN}[BUILD]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${PURPLE}[SUCCESS]${NC} $1"; }
step() { echo -e "${CYAN}[STEP]${NC} $1"; }

# Banner
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║           🐳 BUILD DEV CONTAINER - REI DO ÓLEO           ║
║           Build com Logs Detalhados                      ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Verificar se estamos no diretório correto
if [ ! -f "docker-compose.yml" ]; then
    error "Este script deve ser executado no diretório .devcontainer"
    exit 1
fi

# Função para limpar containers antigos
cleanup_containers() {
    step "🧹 Limpando containers antigos..."
    
    # Parar e remover containers existentes
    if docker compose ps -q | grep -q .; then
        log "Parando containers existentes..."
        docker compose down --remove-orphans
    fi
    
    # Remover containers parados
    if docker ps -aq | grep -q .; then
        log "Removendo containers parados..."
        docker container prune -f
    fi
    
    success "✅ Containers limpos"
}

# Função para limpar cache do Docker
cleanup_cache() {
    step "🗑️ Limpando cache do Docker..."
    
    read -p "Deseja limpar todo o cache do Docker? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        log "Limpando imagens, volumes e cache..."
        docker system prune -af
        docker volume prune -f
        docker network prune -f
        success "✅ Cache limpo"
    else
        warn "Pulando limpeza de cache"
    fi
}

# Função para buildar o container
build_container() {
    step "🔨 Iniciando build do DevContainer..."
    
    echo -e "${BLUE}📋 Configurações do build:${NC}"
    echo -e "  • Arquivo: Dockerfile"
    echo -e "  • Serviço: devcontainer"
    echo -e "  • Cache: Desabilitado (--no-cache)"
    echo -e "  • Logs: Detalhados (--progress=plain)"
    echo
    
    # Iniciar build com logs detalhados
    log "Iniciando build..."
    echo -e "${YELLOW}⏳ O build pode demorar 5-10 minutos...${NC}"
    echo -e "${YELLOW}📺 Acompanhe o progresso abaixo:${NC}"
    echo
    
    # Build com logs detalhados
    docker compose build --no-cache --progress=plain devcontainer
    
    if [ $? -eq 0 ]; then
        success "✅ Build concluído com sucesso!"
    else
        error "❌ Build falhou!"
        exit 1
    fi
}

# Função para testar o container
test_container() {
    step "🧪 Testando o container..."
    
    log "Iniciando serviços..."
    docker compose up -d
    
    # Aguardar serviços ficarem prontos
    log "Aguardando serviços ficarem prontos..."
    sleep 10
    
    # Verificar status dos serviços
    log "Verificando status dos serviços..."
    docker compose ps
    
    success "✅ Container testado com sucesso!"
}

# Função para mostrar próximos passos
show_next_steps() {
    step "🎯 Próximos passos..."
    echo
    echo -e "${BLUE}🚀 Para abrir no VSCode:${NC}"
    echo -e "  1. Abra o VSCode"
    echo -e "  2. Pressione ${GREEN}Ctrl+Shift+P${NC}"
    echo -e "  3. Digite: ${GREEN}Dev Containers: Open Folder in Container${NC}"
    echo -e "  4. Selecione a pasta do projeto"
    echo
    echo -e "${BLUE}🔧 Comandos úteis:${NC}"
    echo -e "  • Ver logs: ${GREEN}docker compose logs -f devcontainer${NC}"
    echo -e "  • Parar: ${GREEN}docker compose down${NC}"
    echo -e "  • Status: ${GREEN}docker compose ps${NC}"
    echo -e "  • Diagnóstico: ${GREEN}.devcontainer/scripts/troubleshoot.sh${NC}"
    echo
    echo -e "${BLUE}📊 URLs disponíveis:${NC}"
    echo -e "  • Laravel API: ${GREEN}http://localhost:8000${NC}"
    echo -e "  • React Frontend: ${GREEN}http://localhost:3000${NC}"
    echo -e "  • phpMyAdmin: ${GREEN}http://localhost:8080${NC}"
    echo -e "  • Redis Commander: ${GREEN}http://localhost:6380${NC}"
}

# Menu principal
show_menu() {
    echo -e "${BLUE}Escolha uma opção:${NC}"
    echo -e "  1) ${GREEN}Build completo (limpar + buildar)${NC}"
    echo -e "  2) ${GREEN}Build rápido (apenas buildar)${NC}"
    echo -e "  3) ${GREEN}Apenas limpar containers${NC}"
    echo -e "  4) ${GREEN}Testar container existente${NC}"
    echo -e "  5) ${GREEN}Sair${NC}"
    echo
}

# Execução principal
main() {
    case $1 in
        "1"|"full")
            cleanup_containers
            cleanup_cache
            build_container
            test_container
            show_next_steps
            ;;
        "2"|"quick")
            build_container
            test_container
            show_next_steps
            ;;
        "3"|"clean")
            cleanup_containers
            cleanup_cache
            ;;
        "4"|"test")
            test_container
            ;;
        "5"|"exit")
            echo "Saindo..."
            exit 0
            ;;
        *)
            show_menu
            read -p "Digite sua opção (1-5): " choice
            main $choice
            ;;
    esac
}

# Executar função principal
main $1 