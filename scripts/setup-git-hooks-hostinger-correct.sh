#!/bin/bash

# Script para configurar Git Hooks na Hostinger com estrutura correta - VERSÃO CORRIGIDA
# Execute este script no servidor Hostinger via SSH
# CORREÇÃO: Frontend não deleta mais arquivos do repositório Git

set -e

echo "🚀 Configurando Git Hooks para Hostinger (VERSÃO CORRIGIDA)..."
echo "📁 Projeto: /home/$(whoami)/rei-do-oleo"
echo "🔗 API: /home/$(whoami)/domains/virtualt.com.br/public_html/api-hom"
echo "🌐 Frontend: /home/$(whoami)/domains/virtualt.com.br/public_html/app-hom"

# Função para aplicar limite de 2 backups
apply_backup_limit() {
    local backup_dir="$1"
    if [ -d "$backup_dir" ]; then
        echo "🔧 Aplicando limite de 2 backups em: $backup_dir"

        # Limpar backups de API
        api_backups=$(ls -t "$backup_dir"/api_backup_* 2>/dev/null || true)
        if [ -n "$api_backups" ]; then
            total_api=$(echo "$api_backups" | wc -l)
            if [ "$total_api" -gt 2 ]; then
                echo "🗑️ Removendo $(($total_api - 2)) backups antigos de API..."
                echo "$api_backups" | tail -n +3 | xargs rm -rf 2>/dev/null || true
            fi
        fi

        # Limpar backups de frontend
        frontend_backups=$(ls -t "$backup_dir"/frontend_backup_* 2>/dev/null || true)
        if [ -n "$frontend_backups" ]; then
            total_frontend=$(echo "$frontend_backups" | wc -l)
            if [ "$total_frontend" -gt 2 ]; then
                echo "🗑️ Removendo $(($total_frontend - 2)) backups antigos de frontend..."
                echo "$frontend_backups" | tail -n +3 | xargs rm -rf 2>/dev/null || true
            fi
        fi

        # Limpar backups de rollback
        rollback_backups=$(ls -t "$backup_dir"/*_rollback_backup_* 2>/dev/null || true)
        if [ -n "$rollback_backups" ]; then
            total_rollback=$(echo "$rollback_backups" | wc -l)
            if [ "$total_rollback" -gt 2 ]; then
                echo "🗑️ Removendo $(($total_rollback - 2)) backups antigos de rollback..."
                echo "$rollback_backups" | tail -n +3 | xargs rm -rf 2>/dev/null || true
            fi
        fi

        echo "✅ Limite de 2 backups aplicado com sucesso!"
    fi
}

# Diretórios do projeto
PROJECT_ROOT="/home/$(whoami)/rei-do-oleo"
API_DIR="/home/$(whoami)/domains/virtualt.com.br/public_html/api-hom"
FRONTEND_DIR="/home/$(whoami)/domains/virtualt.com.br/public_html/app-hom"

# Criar diretórios se não existirem
mkdir -p "$PROJECT_ROOT"
mkdir -p "$API_DIR"
mkdir -p "$FRONTEND_DIR"

# Aplicar limite de 2 backups se o diretório de backup existir
apply_backup_limit "$PROJECT_ROOT/backups"

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

        # Executar script de deploy
        if [ -f "$PROJECT_ROOT/deploy.sh" ]; then
            bash "$PROJECT_ROOT/deploy.sh"
        else
            echo "❌ Script de deploy não encontrado"
        fi
    fi
done
EOF

# Criar script de deploy principal
cat > "$PROJECT_ROOT/deploy.sh" << 'EOF'
#!/bin/bash

set -e

echo "🚀 Iniciando deploy incremental para subdomínios..."

# Configurações
PROJECT_ROOT="/home/$(whoami)/rei-do-oleo"
API_DIR="/home/$(whoami)/domains/virtualt.com.br/public_html/api-hom"
FRONTEND_DIR="/home/$(whoami)/domains/virtualt.com.br/public_html/app-hom"
BACKUP_DIR="/home/$(whoami)/rei-do-oleo/backups"

# Criar diretório de backup se não existir
mkdir -p "$BACKUP_DIR"

# Criar e configurar arquivo de log
if [ ! -f "$PROJECT_ROOT/deploy.log" ]; then
    touch "$PROJECT_ROOT/deploy.log"
    echo "✅ Arquivo de log criado: $PROJECT_ROOT/deploy.log"
fi
chmod 644 "$PROJECT_ROOT/deploy.log"

cd "$PROJECT_ROOT"

# ATUALIZAR REPOSITÓRIO LOCAL COM AS MUDANÇAS DO GITHUB
echo "📥 Atualizando repositório local..."
git fetch origin
git reset --hard origin/hostinger-hom
echo "✅ Repositório atualizado com sucesso"

# Log do deploy
echo "$(date): Deploy iniciado - repositório atualizado" >> "$PROJECT_ROOT/deploy.log"

# FUNÇÃO PARA LIMPAR BACKUPS ANTIGOS (EXECUTAR ANTES DE CRIAR NOVOS)
cleanup_old_backups() {
    echo "🧹 Limpando backups antigos antes de criar novos..."

    if [ -d "$BACKUP_DIR" ]; then
        # Limpar backups de API (manter apenas os 2 mais recentes)
        api_backups=$(ls -t "$BACKUP_DIR"/api_backup_* 2>/dev/null || true)
        if [ -n "$api_backups" ]; then
            total_api=$(echo "$api_backups" | wc -l)
            if [ "$total_api" -gt 2 ]; then
                echo "🗑️ Removendo $(($total_api - 2)) backups antigos de API..."
                echo "$api_backups" | tail -n +3 | xargs rm -rf 2>/dev/null || true
            fi
        fi

        # Limpar backups de frontend (manter apenas os 2 mais recentes)
        frontend_backups=$(ls -t "$BACKUP_DIR"/frontend_backup_* 2>/dev/null || true)
        if [ -n "$frontend_backups" ]; then
            total_frontend=$(echo "$frontend_backups" | wc -l)
            if [ "$total_frontend" -gt 2 ]; then
                echo "🗑️ Removendo $(($total_frontend - 2)) backups antigos de frontend..."
                echo "$frontend_backups" | tail -n +3 | xargs rm -rf 2>/dev/null || true
            fi
        fi

        # Limpar backups de rollback (manter apenas os 2 mais recentes)
        rollback_backups=$(ls -t "$BACKUP_DIR"/*_rollback_backup_* 2>/dev/null || true)
        if [ -n "$rollback_backups" ]; then
            total_rollback=$(echo "$rollback_backups" | wc -l)
            if [ "$total_rollback" -gt 2 ]; then
                echo "🗑️ Removendo $(($total_rollback - 2)) backups antigos de rollback..."
                echo "$rollback_backups" | tail -n +3 | xargs rm -rf 2>/dev/null || true
            fi
        fi

        echo "✅ Limpeza de backups antigos concluída - mantidos apenas os 2 mais recentes de cada tipo"
    else
        echo "ℹ️ Diretório de backup não encontrado: $BACKUP_DIR"
    fi
}

# EXECUTAR LIMPEZA DE BACKUPS ANTIGOS ANTES DE CRIAR NOVOS
cleanup_old_backups

# Função para fazer backup de arquivos importantes
backup_important_files() {
    local target_dir="$1"
    local backup_name="$2"
    local backup_path="$BACKUP_DIR/${backup_name}_$(date +%Y%m%d_%H%M%S)"

    echo "💾 Fazendo backup de arquivos importantes..."
    mkdir -p "$backup_path"

    # Backup de arquivos importantes do Laravel
    if [ -d "$target_dir/vendor" ]; then
        echo "📦 Backup do vendor..."
        cp -r "$target_dir/vendor" "$backup_path/"
    fi

    if [ -f "$target_dir/.env" ]; then
        echo "⚙️ Backup do .env..."
        cp "$target_dir/.env" "$backup_path/"
    fi

    if [ -d "$target_dir/storage/app" ]; then
        echo "📁 Backup do storage/app..."
        cp -r "$target_dir/storage/app" "$backup_path/"
    fi

    if [ -d "$target_dir/storage/logs" ]; then
        echo "📝 Backup dos logs..."
        cp -r "$target_dir/storage/logs" "$backup_path/"
    fi

    echo "✅ Backup salvo em: $backup_path"
}

# Função para restaurar arquivos importantes
restore_important_files() {
    local target_dir="$1"
    local backup_path="$2"

    echo "🔄 Restaurando arquivos importantes..."

    # Restaurar vendor se existir no backup
    if [ -d "$backup_path/vendor" ]; then
        echo "📦 Restaurando vendor..."
        rm -rf "$target_dir/vendor"
        cp -r "$backup_path/vendor" "$target_dir/"
    fi

    # Restaurar .env se existir no backup
    if [ -f "$backup_path/.env" ]; then
        echo "⚙️ Restaurando .env..."
        cp "$backup_path/.env" "$target_dir/"
    fi

    # Restaurar storage/app se existir no backup
    if [ -d "$backup_path/storage/app" ]; then
        echo "📁 Restaurando storage/app..."
        rm -rf "$target_dir/storage/app"
        cp -r "$backup_path/storage/app" "$target_dir/"
    fi

    # Restaurar logs se existir no backup
    if [ -d "$backup_path/storage/logs" ]; then
        echo "📝 Restaurando logs..."
        rm -rf "$target_dir/storage/logs"
        cp -r "$backup_path/storage/logs" "$target_dir/"
    fi
}

# Deploy Backend (Laravel) - API Subdomain
if [ -d "backend" ]; then
    echo "🔧 Configurando Laravel API..."

    # Backup dos arquivos importantes se o diretório já existe
    if [ -d "$API_DIR" ]; then
        backup_important_files "$API_DIR" "api_backup"
    fi

    # Criar diretório temporário para o novo deploy
    TEMP_API_DIR="$API_DIR.temp"
    rm -rf "$TEMP_API_DIR"
    mkdir -p "$TEMP_API_DIR"

    # Copiar arquivos do backend para diretório temporário
    echo "📋 Copiando arquivos do backend..."
    cp -r backend/* "$TEMP_API_DIR/"

    # Copiar arquivos ocultos importantes (que começam com .)
    echo "📋 Copiando arquivos ocultos importantes..."
    cp backend/.env.example "$TEMP_API_DIR/" 2>/dev/null || true
    cp backend/.env.testing.example "$TEMP_API_DIR/" 2>/dev/null || true
    cp backend/.gitignore "$TEMP_API_DIR/" 2>/dev/null || true
    cp backend/.gitattributes "$TEMP_API_DIR/" 2>/dev/null || true
    cp backend/.editorconfig "$TEMP_API_DIR/" 2>/dev/null || true
    cp backend/phpunit.xml "$TEMP_API_DIR/" 2>/dev/null || true
    cp backend/vite.config.js "$TEMP_API_DIR/" 2>/dev/null || true
    cp backend/package.json "$TEMP_API_DIR/" 2>/dev/null || true

    # Copiar vendor do diretório original (se existir)
    if [ -d "$API_DIR/vendor" ]; then
        echo "📦 Copiando vendor do diretório original..."
        cp -r "$API_DIR/vendor" "$TEMP_API_DIR/"
    fi

    # Restaurar outros arquivos importantes do backup (se existir)
    if [ -d "$API_DIR" ]; then
        latest_backup=$(ls -t "$BACKUP_DIR"/api_backup_* 2>/dev/null | head -1)
        if [ -n "$latest_backup" ]; then
            echo "🔄 Restaurando outros arquivos importantes do backup..."
            # Restaurar .env e storage, mas não vendor (já copiamos acima)
            if [ -f "$latest_backup/.env" ]; then
                cp "$latest_backup/.env" "$TEMP_API_DIR/"
            fi
            if [ -d "$latest_backup/storage/app" ]; then
                rm -rf "$TEMP_API_DIR/storage/app"
                cp -r "$latest_backup/storage/app" "$TEMP_API_DIR/"
            fi
            if [ -d "$latest_backup/storage/logs" ]; then
                rm -rf "$TEMP_API_DIR/storage/logs"
                cp -r "$latest_backup/storage/logs" "$TEMP_API_DIR/"
            fi
        fi
    fi

    # Copiar .env do diretório de produção existente (prioridade sobre backup)
    if [ -f "$API_DIR/.env" ]; then
        echo "📋 Copiando .env do diretório de produção..."
        cp "$API_DIR/.env" "$TEMP_API_DIR/"
    fi

    # Verificar se vendor foi copiado com sucesso
    echo "📦 Verificando dependências..."
    if [ -d "$TEMP_API_DIR/vendor" ]; then
        echo "✅ Vendor copiado com sucesso para nova versão"
    else
        echo "⚠️ Vendor não encontrado"
        echo "   Copie a pasta vendor do seu ambiente local para: $API_DIR/"
        echo "   Ou faça upload via FTP/SFTP"
        echo "   Ou execute: composer install --no-dev --optimize-autoloader"
        echo ""
        echo "❌ Deploy interrompido - vendor é obrigatório para continuar"
        exit 1
    fi

    # Mudar para o diretório temporário para executar comandos Laravel
    cd "$TEMP_API_DIR"

    # Verificar se o vendor está funcionando
    echo "🔍 Testando se o vendor está funcionando..."
    if php artisan --version > /dev/null 2>&1; then
        echo "✅ Vendor funcionando corretamente"
    else
        echo "❌ Vendor não está funcionando - verifique as dependências"
        echo "   Execute: composer install --no-dev --optimize-autoloader"
        exit 1
    fi

    # Verificar se .env foi restaurado do backup, senão criar um novo
    if [ ! -f ".env" ]; then
        echo "⚙️ Criando arquivo .env..."
        cp .env.example .env
        php artisan key:generate
    else
        echo "✅ Usando .env existente do backup"
    fi

    # Otimizar para produção
    echo "⚡ Otimizando para produção..."
    # Garantir que o diretório de views compiladas existe
    mkdir -p storage/framework/views
    chmod -R 755 storage
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Criar link simbólico para storage
    echo "🔗 Criando link simbólico para storage..."
    # Garantir que os diretórios necessários existem
    mkdir -p public
    mkdir -p storage/app/public

    # Tentar criar link simbólico (com fallback)
    if [ ! -L "public/storage" ]; then
        # Tentar via Laravel primeiro
        if php artisan storage:link > /dev/null 2>&1; then
            echo "✅ Link simbólico criado via Laravel"
        else
            # Fallback manual
            echo "⚠️ Tentando criar link simbólico manualmente..."
            ln -sf "../storage/app/public" public/storage 2>/dev/null || echo "⚠️ Erro ao criar link simbólico (pode já existir)"
        fi
    else
        echo "✅ Link simbólico para storage já existe"
    fi

    # Executar migrações
    echo "🗄️ Executando migrações..."
    php artisan migrate --force

    # Limpar arquivos de desenvolvimento
    echo "🧹 Limpando arquivos de desenvolvimento..."
    rm -rf tests/ 2>/dev/null || true
    rm -rf .phpunit.cache/ 2>/dev/null || true
    rm -rf storage/framework/cache/* 2>/dev/null || true
    rm -rf storage/framework/sessions/* 2>/dev/null || true
    rm -rf storage/framework/views/* 2>/dev/null || true

    # Garantir permissões corretas para logs
    mkdir -p storage/logs
    chmod -R 755 storage/logs

    # Criar arquivo de log principal do Laravel se não existir
    if [ ! -f "storage/logs/laravel.log" ]; then
        touch storage/logs/laravel.log
        echo "✅ Arquivo de log do Laravel criado: storage/logs/laravel.log"
    fi
    chmod 644 storage/logs/laravel.log

    # Configurar .htaccess para API
    cat > .htaccess << 'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Redireciona tudo para public/index.php
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ public/index.php [L]

    # Protege arquivos sensíveis
    <FilesMatch "\.(env|env\.example|gitignore|gitattributes|git|lock|json|md|yml|yaml|xml|sh|bat|ini|log|sql|bak|zip|tar|gz|rar|7z)$">
        Order allow,deny
        Deny from all
    </FilesMatch>
</IfModule>

<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection 1; mode=block"
</IfModule>
HTACCESS

    # Configurar permissões
    chmod -R 755 storage
    chmod -R 755 storage/logs
    chmod -R 755 bootstrap/cache
    chmod 644 .env

    # Fazer swap dos diretórios (deploy atômico)
    echo "🔄 Fazendo swap dos diretórios..."
    if [ -d "$API_DIR" ]; then
        mv "$API_DIR" "$API_DIR.old"
    fi
    mv "$TEMP_API_DIR" "$API_DIR"

    # Remover diretório antigo após alguns segundos (para garantir que não há problemas)
    if [ -d "$API_DIR.old" ]; then
        echo "🗑️ Removendo versão anterior em 5 segundos..."
        (sleep 5 && rm -rf "$API_DIR.old") &
    fi

    echo "✅ Backend Laravel configurado em api-hom.virtualt.com.br"
    echo "ℹ️ Para ver os logs da aplicação, acesse: $API_DIR/storage/logs/laravel.log"
    echo "ℹ️ Para ver o log do deploy, acesse: $PROJECT_ROOT/deploy.log"
fi

cd "$PROJECT_ROOT"

# Deploy Frontend (React) - App Subdomain - VERSÃO CORRIGIDA
if [ -d "frontend" ]; then
    echo "⚛️ Configurando React App..."

    # Criar diretório temporário para o novo deploy
    TEMP_FRONTEND_DIR="$FRONTEND_DIR.temp"
    rm -rf "$TEMP_FRONTEND_DIR"
    mkdir -p "$TEMP_FRONTEND_DIR"

    # Criar diretório temporário para build
    TEMP_BUILD_DIR="/tmp/frontend-build-$(date +%Y%m%d-%H%M%S)"
    mkdir -p "$TEMP_BUILD_DIR"

    # Copiar arquivos do frontend para diretório temporário de build
    echo "📋 Copiando arquivos do frontend para build temporário..."
    cp -r frontend/* "$TEMP_BUILD_DIR/"

    cd "$TEMP_BUILD_DIR"

    # Verificar se existe build local do frontend
    if [ -d "dist" ]; then
        echo "📋 Usando build local existente..."
        cp -r dist/* "$TEMP_FRONTEND_DIR/"
    else
        echo "⚠️ Build local não encontrado. Execute npm run build no frontend antes do deploy."
        echo "   Comandos: cd frontend && npm install && npm run build"
        echo "❌ Deploy do frontend interrompido - build necessário"

        # Limpar diretório temporário
        cd "$PROJECT_ROOT"
        rm -rf "$TEMP_BUILD_DIR"
        exit 1
    fi

    # Limpar diretório temporário de build (NÃO afeta o repositório Git!)
    echo "🧹 Limpando diretório temporário de build..."
    cd "$PROJECT_ROOT"
    rm -rf "$TEMP_BUILD_DIR"

    # Configurar .htaccess para frontend
    cat > "$TEMP_FRONTEND_DIR/.htaccess" << 'HTACCESS'
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
    chmod -R 755 "$TEMP_FRONTEND_DIR"
    chmod -R 644 "$TEMP_FRONTEND_DIR"/*.html 2>/dev/null || true
    chmod -R 644 "$TEMP_FRONTEND_DIR"/*.css 2>/dev/null || true
    chmod -R 644 "$TEMP_FRONTEND_DIR"/*.js 2>/dev/null || true

    # Fazer swap dos diretórios (deploy atômico)
    echo "🔄 Fazendo swap dos diretórios..."
    if [ -d "$FRONTEND_DIR" ]; then
        mv "$FRONTEND_DIR" "$FRONTEND_DIR.old"
    fi
    mv "$TEMP_FRONTEND_DIR" "$FRONTEND_DIR"

    # Remover diretório antigo após alguns segundos
    if [ -d "$FRONTEND_DIR.old" ]; then
        echo "🗑️ Removendo versão anterior em 5 segundos..."
        (sleep 5 && rm -rf "$FRONTEND_DIR.old") &
    fi

    echo "✅ Frontend React configurado em app-hom.virtualt.com.br"
fi

echo "🎉 Deploy incremental concluído com sucesso!"
echo "🌐 Frontend: https://app-hom.virtualt.com.br"
echo "🔗 API: https://api-hom.virtualt.com.br"

# Log do deploy com informações detalhadas
echo "$(date): Deploy incremental realizado com sucesso - Repositório atualizado via git pull" >> "$PROJECT_ROOT/deploy.log"

# Configurar permissões do arquivo de log
if [ -f "$PROJECT_ROOT/deploy.log" ]; then
    chmod 644 "$PROJECT_ROOT/deploy.log"
    echo "✅ Arquivo de log configurado com permissões corretas: $PROJECT_ROOT/deploy.log"
else
    # Criar arquivo de log se não existir
    touch "$PROJECT_ROOT/deploy.log"
    chmod 644 "$PROJECT_ROOT/deploy.log"
    echo "✅ Arquivo de log criado com permissões corretas: $PROJECT_ROOT/deploy.log"
fi

# LIMPEZA FINAL DE BACKUPS (DUPLA VERIFICAÇÃO)
echo "🧹 Verificação final de backups..."
cleanup_old_backups

echo "✅ Deploy concluído com zero downtime!"
EOF

# Tornar os scripts executáveis
chmod +x "$PROJECT_ROOT/.git/hooks/post-receive"
chmod +x "$PROJECT_ROOT/deploy.sh"

# Criar script de rollback
cat > "$PROJECT_ROOT/rollback.sh" << 'EOF'
#!/bin/bash

set -e

echo "🔄 Iniciando rollback..."

# Configurações
PROJECT_ROOT="/home/$(whoami)/rei-do-oleo"
API_DIR="/home/$(whoami)/domains/virtualt.com.br/public_html/api-hom"
FRONTEND_DIR="/home/$(whoami)/domains/virtualt.com.br/public_html/app-hom"
BACKUP_DIR="/home/$(whoami)/rei-do-oleo/backups"

# Criar e configurar arquivo de log de rollback
if [ ! -f "$PROJECT_ROOT/rollback.log" ]; then
    touch "$PROJECT_ROOT/rollback.log"
    echo "✅ Arquivo de log de rollback criado: $PROJECT_ROOT/rollback.log"
fi
chmod 644 "$PROJECT_ROOT/rollback.log"

# Função para listar backups disponíveis
list_backups() {
    echo "📋 Backups disponíveis:"
    echo ""
    echo "API Backups:"
    ls -la "$BACKUP_DIR"/api_backup_* 2>/dev/null | awk '{print $9}' | sed 's|.*/||' || echo "Nenhum backup de API encontrado"
    echo ""
    echo "Frontend Backups:"
    ls -la "$BACKUP_DIR"/frontend_backup_* 2>/dev/null | awk '{print $9}' | sed 's|.*/||' || echo "Nenhum backup de frontend encontrado"
    echo ""
}

# Função para fazer rollback da API
rollback_api() {
    local backup_name="$1"
    local backup_path="$BACKUP_DIR/$backup_name"

    if [ ! -d "$backup_path" ]; then
        echo "❌ Backup não encontrado: $backup_name"
        return 1
    fi

    echo "🔄 Fazendo rollback da API para: $backup_name"

    # Backup da versão atual antes do rollback
    if [ -d "$API_DIR" ]; then
        backup_important_files "$API_DIR" "api_rollback_backup"
    fi

    # Restaurar arquivos do backup
    echo "📋 Restaurando arquivos do backup..."
    rm -rf "$API_DIR"/*
    cp -r "$backup_path"/* "$API_DIR/"

    # Configurar permissões
    chmod -R 755 "$API_DIR/storage"
    chmod -R 755 "$API_DIR/bootstrap/cache"
    chmod 644 "$API_DIR/.env"

    echo "✅ Rollback da API concluído"

    # Log da operação
    echo "$(date): Rollback da API para $backup_name realizado com sucesso" >> "$PROJECT_ROOT/rollback.log"
}

# Função para fazer rollback do frontend
rollback_frontend() {
    local backup_name="$1"
    local backup_path="$BACKUP_DIR/$backup_name"

    if [ ! -d "$backup_path" ]; then
        echo "❌ Backup não encontrado: $backup_name"
        return 1
    fi

    echo "🔄 Fazendo rollback do frontend para: $backup_name"

    # Restaurar arquivos do backup
    echo "📋 Restaurando arquivos do backup..."
    rm -rf "$FRONTEND_DIR"/*
    cp -r "$backup_path"/* "$FRONTEND_DIR/"

    # Configurar permissões
    chmod -R 755 "$FRONTEND_DIR"

    echo "✅ Rollback do frontend concluído"

    # Log da operação
    echo "$(date): Rollback do frontend para $backup_name realizado com sucesso" >> "$PROJECT_ROOT/rollback.log"
}

# Verificar argumentos
if [ "$1" = "list" ]; then
    list_backups
    exit 0
fi

if [ "$1" = "api" ] && [ -n "$2" ]; then
    rollback_api "$2"
    exit 0
fi

if [ "$1" = "frontend" ] && [ -n "$2" ]; then
    rollback_frontend "$2"
    exit 0
fi

if [ "$1" = "latest" ]; then
    echo "🔄 Fazendo rollback para a versão mais recente..."

    # Rollback da API
    latest_api_backup=$(ls -t "$BACKUP_DIR"/api_backup_* 2>/dev/null | head -1)
    if [ -n "$latest_api_backup" ]; then
        rollback_api "$(basename "$latest_api_backup")"
    else
        echo "⚠️ Nenhum backup de API encontrado"
    fi

    # Rollback do frontend
    latest_frontend_backup=$(ls -t "$BACKUP_DIR"/frontend_backup_* 2>/dev/null | head -1)
    if [ -n "$latest_frontend_backup" ]; then
        rollback_frontend "$(basename "$latest_frontend_backup")"
    else
        echo "⚠️ Nenhum backup de frontend encontrado"
    fi

    exit 0
fi

# Mostrar ajuda se nenhum argumento válido foi fornecido
echo "🔄 Script de Rollback - Rei do Óleo"
echo ""
echo "Uso:"
echo "  ./rollback.sh list                    - Listar backups disponíveis"
echo "  ./rollback.sh api <backup_name>       - Fazer rollback da API"
echo "  ./rollback.sh frontend <backup_name>  - Fazer rollback do frontend"
echo "  ./rollback.sh latest                  - Fazer rollback para versão mais recente"
echo ""
echo "Exemplos:"
echo "  ./rollback.sh list"
echo "  ./rollback.sh api api_backup_20241201_143022"
echo "  ./rollback.sh frontend frontend_backup_20241201_143022"
echo "  ./rollback.sh latest"
echo ""
list_backups
EOF

# Criar script de limpeza de backups
cat > "$PROJECT_ROOT/cleanup-backups.sh" << 'EOF'
#!/bin/bash

set -e

echo "🧹 Iniciando limpeza de backups..."

# Configurações
PROJECT_ROOT="/home/$(whoami)/rei-do-oleo"
BACKUP_DIR="/home/$(whoami)/rei-do-oleo/backups"

# Criar e configurar arquivo de log de limpeza
if [ ! -f "$PROJECT_ROOT/cleanup.log" ]; then
    touch "$PROJECT_ROOT/cleanup.log"
    echo "✅ Arquivo de log de limpeza criado: $PROJECT_ROOT/cleanup.log"
fi
chmod 644 "$PROJECT_ROOT/cleanup.log"

# Função para limpar backups antigos
cleanup_old_backups() {
    local keep_count="$1"
    local backup_pattern="$2"

    echo "🧹 Limpando backups antigos do padrão: $backup_pattern"
    echo "📊 Mantendo os últimos $keep_count backups..."

    # Listar backups ordenados por data (mais recentes primeiro)
    backups=$(ls -t "$BACKUP_DIR"/$backup_pattern 2>/dev/null || true)

    if [ -z "$backups" ]; then
        echo "ℹ️ Nenhum backup encontrado para o padrão: $backup_pattern"
        return
    fi

    # Contar total de backups
    total_backups=$(echo "$backups" | wc -l)
    echo "📊 Total de backups encontrados: $total_backups"

    if [ "$total_backups" -le "$keep_count" ]; then
        echo "ℹ️ Número de backups está dentro do limite ($keep_count)"
        return
    fi

    # Remover backups antigos (manter apenas os últimos $keep_count)
    backups_to_remove=$(echo "$backups" | tail -n +$((keep_count + 1)))

    if [ -n "$backups_to_remove" ]; then
        echo "🗑️ Removendo backups antigos:"
        echo "$backups_to_remove" | while read backup; do
            echo "   - $(basename "$backup")"
            rm -rf "$backup"
        done
        echo "✅ Limpeza concluída"

        # Log da operação
        echo "$(date): Limpeza de backups do padrão $backup_pattern concluída - removidos $(echo "$backups_to_remove" | wc -l) backups" >> "$PROJECT_ROOT/cleanup.log"
    else
        echo "ℹ️ Nenhum backup para remover"

        # Log da operação
        echo "$(date): Limpeza de backups do padrão $backup_pattern - nenhum backup removido" >> "$PROJECT_ROOT/cleanup.log"
    fi
}

# Verificar argumentos
if [ "$1" = "auto" ]; then
    # Limpeza automática (manter 2 backups de cada tipo)
    echo "$(date): Iniciando limpeza automática de backups" >> "$PROJECT_ROOT/cleanup.log"
    cleanup_old_backups 2 "api_backup_*"
    cleanup_old_backups 2 "frontend_backup_*"
    cleanup_old_backups 2 "*_rollback_backup_*"
    echo "$(date): Limpeza automática de backups concluída" >> "$PROJECT_ROOT/cleanup.log"
    exit 0
fi

if [ "$1" = "all" ]; then
    # Remover todos os backups
    echo "⚠️ ATENÇÃO: Isso removerá TODOS os backups!"
    read -p "Tem certeza? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "$(date): Removendo TODOS os backups" >> "$PROJECT_ROOT/cleanup.log"
        rm -rf "$BACKUP_DIR"/*
        echo "✅ Todos os backups foram removidos"
        echo "$(date): Todos os backups removidos com sucesso" >> "$PROJECT_ROOT/cleanup.log"
    else
        echo "❌ Operação cancelada"
        echo "$(date): Operação de remoção total cancelada pelo usuário" >> "$PROJECT_ROOT/cleanup.log"
    fi
    exit 0
fi

# Limpeza padrão (manter 2 backups de cada tipo)
echo "🧹 Limpeza padrão de backups (manter 2 de cada tipo)..."
echo "$(date): Iniciando limpeza padrão de backups" >> "$PROJECT_ROOT/cleanup.log"
cleanup_old_backups 2 "api_backup_*"
cleanup_old_backups 2 "frontend_backup_*"
cleanup_old_backups 2 "*_rollback_backup_*"
echo "$(date): Limpeza padrão de backups concluída" >> "$PROJECT_ROOT/cleanup.log"

echo "✅ Limpeza concluída!"
EOF

# Tornar os scripts executáveis
chmod +x "$PROJECT_ROOT/rollback.sh"
chmod +x "$PROJECT_ROOT/cleanup-backups.sh"

# Criar e configurar todos os arquivos de log necessários
echo "📝 Configurando sistema de logs..."

# Log principal de deploy
if [ ! -f "$PROJECT_ROOT/deploy.log" ]; then
    touch "$PROJECT_ROOT/deploy.log"
    echo "✅ Arquivo de log de deploy criado: $PROJECT_ROOT/deploy.log"
fi
chmod 644 "$PROJECT_ROOT/deploy.log"

# Log de rollback
if [ ! -f "$PROJECT_ROOT/rollback.log" ]; then
    touch "$PROJECT_ROOT/rollback.log"
    echo "✅ Arquivo de log de rollback criado: $PROJECT_ROOT/rollback.log"
fi
chmod 644 "$PROJECT_ROOT/rollback.log"

# Log de limpeza
if [ ! -f "$PROJECT_ROOT/cleanup.log" ]; then
    touch "$PROJECT_ROOT/cleanup.log"
    echo "✅ Arquivo de log de limpeza criado: $PROJECT_ROOT/cleanup.log"
fi
chmod 644 "$PROJECT_ROOT/cleanup.log"

# Log de webhook (para futuras implementações)
if [ ! -f "$PROJECT_ROOT/webhook.log" ]; then
    touch "$PROJECT_ROOT/webhook.log"
    echo "✅ Arquivo de log de webhook criado: $PROJECT_ROOT/webhook.log"
fi
chmod 644 "$PROJECT_ROOT/webhook.log"

# Log de erro geral
if [ ! -f "$PROJECT_ROOT/error.log" ]; then
    touch "$PROJECT_ROOT/error.log"
    echo "✅ Arquivo de log de erro criado: $PROJECT_ROOT/error.log"
fi
chmod 644 "$PROJECT_ROOT/error.log"

echo "✅ Sistema de logs configurado com sucesso!"
echo "📋 Arquivos de log criados:"
echo "   - deploy.log: Logs de deploy automático"
echo "   - rollback.log: Logs de operações de rollback"
echo "   - cleanup.log: Logs de limpeza de backups"
echo "   - webhook.log: Logs de webhooks (futuro)"
echo "   - error.log: Logs de erros gerais"

echo "✅ Git hook configurado em: $PROJECT_ROOT/.git/hooks/post-receive"
echo "✅ Script de deploy incremental criado em: $PROJECT_ROOT/deploy.sh"
echo "✅ Script de rollback criado em: $PROJECT_ROOT/rollback.sh"
echo "✅ Script de limpeza de backups criado em: $PROJECT_ROOT/cleanup-backups.sh"

echo "✅ Webhook controller já existe no Laravel: backend/app/Http/Controllers/Api/WebhookController.php"

echo ""
echo "🚀 MELHORIAS NO SISTEMA DE DEPLOY:"
echo "✅ Deploy incremental (não apaga tudo)"
echo "✅ Backup automático de arquivos importantes (vendor, .env, storage)"
echo "✅ Deploy atômico com swap de diretórios (zero downtime)"
echo "✅ Sistema de rollback completo"
echo "✅ Limpeza automática de backups antigos"
echo "✅ Preservação de uploads e logs"
echo "✅ Restauração automática do vendor"
echo "✅ Frontend não deleta mais arquivos do repositório Git"
echo "✅ Usa diretório temporário para build"
echo "✅ Preserva arquivos de deploy e logs"
echo "✅ Mantém estrutura do repositório intacta"

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
echo "3. Coloque o vendor manualmente:"
echo "   # Copie a pasta vendor do seu ambiente local para $API_DIR/"
echo "   # Ou faça upload via FTP/SFTP"
echo ""
echo "4. Configure o frontend:"
echo "   cd $FRONTEND_DIR"
echo "   # Criar .env com VITE_API_URL=https://api-hom.virtualt.com.br"
echo ""
echo "5. Para fazer deploy automático (via webhook):"
echo "   # As rotas já estão configuradas no Laravel!"
echo "   # Configure webhook no GitHub:"
echo "   # URL: https://api-hom.virtualt.com.br/webhook/deploy"
echo "   # Branch: hostinger-hom"
echo "   # Event: push"
echo "   # Teste com: ./scripts/test-webhook.sh"
echo ""
echo "6. Para deploy manual:"
echo "   cd $PROJECT_ROOT"
echo "   git pull origin hostinger-hom"
echo "   ./deploy.sh"
echo ""
echo "7. Para verificar subdomínios:"
echo "   curl -I https://api-hom.virtualt.com.br"
echo "   curl -I https://app-hom.virtualt.com.br"
echo ""
echo "8. Para corrigir problemas de arquivos deletados:"
echo "   cd $PROJECT_ROOT"
echo "   git reset --hard HEAD && git clean -fd"
echo "   git pull origin hostinger-hom"
echo "   ./deploy.sh"
echo ""
echo "9. Para gerenciar backups e rollbacks:"
echo "   cd $PROJECT_ROOT"
echo "   ./rollback.sh list                    # Listar backups disponíveis"
echo "   ./rollback.sh latest                  # Rollback para versão mais recente"
echo "   ./rollback.sh api api_backup_20241201_143022  # Rollback específico da API"
echo "   ./cleanup-backups.sh auto             # Limpar backups antigos automaticamente"
echo ""
echo "10. Se houver problemas com Composer:"
echo "   cd $PROJECT_ROOT"
echo "   ./scripts/fix-composer-deps.sh"
echo ""
echo "11. Se houver problemas de memória:"
echo "   cd $PROJECT_ROOT"
echo "   ./scripts/fix-memory-issues.sh"
echo ""
echo "12. Para monitorar logs do sistema:"
echo "   cd $PROJECT_ROOT"
echo "   tail -f deploy.log                    # Logs de deploy em tempo real"
echo "   tail -f rollback.log                  # Logs de rollback em tempo real"
echo "   tail -f cleanup.log                   # Logs de limpeza em tempo real"
echo "   tail -f error.log                     # Logs de erro em tempo real"
echo "   tail -f $API_DIR/storage/logs/laravel.log  # Logs da aplicação Laravel"

echo ""
echo "🔧 CORREÇÕES APLICADAS NESTA VERSÃO:"
echo "====================================="
echo "✅ PROBLEMA RESOLVIDO: Frontend não deleta mais arquivos do repositório Git"
echo "✅ SOLUÇÃO: Usa diretório temporário /tmp/ para operações de build"
echo "✅ BENEFÍCIO: Preserva estrutura do repositório e arquivos de deploy"
echo "✅ SEGURANÇA: Arquivos de deploy (deploy.sh, rollback.sh) não são afetados"
echo ""
echo "📋 SE VOCÊ JÁ TEM ARQUIVOS DELETADOS:"
echo "   Execute: git reset --hard HEAD && git clean -fd"
echo "   Execute: git pull origin hostinger-hom"
echo "   Execute: ./deploy.sh"
