#!/bin/bash

echo "🔍 Diagnóstico do Ambiente Docker"
echo "================================="

# Cores para output
RED='\033[0;31m'
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
        "ERROR")
            echo -e "${RED}❌ $message${NC}"
            ;;
        "INFO")
            echo -e "${BLUE}ℹ️  $message${NC}"
            ;;
    esac
}

# 1. Verificar se o Docker está rodando
echo ""
print_status "INFO" "Verificando Docker daemon..."
if docker info > /dev/null 2>&1; then
    print_status "OK" "Docker está rodando"
    DOCKER_VERSION=$(docker --version)
    print_status "INFO" "Versão: $DOCKER_VERSION"
else
    print_status "ERROR" "Docker não está rodando"
    echo "💡 Solução: sudo systemctl start docker"
    exit 1
fi

# 2. Verificar docker compose
echo ""
print_status "INFO" "Verificando docker compose..."
if docker compose version > /dev/null 2>&1; then
    print_status "OK" "docker compose está disponível"
    DOCKER_COMPOSE_VERSION=$(docker compose version --short)
    print_status "INFO" "Versão: $DOCKER_COMPOSE_VERSION"
else
    print_status "ERROR" "docker compose não está disponível"
    echo "💡 Solução: sudo apt-get install docker-compose-plugin"
fi

# 3. Verificar docker-compose (legacy)
echo ""
print_status "INFO" "Verificando docker-compose (legacy)..."
if command -v docker-compose > /dev/null 2>&1; then
    print_status "OK" "docker-compose (legacy) está disponível"
    DOCKER_COMPOSE_LEGACY_VERSION=$(docker-compose --version)
    print_status "INFO" "Versão: $DOCKER_COMPOSE_LEGACY_VERSION"
else
    print_status "WARN" "docker-compose (legacy) não está disponível"
    echo "💡 Isso é normal em versões mais recentes do Docker"
fi

# 4. Verificar buildx
echo ""
print_status "INFO" "Verificando buildx..."
if docker buildx version > /dev/null 2>&1; then
    print_status "OK" "buildx está disponível"
    BUILDX_VERSION=$(docker buildx version)
    print_status "INFO" "Versão: $BUILDX_VERSION"
    
    # Verificar builders disponíveis
    echo "🔍 Builders disponíveis:"
    docker buildx ls
else
    print_status "ERROR" "buildx não está disponível"
    echo "💡 Solução: docker buildx create --name default --use"
fi

# 5. Verificar permissões do usuário
echo ""
print_status "INFO" "Verificando permissões do usuário..."
if groups $USER | grep -q docker; then
    print_status "OK" "Usuário $USER está no grupo docker"
else
    print_status "WARN" "Usuário $USER não está no grupo docker"
    echo "💡 Solução: sudo usermod -aG docker $USER"
    echo "💡 Depois: newgrp docker"
fi

# 6. Verificar variáveis de ambiente
echo ""
print_status "INFO" "Verificando variáveis de ambiente..."
if [ "$DOCKER_BUILDKIT" = "1" ]; then
    print_status "OK" "DOCKER_BUILDKIT=1 está configurado"
else
    print_status "WARN" "DOCKER_BUILDKIT não está configurado"
    echo "💡 Solução: export DOCKER_BUILDKIT=1"
fi

if [ "$COMPOSE_DOCKER_CLI_BUILD" = "1" ]; then
    print_status "OK" "COMPOSE_DOCKER_CLI_BUILD=1 está configurado"
else
    print_status "WARN" "COMPOSE_DOCKER_CLI_BUILD não está configurado"
    echo "💡 Solução: export COMPOSE_DOCKER_CLI_BUILD=1"
fi

# 7. Verificar arquivos de configuração do devcontainer
echo ""
print_status "INFO" "Verificando arquivos de configuração..."
if [ -f ".devcontainer/devcontainer.json" ]; then
    print_status "OK" "devcontainer.json encontrado"
else
    print_status "ERROR" "devcontainer.json não encontrado"
fi

if [ -f ".devcontainer/docker-compose.yml" ]; then
    print_status "OK" "docker-compose.yml encontrado"
else
    print_status "ERROR" "docker-compose.yml não encontrado"
fi

if [ -f ".devcontainer/Dockerfile" ]; then
    print_status "OK" "Dockerfile encontrado"
else
    print_status "ERROR" "Dockerfile não encontrado"
fi

# 8. Verificar portas em uso
echo ""
print_status "INFO" "Verificando portas em uso..."
PORTS=(8000 3000 5200 3310 6400 8110 6410 1030 8030)
for port in "${PORTS[@]}"; do
    if lsof -i :$port > /dev/null 2>&1; then
        print_status "WARN" "Porta $port está em uso"
        lsof -i :$port | head -1
    else
        print_status "OK" "Porta $port está livre"
    fi
done

# 9. Verificar containers existentes
echo ""
print_status "INFO" "Verificando containers existentes..."
if docker ps -a --filter "label=devcontainer.local_folder=/home/tiao/Documentos/projects/github/rei-do-oleo" | grep -q .; then
    print_status "WARN" "Containers do devcontainer encontrados:"
    docker ps -a --filter "label=devcontainer.local_folder=/home/tiao/Documentos/projects/github/rei-do-oleo"
else
    print_status "OK" "Nenhum container do devcontainer encontrado"
fi

# 10. Verificar imagens
echo ""
print_status "INFO" "Verificando imagens Docker..."
if docker images | grep -q "devcontainer"; then
    print_status "INFO" "Imagens do devcontainer encontradas:"
    docker images | grep "devcontainer"
else
    print_status "INFO" "Nenhuma imagem do devcontainer encontrada"
fi

# 11. Resumo e recomendações
echo ""
echo "📋 RESUMO DO DIAGNÓSTICO"
echo "========================="

if docker compose version > /dev/null 2>&1 && docker buildx version > /dev/null 2>&1; then
    print_status "OK" "Ambiente Docker está configurado corretamente"
    echo ""
    echo "🎯 Para abrir o devcontainer:"
    echo "1. Execute: bash .devcontainer/scripts/init-devcontainer.sh"
    echo "2. Abra o VS Code"
    echo "3. Pressione Ctrl+Shift+P"
    echo "4. Digite 'Dev Containers: Reopen in Container'"
else
    print_status "ERROR" "Ambiente Docker tem problemas que precisam ser resolvidos"
    echo ""
    echo "🔧 Comandos para resolver problemas:"
    echo "1. Instalar docker-compose-plugin: sudo apt-get install docker-compose-plugin"
    echo "2. Configurar buildx: docker buildx create --name default --use"
    echo "3. Adicionar usuário ao grupo docker: sudo usermod -aG docker $USER"
    echo "4. Configurar variáveis: export DOCKER_BUILDKIT=1 && export COMPOSE_DOCKER_CLI_BUILD=1"
fi

echo ""
echo "📚 Para mais informações, consulte:"
echo "- .devcontainer/TROUBLESHOOTING.md"
echo "- .devcontainer/BUILD-GUIDE.md"
