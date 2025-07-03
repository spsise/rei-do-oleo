#!/bin/bash

# Script para iniciar o servidor de desenvolvimento Vite
# Otimizado para WSL2 e devcontainer

set -e

echo "🚀 Iniciando servidor de desenvolvimento Vite..."

# Navegar para o diretório do frontend
cd /workspace/frontend

# Verificar se o node_modules existe
if [ ! -d "node_modules" ]; then
    echo "📦 Instalando dependências..."
    npm install
fi

# Parar qualquer processo Vite existente
echo "🛑 Parando processos Vite existentes..."
pkill -f "vite" || true

# Aguardar um momento
sleep 2

# Iniciar o Vite com configurações para WSL2
echo "⚡ Iniciando Vite com configurações para WSL2..."
echo "📍 URLs disponíveis:"
echo "   - Local: http://localhost:5173"
echo "   - Network: http://172.25.0.7:5173"
echo "   - WSL2: http://localhost:5173 (via port forwarding)"
echo ""

# Iniciar o servidor
exec npm run dev -- --host 0.0.0.0 --port 5173
