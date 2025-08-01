#!/bin/bash

echo "🔧 Diagnóstico e Correção do Dev Container"
echo "=========================================="

# 1. Verificar se o Docker está rodando
echo "📋 Verificando Docker..."
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Inicie o Docker primeiro."
    exit 1
fi
echo "✅ Docker está rodando"

# 2. Parar todos os containers relacionados
echo "🛑 Parando containers existentes..."
docker compose -f .devcontainer/docker-compose.yml down --volumes --remove-orphans 2>/dev/null || true

# 3. Limpar imagens órfãs
echo "🧹 Limpando imagens órfãs..."
docker image prune -f 2>/dev/null || true

# 4. Verificar portas em uso
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

# 5. Verificar arquivos de configuração
echo "📁 Verificando arquivos de configuração..."
if [ ! -f ".devcontainer/Dockerfile" ]; then
    echo "❌ Dockerfile não encontrado"
    exit 1
fi

if [ ! -f ".devcontainer/docker-compose.yml" ]; then
    echo "❌ docker-compose.yml não encontrado"
    exit 1
fi

if [ ! -f ".devcontainer/devcontainer.json" ]; then
    echo "❌ devcontainer.json não encontrado"
    exit 1
fi

echo "✅ Todos os arquivos de configuração encontrados"

# 6. Verificar permissões
echo "🔐 Verificando permissões..."
if [ ! -r ".devcontainer/docker-compose.yml" ]; then
    echo "❌ Sem permissão de leitura no docker-compose.yml"
    exit 1
fi

# 7. Tentar iniciar os containers
echo "🚀 Iniciando containers..."
docker compose -f .devcontainer/docker-compose.yml up -d

# 8. Verificar status
echo "📊 Verificando status dos containers..."
sleep 5
docker compose -f .devcontainer/docker-compose.yml ps

echo "✅ Diagnóstico concluído!"
echo ""
echo "🎯 Próximos passos:"
echo "1. Abra o VS Code"
echo "2. Pressione Ctrl+Shift+P"
echo "3. Digite 'Dev Containers: Reopen in Container'"
echo "4. Selecione a configuração do Dev Container" 