#!/bin/bash

echo "🚀 Inicializando Dev Container Environment"
echo "========================================="

# 1. Verificar se o Docker está rodando
echo "📋 Verificando Docker..."
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Inicie o Docker primeiro."
    exit 1
fi
echo "✅ Docker está rodando"

# 2. Verificar versão do Docker
echo "🔍 Verificando versão do Docker..."
DOCKER_VERSION=$(docker --version)
echo "📦 Docker: $DOCKER_VERSION"

# 3. Verificar se docker compose está disponível
echo "🔍 Verificando docker compose..."
if docker compose version > /dev/null 2>&1; then
    echo "✅ docker compose está disponível"
    DOCKER_COMPOSE_VERSION=$(docker compose version --short)
    echo "📦 Docker Compose: $DOCKER_COMPOSE_VERSION"
elif command -v docker-compose > /dev/null 2>&1; then
    echo "✅ docker-compose está disponível"
    DOCKER_COMPOSE_VERSION=$(docker-compose --version)
    echo "📦 Docker Compose: $DOCKER_COMPOSE_VERSION"
else
    echo "❌ docker compose não está disponível"
    echo "💡 Tentando instalar docker compose..."
    
    # Tentar instalar docker compose se não estiver disponível
    if command -v apt-get > /dev/null 2>&1; then
        sudo apt-get update
        sudo apt-get install -y docker-compose-plugin
    elif command -v yum > /dev/null 2>&1; then
        sudo yum install -y docker-compose-plugin
    elif command -v dnf > /dev/null 2>&1; then
        sudo dnf install -y docker-compose-plugin
    fi
    
    # Verificar novamente
    if docker compose version > /dev/null 2>&1; then
        echo "✅ docker compose instalado com sucesso"
    elif command -v docker-compose > /dev/null 2>&1; then
        echo "✅ docker-compose instalado com sucesso"
    else
        echo "❌ Falha ao instalar docker compose"
        exit 1
    fi
fi

# 4. Verificar e configurar buildx
echo "🔍 Verificando buildx..."
if docker buildx version > /dev/null 2>&1; then
    echo "✅ buildx está disponível"
else
    echo "⚠️  buildx não está disponível, configurando..."
    
    # Criar builder padrão se não existir
    if ! docker buildx inspect default > /dev/null 2>&1; then
        echo "🔧 Criando builder padrão..."
        docker buildx create --name default --use
    fi
    
    # Verificar novamente
    if docker buildx version > /dev/null 2>&1; then
        echo "✅ buildx configurado com sucesso"
    else
        echo "⚠️  buildx não pôde ser configurado, mas continuando..."
    fi
fi

# 5. Parar containers existentes
echo "🛑 Parando containers existentes..."
if command -v docker-compose > /dev/null 2>&1; then
    docker-compose -f .devcontainer/docker-compose.yml down --volumes --remove-orphans 2>/dev/null || true
else
    docker compose -f .devcontainer/docker-compose.yml down --volumes --remove-orphans 2>/dev/null || true
fi

# 6. Limpar recursos não utilizados
echo "🧹 Limpando recursos não utilizados..."
docker system prune -f 2>/dev/null || true

# 7. Verificar portas em uso
echo "🔍 Verificando portas em uso..."
PORTS=(8000 3000 5200 3310 6400 8110 6410 1030 8030)
for port in "${PORTS[@]}"; do
    if lsof -i :$port > /dev/null 2>&1; then
        echo "⚠️  Porta $port está em uso"
        lsof -i :$port
    else
        echo "✅ Porta $port está livre"
    fi
done

# 8. Verificar arquivos de configuração
echo "📁 Verificando arquivos de configuração..."
REQUIRED_FILES=(
    ".devcontainer/Dockerfile"
    ".devcontainer/docker-compose.yml"
    ".devcontainer/devcontainer.json"
)

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file encontrado"
    else
        echo "❌ $file não encontrado"
        exit 1
    fi
done

# 9. Verificar permissões
echo "🔐 Verificando permissões..."
if [ ! -r ".devcontainer/docker-compose.yml" ]; then
    echo "❌ Sem permissão de leitura no docker-compose.yml"
    exit 1
fi

# 10. Configurar variáveis de ambiente
echo "🔧 Configurando variáveis de ambiente..."
export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1

# 11. Tentar fazer build da imagem
echo "🏗️  Fazendo build da imagem..."
if command -v docker-compose > /dev/null 2>&1; then
    if docker-compose -f .devcontainer/docker-compose.yml build --no-cache; then
        echo "✅ Build da imagem concluído com sucesso"
    else
        echo "❌ Falha no build da imagem"
        echo "💡 Tentando build sem cache..."
        if docker-compose -f .devcontainer/docker-compose.yml build; then
            echo "✅ Build da imagem concluído"
        else
            echo "❌ Falha no build da imagem"
            exit 1
        fi
    fi
else
    if docker compose -f .devcontainer/docker-compose.yml build --no-cache; then
        echo "✅ Build da imagem concluído com sucesso"
    else
        echo "❌ Falha no build da imagem"
        echo "💡 Tentando build sem cache..."
        if docker compose -f .devcontainer/docker-compose.yml build; then
            echo "✅ Build da imagem concluído"
        else
            echo "❌ Falha no build da imagem"
            exit 1
        fi
    fi
fi

# 12. Iniciar containers
echo "🚀 Iniciando containers..."
if command -v docker-compose > /dev/null 2>&1; then
    if docker-compose -f .devcontainer/docker-compose.yml up -d; then
        echo "✅ Containers iniciados com sucesso"
    else
        echo "❌ Falha ao iniciar containers"
        exit 1
    fi
else
    if docker compose -f .devcontainer/docker-compose.yml up -d; then
        echo "✅ Containers iniciados com sucesso"
    else
        echo "❌ Falha ao iniciar containers"
        exit 1
    fi
fi

# 13. Verificar status dos containers
echo "📊 Verificando status dos containers..."
sleep 5
if command -v docker-compose > /dev/null 2>&1; then
    docker-compose -f .devcontainer/docker-compose.yml ps
else
    docker compose -f .devcontainer/docker-compose.yml ps
fi

echo ""
echo "🎉 Dev Container Environment inicializado com sucesso!"
echo ""
echo "🎯 Próximos passos:"
echo "1. Abra o VS Code"
echo "2. Pressione Ctrl+Shift+P"
echo "3. Digite 'Dev Containers: Reopen in Container'"
echo "4. Selecione a configuração do Dev Container"
echo ""
echo "🔧 Se houver problemas:"
if command -v docker-compose > /dev/null 2>&1; then
    echo "- Execute: docker-compose -f .devcontainer/docker-compose.yml logs"
    echo "- Execute: docker-compose -f .devcontainer/docker-compose.yml down"
else
    echo "- Execute: docker compose -f .devcontainer/docker-compose.yml logs"
    echo "- Execute: docker compose -f .devcontainer/docker-compose.yml down"
fi
echo "- Execute: bash .devcontainer/scripts/init-devcontainer.sh"
