#!/bin/bash

# 🛠️ Script para Corrigir Problemas do DevContainer
# Autor: Sebastião Apolinario
# Data: 2025-01-11

set -e

echo "🔧 Iniciando correção do DevContainer..."

# 🧹 Limpar containers e redes antigas
echo "🧹 Limpando containers e redes antigas..."
docker compose -f .devcontainer/docker-compose.yml down --volumes --remove-orphans 2>/dev/null || true

# Remover rede específica se existir
echo "🗑️ Removendo rede devcontainer_reidooleo-dev se existir..."
docker network rm devcontainer_reidooleo-dev 2>/dev/null || true

# 🧹 Limpar volumes não utilizados
echo "🧹 Limpando volumes não utilizados..."
docker volume prune -f

# 🧹 Limpar imagens não utilizadas
echo "🧹 Limpando imagens não utilizadas..."
docker image prune -f

# 🔄 Recriar rede
echo "🔄 Criando rede devcontainer_reidooleo-dev..."
docker network create devcontainer_reidooleo-dev 2>/dev/null || true

# ✅ Verificar se a rede foi criada
echo "✅ Verificando se a rede foi criada..."
docker network ls | grep devcontainer_reidooleo-dev || echo "❌ Rede não foi criada"

# 🚀 Tentar subir o devcontainer
echo "🚀 Tentando subir o devcontainer..."
docker compose -f .devcontainer/docker-compose.yml up -d

# ✅ Verificar status dos containers
echo "✅ Verificando status dos containers..."
docker compose -f .devcontainer/docker-compose.yml ps

echo "🎉 Correção concluída!"
echo ""
echo "📋 Próximos passos:"
echo "1. Abra o VS Code/Cursor"
echo "2. Pressione Ctrl+Shift+P"
echo "3. Digite 'Dev Containers: Reopen in Container'"
echo "4. Selecione o container 'devcontainer'"
echo ""
echo "🔗 URLs disponíveis após inicialização:"
echo "- Laravel API: http://localhost:8000"
echo "- React Frontend: http://localhost:3000"
echo "- Vite Dev Server: http://localhost:5200"
echo "- phpMyAdmin: http://localhost:8110"
echo "- Redis Commander: http://localhost:6410"
echo "- MailHog: http://localhost:8030"
