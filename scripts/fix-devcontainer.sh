#!/bin/bash
# 🔧 Script de Correção Dev Container - Rei do Óleo
# Resolve problemas comuns de inicialização do Dev Container

set -e

echo "🔧 Iniciando correção do Dev Container..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log colorido
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# 1. Parar containers existentes do projeto
log_info "Parando containers existentes..."
docker compose -f .devcontainer/docker-compose.yml down --remove-orphans 2>/dev/null || true
log_success "Containers parados"

# 2. Limpar recursos Docker não utilizados
log_info "Limpando recursos Docker não utilizados..."
docker system prune -f
docker network prune -f
log_success "Recursos Docker limpos"

# 3. Verificar conflitos de rede
log_info "Verificando conflitos de rede..."
echo "Redes atuais e suas subnets:"
for network in $(docker network ls -q); do 
    name=$(docker network inspect $network | grep '"Name"' | head -1 | cut -d'"' -f4)
    subnet=$(docker network inspect $network | grep '"Subnet"' | head -1 | cut -d'"' -f4 2>/dev/null || echo "N/A")
    if [ "$subnet" != "N/A" ]; then
        echo "  - $name: $subnet"
    fi
done

# 4. Remover redes conflitantes específicas
log_info "Removendo redes conflitantes..."
for network_pattern in "rei" "devcontainer"; do
    conflicting_networks=$(docker network ls --filter name=$network_pattern --format "{{.ID}}" 2>/dev/null || true)
    if [ ! -z "$conflicting_networks" ]; then
        echo $conflicting_networks | xargs -r docker network rm 2>/dev/null || true
        log_success "Redes conflitantes removidas: $network_pattern"
    fi
done

# 5. Verificar se Docker daemon está rodando
log_info "Verificando Docker daemon..."
if ! docker info >/dev/null 2>&1; then
    log_error "Docker daemon não está rodando. Inicie o Docker primeiro."
    exit 1
fi
log_success "Docker daemon está funcionando"

# 6. Verificar espaço em disco
log_info "Verificando espaço em disco..."
disk_usage=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $disk_usage -gt 90 ]; then
    log_warning "Espaço em disco baixo ($disk_usage% usado)"
    log_info "Limpando imagens Docker não utilizadas..."
    docker image prune -a -f
fi

# 7. Validar configuração do docker-compose
log_info "Validando configuração do docker-compose..."
if docker compose -f .devcontainer/docker-compose.yml config >/dev/null 2>&1; then
    log_success "Configuração do docker-compose válida"
else
    log_error "Erro na configuração do docker-compose"
    docker compose -f .devcontainer/docker-compose.yml config
    exit 1
fi

# 8. Verificar arquivos necessários
log_info "Verificando arquivos de configuração..."

required_files=(
    ".devcontainer/docker-compose.yml"
    ".devcontainer/Dockerfile"
    ".devcontainer/devcontainer.json"
    ".devcontainer/redis/redis.conf"
    ".devcontainer/mysql-init/01-create-databases.sql"
)

for file in "${required_files[@]}"; do
    if [ ! -f "$file" ]; then
        log_error "Arquivo obrigatório não encontrado: $file"
        exit 1
    fi
done
log_success "Todos os arquivos necessários encontrados"

# 9. Testar conectividade de rede
log_info "Testando conectividade de rede..."
if ping -c 1 8.8.8.8 >/dev/null 2>&1; then
    log_success "Conectividade de internet OK"
else
    log_warning "Problemas de conectividade de internet detectados"
fi

# 10. Mostrar informações do sistema
log_info "Informações do sistema:"
echo "  - Docker version: $(docker --version)"
echo "  - Docker Compose version: $(docker compose version)"
echo "  - Espaço disponível: $(df -h / | tail -1 | awk '{print $4}')"
echo "  - Memória disponível: $(free -h | grep '^Mem:' | awk '{print $7}')"

# 11. Criar rede manualmente se necessário
log_info "Criando rede personalizada..."
if ! docker network ls | grep -q "devcontainer_reidooleo-dev"; then
    docker network create \
        --driver bridge \
        --subnet=172.25.0.0/16 \
        devcontainer_reidooleo-dev 2>/dev/null || true
    log_success "Rede criada com sucesso"
else
    log_info "Rede já existe"
fi

echo ""
log_success "🎉 Correção concluída com sucesso!"
echo ""
log_info "Agora você pode tentar abrir o Dev Container novamente:"
log_info "1. No VS Code, pressione Ctrl+Shift+P"
log_info "2. Digite: 'Dev Containers: Reopen in Container'"
log_info "3. Aguarde a inicialização completa"
echo ""
log_info "Se ainda houver problemas, execute:"
log_info "  docker compose -f .devcontainer/docker-compose.yml up -d"
echo "" 