#!/bin/bash

# Script para configurar Git Hooks no servidor Hostinger
# Execute este script no servidor Hostinger via SSH

set -e

echo "🚀 Configurando Git Hooks para deploy automático..."

# Diretórios do projeto
PROJECT_ROOT="/home/$(whoami)/public_html"
API_DIR="$PROJECT_ROOT/api"
FRONTEND_DIR="$PROJECT_ROOT"

# Criar diretório .git se não existir
if [ ! -d "$PROJECT_ROOT/.git" ]; then
    echo "📁 Inicializando repositório Git..."
    cd "$PROJECT_ROOT"
    git init
    git remote add origin https://github.com/SEU_USUARIO/rei-do-oleo.git
fi

# Criar post-receive hook
cat > "$PROJECT_ROOT/.git/hooks/post-receive" << 'EOF'
#!/bin/bash

set -e

echo "🚀 Iniciando deploy automático..."

# Configurações
PROJECT_ROOT="/home/$(whoami)/public_html"
API_DIR="$PROJECT_ROOT/api"
FRONTEND_DIR="$PROJECT_ROOT"
BRANCH="main"

# Ler referências do stdin
while read oldrev newrev refname; do
    branch=$(git rev-parse --symbolic --abbrev-ref $refname)

    if [ "$branch" = "$BRANCH" ]; then
        echo "📦 Deployando branch: $branch"

        # Fazer checkout dos arquivos
        git --work-tree="$PROJECT_ROOT" --git-dir="$PROJECT_ROOT/.git" checkout -f $BRANCH

        # Deploy Backend (Laravel)
        if [ -d "$API_DIR" ]; then
            echo "🔧 Configurando Laravel..."
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

            echo "✅ Backend Laravel configurado"
        fi

        # Deploy Frontend (React)
        if [ -d "$FRONTEND_DIR/frontend" ]; then
            echo "⚛️ Configurando React..."
            cd "$FRONTEND_DIR/frontend"

            # Instalar dependências
            npm ci

            # Build para produção
            npm run build

            # Mover arquivos buildados para public_html
            cp -r dist/* "$FRONTEND_DIR/"

            # Limpar arquivos de desenvolvimento
            rm -rf node_modules/
            rm -rf src/
            rm -rf public/
            rm package.json package-lock.json
            rm vite.config.ts tailwind.config.js postcss.config.js
            rm tsconfig.json tsconfig.app.json tsconfig.node.json
            rm .eslintrc.js .prettierrc index.html

            echo "✅ Frontend React configurado"
        fi

        # Configurar permissões
        chmod -R 755 "$PROJECT_ROOT"
        chmod -R 644 "$PROJECT_ROOT"/*.html
        chmod -R 644 "$PROJECT_ROOT"/*.css
        chmod -R 644 "$PROJECT_ROOT"/*.js

        if [ -d "$API_DIR" ]; then
            chmod -R 755 "$API_DIR/storage"
            chmod -R 755 "$API_DIR/bootstrap/cache"
        fi

        echo "🎉 Deploy concluído com sucesso!"
        echo "🌐 Frontend: https://$(hostname)"
        echo "🔗 API: https://$(hostname)/api"
    fi
done
EOF

# Tornar o hook executável
chmod +x "$PROJECT_ROOT/.git/hooks/post-receive"

echo "✅ Git hook configurado em: $PROJECT_ROOT/.git/hooks/post-receive"

# Criar script de deploy manual
cat > "$PROJECT_ROOT/deploy.sh" << 'EOF'
#!/bin/bash

# Script de deploy manual
cd "$(dirname "$0")"
git pull origin main
EOF

chmod +x "$PROJECT_ROOT/deploy.sh"

echo "✅ Script de deploy manual criado: $PROJECT_ROOT/deploy.sh"

# Configurar .gitignore para produção
cat > "$PROJECT_ROOT/.gitignore" << 'EOF'
# Laravel
/vendor/
/node_modules/
.env
.env.backup
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
*.log

# React
frontend/node_modules/
frontend/dist/
frontend/.env
frontend/.env.local
frontend/.env.development.local
frontend/.env.test.local
frontend/.env.production.local

# Sistema
.DS_Store
Thumbs.db
EOF

echo "✅ .gitignore configurado"

echo ""
echo "🎯 PRÓXIMOS PASSOS:"
echo "1. Configure o repositório remoto:"
echo "   cd $PROJECT_ROOT"
echo "   git remote set-url origin https://github.com/SEU_USUARIO/rei-do-oleo.git"
echo ""
echo "2. Configure as variáveis de ambiente:"
echo "   cd $API_DIR"
echo "   cp .env.example .env"
echo "   nano .env"
echo ""
echo "3. Para fazer deploy:"
echo "   git push origin main"
echo ""
echo "4. Para deploy manual:"
echo "   ./deploy.sh"
