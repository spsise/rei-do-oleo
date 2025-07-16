#!/bin/bash

# Script para configurar Git Hooks na Hostinger com estrutura correta
# Execute este script no servidor Hostinger via SSH

set -e

echo "🚀 Configurando Git Hooks para Hostinger..."
echo "📁 Projeto: /home/$(whoami)/rei-do-oleo"
echo "🔗 API: /home/$(whoami)/domains/virtualt.com.br/public_html/api-hom"
echo "🌐 Frontend: /home/$(whoami)/domains/virtualt.com.br/public_html/app-hom"

# Diretórios do projeto
PROJECT_ROOT="/home/$(whoami)/rei-do-oleo"
API_DIR="/home/$(whoami)/domains/virtualt.com.br/public_html/api-hom"
FRONTEND_DIR="/home/$(whoami)/domains/virtualt.com.br/public_html/app-hom"

# Criar diretórios se não existirem
mkdir -p "$PROJECT_ROOT"
mkdir -p "$API_DIR"
mkdir -p "$FRONTEND_DIR"

# Verificar se já existe um .git e se é do projeto correto
if [ -d "$PROJECT_ROOT/.git" ]; then
    echo "📁 Diretório .git já existe..."

    # Verificar se é do projeto correto
    if [ -f "$PROJECT_ROOT/.git/config" ]; then
        if grep -q "spsise/rei-do-oleo" "$PROJECT_ROOT/.git/config" 2>/dev/null; then
            echo "✅ .git já configurado para o projeto correto"
        else
            echo "⚠️ .git existe mas é de outro projeto"
            echo "📋 Configuração atual:"
            cat "$PROJECT_ROOT/.git/config" | grep -E "(url|remote)" || echo "Sem configuração de remote"

            read -p "Deseja sobrescrever o .git existente? (y/N): " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                echo "🗑️ Removendo .git existente..."
                rm -rf "$PROJECT_ROOT/.git"
                echo "📁 Inicializando novo repositório Git..."
                cd "$PROJECT_ROOT"
                git init
                git remote add origin https://github.com/spsise/rei-do-oleo.git
            else
                echo "❌ Configuração cancelada. Remova manualmente o .git existente ou configure-o corretamente."
                exit 1
            fi
        fi
    else
        echo "⚠️ .git existe mas não tem configuração válida"
        read -p "Deseja sobrescrever o .git existente? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            echo "🗑️ Removendo .git existente..."
            rm -rf "$PROJECT_ROOT/.git"
            echo "📁 Inicializando novo repositório Git..."
            cd "$PROJECT_ROOT"
            git init
            git remote add origin https://github.com/spsise/rei-do-oleo.git
        else
            echo "❌ Configuração cancelada."
            exit 1
        fi
    fi
else
    echo "📁 Inicializando repositório Git..."
    cd "$PROJECT_ROOT"
    git init
    git remote add origin https://github.com/spsise/rei-do-oleo.git
fi

# Fazer fetch e checkout da branch correta
echo "📥 Fazendo fetch das branches..."
git fetch origin

echo "🔀 Fazendo checkout da branch hostinger-hom..."
if git checkout hostinger-hom 2>/dev/null; then
    echo "✅ Checkout realizado com sucesso"
else
    echo "🔄 Criando branch local hostinger-hom..."
    git checkout -b hostinger-hom origin/hostinger-hom
    echo "✅ Branch criada e checkout realizado"
fi

# Criar post-receive hook
cat > "$PROJECT_ROOT/.git/hooks/post-receive" << 'EOF'
#!/bin/bash

set -e

echo "🚀 Iniciando deploy automático para subdomínios..."

# Configurações
PROJECT_ROOT="/home/$(whoami)/rei-do-oleo"
API_DIR="/home/$(whoami)/domains/virtualt.com.br/public_html/api-hom"
FRONTEND_DIR="/home/$(whoami)/domains/virtualt.com.br/public_html/app-hom"
BRANCH="hostinger-hom"

