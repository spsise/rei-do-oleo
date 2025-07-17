#!/bin/bash

# Script para fazer deploy na Hostinger via Git Hooks
# Execute este script localmente para fazer push e verificar o deploy

set -e

echo "🚀 Iniciando deploy para Hostinger..."
echo "Branch: hostinger-hom"
echo "API: api-hom.virtualt.com.br"
echo "Frontend: app-hom.virtualt.com.br"

# Verificar se estamos na branch correta
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "hostinger-hom" ]; then
    echo "❌ Erro: Você deve estar na branch 'hostinger-hom'"
    echo "Execute: git checkout hostinger-hom"
    exit 1
fi

# Verificar se há mudanças para commitar
if [ -n "$(git status --porcelain)" ]; then
    echo "📝 Há mudanças não commitadas. Deseja fazer commit?"
    read -p "Digite uma mensagem de commit (ou 'n' para cancelar): " commit_msg

    if [ "$commit_msg" != "n" ] && [ "$commit_msg" != "N" ]; then
        git add .
        git commit -m "$commit_msg"
        echo "✅ Commit realizado"
    else
        echo "❌ Deploy cancelado"
        exit 1
    fi
fi

# Fazer push para o repositório
echo "📤 Fazendo push para origin/hostinger-hom..."
git push origin hostinger-hom

echo "✅ Push realizado com sucesso!"

# Aguardar um pouco para o deploy processar
echo "⏳ Aguardando processamento do deploy..."
sleep 10

# Verificar status dos subdomínios
echo ""
echo "🔍 Verificando status dos subdomínios..."

# Verificar API
echo "📡 Verificando API (api-hom.virtualt.com.br):"
if curl -s -I https://api-hom.virtualt.com.br | grep -q "200\|301\|302"; then
    echo "✅ API respondendo"
else
    echo "❌ API não responde"
fi

# Verificar Frontend
echo "🌐 Verificando Frontend (app-hom.virtualt.com.br):"
if curl -s -I https://app-hom.virtualt.com.br | grep -q "200\|301\|302"; then
    echo "✅ Frontend respondendo"
else
    echo "❌ Frontend não responde"
fi

echo ""
echo "🎯 PRÓXIMOS PASSOS:"
echo "1. Se os subdomínios não responderem, verifique:"
echo "   - Se o Git Hook está configurado no servidor"
echo "   - Se as variáveis de ambiente estão configuradas"
echo "   - Se o banco de dados está acessível"
echo ""
echo "2. Para verificar logs do deploy:"
echo "   ssh usuario@servidor 'tail -f /home/usuario/deploy.log'"
echo ""
echo "3. Para verificar estrutura dos diretórios:"
echo "   ssh usuario@servidor './check-subdomains.sh'"
echo ""
echo "4. Para deploy manual no servidor:"
echo "   ssh usuario@servidor './deploy.sh'"

echo ""
echo "🎉 Deploy iniciado! Verifique os subdomínios em alguns minutos."
