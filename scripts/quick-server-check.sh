#!/bin/bash

# Script de verificação rápida para servidor Hostinger
# Execute este script no servidor via SSH

echo "🔍 Verificação Rápida - Servidor Hostinger"
echo "=========================================="

# Configurações
PROJECT_ROOT="/home/$(whoami)"
API_DIR="$PROJECT_ROOT/api-hom.virtualt.com.br"
FRONTEND_DIR="$PROJECT_ROOT/app-hom.virtualt.com.br"

echo ""
echo "📁 1. Verificando estrutura de diretórios..."

if [ -d "$API_DIR" ]; then
    echo "✅ Diretório da API existe: $API_DIR"
    echo "   Conteúdo:"
    ls -la "$API_DIR" | head -5
else
    echo "❌ Diretório da API não existe: $API_DIR"
fi

if [ -d "$FRONTEND_DIR" ]; then
    echo "✅ Diretório do Frontend existe: $FRONTEND_DIR"
    echo "   Conteúdo:"
    ls -la "$FRONTEND_DIR" | head -5
else
    echo "❌ Diretório do Frontend não existe: $FRONTEND_DIR"
fi

echo ""
echo "🔧 2. Verificando Git Hook..."

if [ -f "$PROJECT_ROOT/.git/hooks/post-receive" ]; then
    echo "✅ Git Hook existe"
    if [ -x "$PROJECT_ROOT/.git/hooks/post-receive" ]; then
        echo "✅ Git Hook é executável"
    else
        echo "❌ Git Hook não é executável"
        echo "   Execute: chmod +x $PROJECT_ROOT/.git/hooks/post-receive"
    fi
else
    echo "❌ Git Hook não existe"
    echo "   Execute: ./setup-git-hooks-subdomains.sh"
fi

echo ""
echo "📋 3. Verificando configuração Git..."

cd "$PROJECT_ROOT"
if [ -d ".git" ]; then
    echo "✅ Repositório Git existe"
    echo "   Remote:"
    git remote -v 2>/dev/null || echo "   Sem remote configurado"
    echo "   Branches:"
    git branch -a 2>/dev/null || echo "   Sem branches encontradas"
else
    echo "❌ Repositório Git não existe"
    echo "   Execute: git init"
fi

echo ""
echo "🔐 4. Verificando arquivos de configuração..."

if [ -f "$API_DIR/.env" ]; then
    echo "✅ .env da API existe"
else
    echo "❌ .env da API não existe"
    echo "   Execute: cd $API_DIR && cp .env.example .env"
fi

if [ -f "$API_DIR/index.php" ]; then
    echo "✅ index.php da API existe"
else
    echo "❌ index.php da API não existe"
fi

if [ -f "$FRONTEND_DIR/index.html" ]; then
    echo "✅ index.html do Frontend existe"
else
    echo "❌ index.html do Frontend não existe"
fi

echo ""
echo "📄 5. Verificando arquivos .htaccess..."

if [ -f "$API_DIR/.htaccess" ]; then
    echo "✅ .htaccess da API existe"
else
    echo "❌ .htaccess da API não existe"
fi

if [ -f "$FRONTEND_DIR/.htaccess" ]; then
    echo "✅ .htaccess do Frontend existe"
else
    echo "❌ .htaccess do Frontend não existe"
fi

echo ""
echo "🔒 6. Verificando permissões..."

if [ -d "$API_DIR/storage" ]; then
    PERM=$(stat -c "%a" "$API_DIR/storage")
    if [ "$PERM" = "755" ]; then
        echo "✅ Permissões do storage da API corretas (755)"
    else
        echo "⚠️ Permissões do storage da API: $PERM (deveria ser 755)"
    fi
fi

if [ -d "$FRONTEND_DIR" ]; then
    PERM=$(stat -c "%a" "$FRONTEND_DIR")
    if [ "$PERM" = "755" ]; then
        echo "✅ Permissões do Frontend corretas (755)"
    else
        echo "⚠️ Permissões do Frontend: $PERM (deveria ser 755)"
    fi
fi

echo ""
echo "📊 7. Verificando logs..."

if [ -f "$PROJECT_ROOT/deploy.log" ]; then
    echo "✅ Log de deploy existe"
    echo "   Últimas linhas:"
    tail -3 "$PROJECT_ROOT/deploy.log"
else
    echo "❌ Log de deploy não existe"
fi

if [ -f "$API_DIR/storage/logs/laravel.log" ]; then
    echo "✅ Log do Laravel existe"
    echo "   Últimas linhas:"
    tail -3 "$API_DIR/storage/logs/laravel.log"
else
    echo "❌ Log do Laravel não existe"
fi

echo ""
echo "🌐 8. Testando conectividade..."

echo "📡 API (api-hom.virtualt.com.br):"
API_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://api-hom.virtualt.com.br)
case $API_STATUS in
    200) echo "✅ API funcionando (HTTP 200)" ;;
    301|302) echo "⚠️ API redirecionando (HTTP $API_STATUS)" ;;
    403) echo "❌ API com erro de permissão (HTTP 403)" ;;
    404) echo "❌ API não encontrada (HTTP 404)" ;;
    500) echo "❌ Erro interno do servidor (HTTP 500)" ;;
    *) echo "❌ API não responde (HTTP $API_STATUS)" ;;
esac

echo "🌐 Frontend (app-hom.virtualt.com.br):"
FRONTEND_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://app-hom.virtualt.com.br)
case $FRONTEND_STATUS in
    200) echo "✅ Frontend funcionando (HTTP 200)" ;;
    301|302) echo "⚠️ Frontend redirecionando (HTTP $FRONTEND_STATUS)" ;;
    403) echo "❌ Frontend com erro de permissão (HTTP 403)" ;;
    404) echo "❌ Frontend não encontrado (HTTP 404)" ;;
    500) echo "❌ Erro interno do servidor (HTTP 500)" ;;
    *) echo "❌ Frontend não responde (HTTP $FRONTEND_STATUS)" ;;
esac

echo ""
echo "🎯 RESUMO E PRÓXIMOS PASSOS:"
echo ""

if [ "$API_STATUS" = "200" ] && [ "$FRONTEND_STATUS" = "200" ]; then
    echo "🎉 TUDO FUNCIONANDO! Deploy realizado com sucesso!"
else
    echo "⚠️ PROBLEMAS DETECTADOS:"

    if [ ! -f "$PROJECT_ROOT/.git/hooks/post-receive" ]; then
        echo "   - Git Hook não configurado"
        echo "     Execute: ./setup-git-hooks-subdomains.sh"
    fi

    if [ ! -f "$API_DIR/.env" ]; then
        echo "   - .env da API não configurado"
        echo "     Execute: cd $API_DIR && cp .env.example .env"
    fi

    if [ ! -f "$API_DIR/index.php" ] || [ ! -f "$FRONTEND_DIR/index.html" ]; then
        echo "   - Deploy não executado"
        echo "     Execute: git pull origin hostinger-hom"
    fi

    echo ""
    echo "🔧 COMANDOS PARA CORRIGIR:"
    echo "1. Configurar Git Hook: ./setup-git-hooks-subdomains.sh"
    echo "2. Configurar .env: cd $API_DIR && cp .env.example .env"
    echo "3. Fazer deploy: git pull origin hostinger-hom"
    echo "4. Configurar permissões: chmod -R 755 $API_DIR $FRONTEND_DIR"
fi

echo ""
echo "📞 Para mais detalhes, execute: ./diagnose-deploy.sh"
