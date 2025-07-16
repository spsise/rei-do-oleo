#!/bin/bash

# Script para corrigir quebras de linha dos arquivos
# Execute este script no servidor para corrigir o problema

echo "🔧 Corrigindo quebras de linha..."

# Verificar se o arquivo existe
if [ -f "check-git-status.sh" ]; then
    echo "📝 Corrigindo check-git-status.sh..."
    sed -i 's/\r$//' check-git-status.sh
    chmod +x check-git-status.sh
    echo "✅ check-git-status.sh corrigido"
else
    echo "❌ Arquivo check-git-status.sh não encontrado"
fi

if [ -f "setup-git-hooks-subdomains.sh" ]; then
    echo "📝 Corrigindo setup-git-hooks-subdomains.sh..."
    sed -i 's/\r$//' setup-git-hooks-subdomains.sh
    chmod +x setup-git-hooks-subdomains.sh
    echo "✅ setup-git-hooks-subdomains.sh corrigido"
else
    echo "❌ Arquivo setup-git-hooks-subdomains.sh não encontrado"
fi

echo ""
echo "🎉 Correção concluída!"
echo "Agora você pode executar:"
echo "  ./check-git-status.sh"
echo "  ./setup-git-hooks-subdomains.sh"