# Ler referências do stdin
while read oldrev newrev refname; do
    branch=$(git rev-parse --symbolic --abbrev-ref $refname)

    if [ "$branch" = "$BRANCH" ]; then
        echo "📦 Deployando branch: $branch"

        # Fazer checkout dos arquivos
        git --work-tree="$PROJECT_ROOT" --git-dir="$PROJECT_ROOT/.git" checkout -f $BRANCH

        # Deploy Backend (Laravel) - API Subdomain
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

            # Limpar arquivos de desenvolvimento
            rm -rf tests/
            rm -rf .phpunit.cache/
            rm -rf storage/logs/*.log
            rm -rf storage/framework/cache/*
            rm -rf storage/framework/sessions/*
            rm -rf storage/framework/views/*

            # Configurar .htaccess para API
            cat > .htaccess << 'HTACCESS'
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
HTACCESS

            # Configurar permissões
            chmod -R 755 storage
            chmod -R 755 bootstrap/cache
            chmod 644 .env

            echo "✅ Backend Laravel configurado em api-hom.virtualt.com.br"
        fi

        # Deploy Frontend (React) - App Subdomain
        if [ -d "frontend" ]; then
            echo "⚛️ Configurando React App..."

            # Limpar diretório do frontend
            rm -rf "$FRONTEND_DIR"/*

            cd frontend

            # Instalar dependências
            npm ci

            # Build para produção
            npm run build

            # Mover arquivos buildados para o subdomínio
            cp -r dist/* "$FRONTEND_DIR/"

            # Limpar arquivos de desenvolvimento
            rm -rf node_modules/
            rm -rf src/
            rm -rf public/
            rm package.json package-lock.json
            rm vite.config.ts tailwind.config.js postcss.config.js
            rm tsconfig.json tsconfig.app.json tsconfig.node.json
            rm .eslintrc.js .prettierrc index.html

            cd ..

            # Configurar .htaccess para frontend
            cat > "$FRONTEND_DIR/.htaccess" << 'HTACCESS'
RewriteEngine On

# Handle React Router
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.html [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Cache static assets
<FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 year"
    Header set Cache-Control "public, immutable"
</FilesMatch>
HTACCESS

            # Configurar permissões
            chmod -R 755 "$FRONTEND_DIR"
            chmod -R 644 "$FRONTEND_DIR"/*.html
            chmod -R 644 "$FRONTEND_DIR"/*.css
            chmod -R 644 "$FRONTEND_DIR"/*.js

            echo "✅ Frontend React configurado em app-hom.virtualt.com.br"
        fi

        # Limpar arquivos temporários
        rm -rf backend/
        rm -rf frontend/
        rm -rf scripts/
        rm -rf docs/
        rm -rf .github/
        rm -rf docker/
        rm -f *.md
        rm -f *.yml
        rm -f *.json
        rm -f *.lock

        echo "🎉 Deploy concluído com sucesso!"
        echo "🌐 Frontend: https://app-hom.virtualt.com.br"
        echo "🔗 API: https://api-hom.virtualt.com.br"

        # Log do deploy
        echo "$(date): Deploy realizado com sucesso" >> "$PROJECT_ROOT/deploy.log"
    fi
done
EOF

# Tornar o hook executável
chmod +x "$PROJECT_ROOT/.git/hooks/post-receive"

echo "✅ Git hook configurado em: $PROJECT_ROOT/.git/hooks/post-receive"

echo "✅ Git hook configurado em: $PROJECT_ROOT/.git/hooks/post-receive"

echo ""
echo "💡 DICA: Para verificar subdomínios manualmente, você pode criar:"
echo "   nano $PROJECT_ROOT/check-subdomains.sh"
echo "   # Conteúdo útil para debug dos subdomínios"

# Verificar configuração atual
echo ""
echo "📋 Configuração atual do Git:"
cd "$PROJECT_ROOT"
git remote -v
git branch -a 2>/dev/null || echo "Nenhuma branch encontrada"

echo ""
echo "🎯 PRÓXIMOS PASSOS:"
echo "1. Configure o repositório remoto (se necessário):"
echo "   cd $PROJECT_ROOT"
echo "   git remote set-url origin https://github.com/spsise/rei-do-oleo.git"
echo ""
echo "2. Configure as variáveis de ambiente:"
echo "   cd $API_DIR"
echo "   cp .env.example .env"
echo "   nano .env"
echo "   # Configurar: DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL=https://api-hom.virtualt.com.br"
echo ""
echo "3. Configure o frontend:"
echo "   cd $FRONTEND_DIR"
echo "   # Criar .env com VITE_API_URL=https://api-hom.virtualt.com.br"
echo ""
echo "4. Para fazer deploy:"
echo "   cd $PROJECT_ROOT"
echo "   git push origin hostinger-hom"
echo ""
echo "5. Para deploy manual (se necessário):"
echo "   cd $PROJECT_ROOT"
echo "   git pull origin hostinger-hom"
echo ""
echo "6. Para verificar subdomínios (criar manualmente se necessário):"
echo "   curl -I https://api-hom.virtualt.com.br"
echo "   curl -I https://app-hom.virtualt.com.br"
