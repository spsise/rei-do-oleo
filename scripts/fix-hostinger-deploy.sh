#!/bin/bash

# Script para resolver conflitos e fazer deploy limpo na Hostinger
# Execute este script no servidor via SSH

set -e

echo "🔧 Resolvendo conflitos e fazendo deploy limpo..."
echo "================================================"

# Configurações
PROJECT_ROOT="/home/$(whoami)"
API_DIR="$PROJECT_ROOT/api-hom.virtualt.com.br"
FRONTEND_DIR="$PROJECT_ROOT/app-hom.virtualt.com.br"

echo "📁 Diretório do projeto: $PROJECT_ROOT"

# 1. Limpar arquivos conflitantes
echo ""
echo "🧹 1. Limpando arquivos conflitantes..."
cd "$PROJECT_ROOT"

# Fazer backup do .gitignore existente se necessário
if [ -f ".gitignore" ]; then
    echo "📋 Fazendo backup do .gitignore existente..."
    mv .gitignore .gitignore.backup
fi

# Limpar arquivos que podem causar conflito
echo "🗑️ Removendo arquivos temporários..."
rm -f *.md *.yml *.json *.lock 2>/dev/null || true
rm -rf backend/ frontend/ scripts/ docs/ .github/ docker/ 2>/dev/null || true

# 2. Fazer checkout limpo
echo ""
echo "📥 2. Fazendo checkout limpo..."
git reset --hard HEAD 2>/dev/null || true
git clean -fd 2>/dev/null || true
git checkout hostinger-hom

echo "✅ Checkout realizado com sucesso"

# 3. Verificar se o deploy foi executado
echo ""
echo "🔍 3. Verificando resultado do deploy..."

if [ -d "$API_DIR" ] && [ -f "$API_DIR/index.php" ]; then
    echo "✅ API deployada com sucesso"
    echo "   Arquivos encontrados:"
    ls -la "$API_DIR" | head -5
else
    echo "❌ API não foi deployada"
fi

if [ -d "$FRONTEND_DIR" ] && [ -f "$FRONTEND_DIR/index.html" ]; then
    echo "✅ Frontend deployado com sucesso"
    echo "   Arquivos encontrados:"
    ls -la "$FRONTEND_DIR" | head -5
else
    echo "❌ Frontend não foi deployado"
fi

# 4. Se o deploy não foi executado, fazer manualmente
if [ ! -f "$API_DIR/index.php" ] || [ ! -f "$FRONTEND_DIR/index.html" ]; then
    echo ""
    echo "🚀 4. Executando deploy manual..."

    # Deploy Backend
    if [ -d "backend" ]; then
        echo "🔧 Deployando Laravel API..."
        rm -rf "$API_DIR"/*
        cp -r backend/* "$API_DIR/"

        cd "$API_DIR"

        # Instalar dependências
        composer install --no-dev --optimize-autoloader

        # Configurar ambiente
        if [ ! -f ".env" ]; then
            cp .env.example .env
            php artisan key:generate
        fi

        # Otimizar para produção
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache

        # Executar migrações
        php artisan migrate --force

        # Configurar permissões
        chmod -R 755 storage
        chmod -R 755 bootstrap/cache
        chmod 644 .env

        # Configurar .htaccess
        cat > .htaccess << 'HTACCESS'
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]

Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
HTACCESS

        echo "✅ Backend Laravel configurado"
    fi

    # Deploy Frontend
    if [ -d "frontend" ]; then
        echo "⚛️ Deployando React App..."

        rm -rf "$FRONTEND_DIR"/*

        cd frontend

        # Instalar dependências
        npm ci

        # Build para produção
        npm run build

        # Mover arquivos buildados
        cp -r dist/* "$FRONTEND_DIR/"

        cd ..

        # Configurar .htaccess
        cat > "$FRONTEND_DIR/.htaccess" << 'HTACCESS'
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.html [QSA,L]

Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

<FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 year"
    Header set Cache-Control "public, immutable"
</FilesMatch>
HTACCESS

        # Configurar permissões
        chmod -R 755 "$FRONTEND_DIR"

        echo "✅ Frontend React configurado"
    fi

    # Limpar arquivos temporários
    rm -rf backend/ frontend/ scripts/ docs/ .github/ docker/
    rm -f *.md *.yml *.json *.lock

    echo "🎉 Deploy manual concluído!"
fi

# 5. Verificar resultado final
echo ""
echo "📋 5. Verificação final..."

echo "📡 API (api-hom.virtualt.com.br):"
if [ -f "$API_DIR/index.php" ]; then
    echo "✅ index.php encontrado"
    echo "✅ .env existe: $([ -f "$API_DIR/.env" ] && echo "Sim" || echo "Não")"
    echo "✅ .htaccess existe: $([ -f "$API_DIR/.htaccess" ] && echo "Sim" || echo "Não")"
else
    echo "❌ index.php não encontrado"
fi

echo ""
echo "🌐 Frontend (app-hom.virtualt.com.br):"
if [ -f "$FRONTEND_DIR/index.html" ]; then
    echo "✅ index.html encontrado"
    echo "✅ .htaccess existe: $([ -f "$FRONTEND_DIR/.htaccess" ] && echo "Sim" || echo "Não")"
else
    echo "❌ index.html não encontrado"
fi

# 6. Testar conectividade
echo ""
echo "🌐 6. Testando conectividade..."

echo "📡 API:"
API_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://api-hom.virtualt.com.br)
case $API_STATUS in
    200) echo "✅ API funcionando (HTTP 200)" ;;
    301|302) echo "⚠️ API redirecionando (HTTP $API_STATUS)" ;;
    403) echo "❌ API com erro de permissão (HTTP 403)" ;;
    404) echo "❌ API não encontrada (HTTP 404)" ;;
    500) echo "❌ Erro interno do servidor (HTTP 500)" ;;
    *) echo "❌ API não responde (HTTP $API_STATUS)" ;;
esac

echo "🌐 Frontend:"
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
echo "🎯 PRÓXIMOS PASSOS:"
echo ""
if [ "$API_STATUS" != "200" ] || [ "$FRONTEND_STATUS" != "200" ]; then
    echo "⚠️ PROBLEMAS DETECTADOS:"
    echo "1. Configure as variáveis de ambiente:"
    echo "   cd $API_DIR"
    echo "   cp .env.example .env"
    echo "   nano .env"
    echo ""
    echo "2. Configure o banco de dados no .env:"
    echo "   DB_HOST=localhost"
    echo "   DB_DATABASE=seu_banco"
    echo "   DB_USERNAME=seu_usuario"
    echo "   DB_PASSWORD=sua_senha"
    echo ""
    echo "3. Execute migrações:"
    echo "   cd $API_DIR"
    echo "   php artisan migrate --force"
    echo ""
    echo "4. Configure permissões:"
    echo "   chmod -R 755 $API_DIR $FRONTEND_DIR"
else
    echo "🎉 TUDO FUNCIONANDO! Deploy realizado com sucesso!"
    echo ""
    echo "✅ Para fazer novos deploys, use:"
    echo "   git push origin hostinger-hom"
fi

echo ""
echo "📞 Para monitorar logs:"
echo "   tail -f deploy.log"
