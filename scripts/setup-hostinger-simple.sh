#!/bin/bash

# Script simplificado para configurar Git Hook na Hostinger
# Execute este script no servidor via SSH

set -e

echo "🚀 Configurando Git Hook na Hostinger..."
echo "========================================"

# Configurações
PROJECT_ROOT="/home/$(whoami)"
API_DIR="$PROJECT_ROOT/api-hom.virtualt.com.br"
FRONTEND_DIR="$PROJECT_ROOT/app-hom.virtualt.com.br"

echo "📁 Diretório do projeto: $PROJECT_ROOT"
echo "🔗 API: $API_DIR"
echo "🌐 Frontend: $FRONTEND_DIR"

# 1. Criar diretórios dos subdomínios
echo ""
echo "📁 1. Criando diretórios dos subdomínios..."
mkdir -p "$API_DIR"
mkdir -p "$FRONTEND_DIR"
echo "✅ Diretórios criados"

# 2. Configurar Git (se não existir)
echo ""
echo "🔧 2. Configurando Git..."
cd "$PROJECT_ROOT"

if [ ! -d ".git" ]; then
    echo "📁 Inicializando repositório Git..."
    git init
    git remote add origin https://github.com/spsise/rei-do-oleo.git
    echo "✅ Repositório Git inicializado"
else
    echo "📁 Repositório Git já existe"
    # Verificar se o remote está correto
    if ! git remote get-url origin 2>/dev/null | grep -q "spsise/rei-do-oleo"; then
        echo "🔄 Configurando remote correto..."
        git remote set-url origin https://github.com/spsise/rei-do-oleo.git
    fi
fi

# 3. Criar diretório hooks se não existir
echo ""
echo "🔧 3. Configurando Git Hook..."
mkdir -p .git/hooks

# 4. Criar post-receive hook
echo "📝 Criando post-receive hook..."
cat > .git/hooks/post-receive << 'EOF'
#!/bin/bash

set -e

echo "🚀 Iniciando deploy automático..."

# Configurações
PROJECT_ROOT="/home/$(whoami)"
API_DIR="$PROJECT_ROOT/api-hom.virtualt.com.br"
FRONTEND_DIR="$PROJECT_ROOT/app-hom.virtualt.com.br"
BRANCH="hostinger-hom"

# Ler referências do stdin
while read oldrev newrev refname; do
    branch=$(git rev-parse --symbolic --abbrev-ref $refname)

    if [ "$branch" = "$BRANCH" ]; then
        echo "📦 Deployando branch: $branch"

        # Fazer checkout dos arquivos
        git --work-tree="$PROJECT_ROOT" --git-dir="$PROJECT_ROOT/.git" checkout -f $BRANCH

        # Deploy Backend (Laravel)
        if [ -d "backend" ]; then
            echo "🔧 Configurando Laravel API..."

            # Limpar diretório da API
            rm -rf "$API_DIR"/*

            # Copiar arquivos do backend
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

            # Configurar .htaccess para API
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

        # Deploy Frontend (React)
        if [ -d "frontend" ]; then
            echo "⚛️ Configurando React App..."

            # Limpar diretório do frontend
            rm -rf "$FRONTEND_DIR"/*

            cd frontend

            # Instalar dependências
            npm ci

            # Build para produção
            npm run build

            # Mover arquivos buildados
            cp -r dist/* "$FRONTEND_DIR/"

            cd ..

            # Configurar .htaccess para frontend
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

        echo "🎉 Deploy concluído com sucesso!"
        echo "🌐 Frontend: https://app-hom.virtualt.com.br"
        echo "🔗 API: https://api-hom.virtualt.com.br"

        # Log do deploy
        echo "$(date): Deploy realizado com sucesso" >> "$PROJECT_ROOT/deploy.log"
    fi
done
EOF

# 5. Tornar o hook executável
chmod +x .git/hooks/post-receive
echo "✅ Git Hook configurado e executável"

# 6. Verificar configuração
echo ""
echo "📋 4. Verificando configuração..."
echo "   Git Hook: $(ls -la .git/hooks/post-receive)"
echo "   Remote: $(git remote get-url origin)"
echo "   Diretórios:"
echo "     API: $API_DIR"
echo "     Frontend: $FRONTEND_DIR"

# 7. Fazer primeiro deploy
echo ""
echo "🚀 5. Fazendo primeiro deploy..."
git fetch origin
git checkout hostinger-hom

echo ""
echo "🎯 CONFIGURAÇÃO CONCLUÍDA!"
echo ""
echo "📋 PRÓXIMOS PASSOS:"
echo "1. Configurar variáveis de ambiente:"
echo "   cd $API_DIR"
echo "   cp .env.example .env"
echo "   nano .env"
echo ""
echo "2. Configurar banco de dados no .env:"
echo "   DB_HOST=localhost"
echo "   DB_DATABASE=seu_banco"
echo "   DB_USERNAME=seu_usuario"
echo "   DB_PASSWORD=sua_senha"
echo ""
echo "3. Testar deploy automático:"
echo "   git push origin hostinger-hom"
echo ""
echo "4. Verificar status:"
echo "   curl -I https://api-hom.virtualt.com.br"
echo "   curl -I https://app-hom.virtualt.com.br"
echo ""
echo "✅ Git Hook configurado! Agora faça push para testar o deploy automático."
