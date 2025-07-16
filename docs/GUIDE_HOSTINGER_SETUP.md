# 🚀 Guia Completo - Deploy na Hostinger

## 📋 Resumo do Problema

O deploy automático não está funcionando porque o **Git Hook não está configurado no servidor**. Os subdomínios estão retornando:

- **API**: HTTP 000 (não responde)
- **Frontend**: HTTP 403 (Forbidden)

## 🔧 Solução Passo a Passo

### 1. Conectar no Servidor Hostinger

```bash
ssh usuario@servidor
```

### 2. Verificar Estrutura Atual

```bash
# Verificar diretório atual
pwd
ls -la

# Verificar se já existe configuração Git
ls -la .git/
```

### 3. Configurar Git Hook (Método Recomendado)

#### Opção A: Usar Script Automático

```bash
# Baixar o script de configuração
wget https://raw.githubusercontent.com/spsise/rei-do-oleo/hostinger-hom/scripts/setup-git-hooks-subdomains.sh

# Tornar executável
chmod +x setup-git-hooks-subdomains.sh

# Executar configuração
./setup-git-hooks-subdomains.sh
```

#### Opção B: Configuração Manual

```bash
# 1. Inicializar Git (se não existir)
git init
git remote add origin https://github.com/spsise/rei-do-oleo.git

# 2. Criar diretórios dos subdomínios
mkdir -p api-hom.virtualt.com.br
mkdir -p app-hom.virtualt.com.br

# 3. Fazer checkout da branch
git fetch origin
git checkout hostinger-hom

# 4. Configurar Git Hook
cat > .git/hooks/post-receive << 'EOF'
#!/bin/bash
set -e

echo "🚀 Iniciando deploy automático..."

PROJECT_ROOT="/home/$(whoami)"
API_DIR="$PROJECT_ROOT/api-hom.virtualt.com.br"
FRONTEND_DIR="$PROJECT_ROOT/app-hom.virtualt.com.br"
BRANCH="hostinger-hom"

while read oldrev newrev refname; do
    branch=$(git rev-parse --symbolic --abbrev-ref $refname)

    if [ "$branch" = "$BRANCH" ]; then
        echo "📦 Deployando branch: $branch"

        # Checkout dos arquivos
        git --work-tree="$PROJECT_ROOT" --git-dir="$PROJECT_ROOT/.git" checkout -f $BRANCH

        # Deploy Backend
        if [ -d "backend" ]; then
            echo "🔧 Configurando Laravel API..."
            rm -rf "$API_DIR"/*
            cp -r backend/* "$API_DIR/"

            cd "$API_DIR"
            composer install --no-dev --optimize-autoloader

            if [ ! -f ".env" ]; then
                cp .env.example .env
                php artisan key:generate
            fi

            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan migrate --force

            chmod -R 755 storage
            chmod -R 755 bootstrap/cache
            chmod 644 .env

            echo "✅ Backend configurado"
        fi

        # Deploy Frontend
        if [ -d "frontend" ]; then
            echo "⚛️ Configurando React App..."
            rm -rf "$FRONTEND_DIR"/*

            cd frontend
            npm ci
            npm run build
            cp -r dist/* "$FRONTEND_DIR/"
            cd ..

            chmod -R 755 "$FRONTEND_DIR"
            echo "✅ Frontend configurado"
        fi

        # Limpar arquivos temporários
        rm -rf backend/ frontend/ scripts/ docs/ .github/ docker/
        rm -f *.md *.yml *.json *.lock

        echo "🎉 Deploy concluído!"
        echo "$(date): Deploy realizado" >> "$PROJECT_ROOT/deploy.log"
    fi
done
EOF

# 5. Tornar hook executável
chmod +x .git/hooks/post-receive
```

### 4. Configurar Variáveis de Ambiente

#### Backend (Laravel)

```bash
cd api-hom.virtualt.com.br

# Copiar arquivo de ambiente
cp .env.example .env

# Editar configurações
nano .env
```

**Configurações importantes no .env:**

```env
APP_NAME="Rei do Óleo"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api-hom.virtualt.com.br

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

#### Frontend (React)

```bash
cd app-hom.virtualt.com.br

# Criar arquivo de ambiente
cat > .env << 'EOF'
VITE_APP_NAME="Rei do Óleo"
VITE_API_URL=https://api-hom.virtualt.com.br
VITE_APP_URL=https://app-hom.virtualt.com.br
VITE_APP_ENV=production
EOF
```

### 5. Configurar Permissões

```bash
# Permissões para API
chmod -R 755 api-hom.virtualt.com.br/
chmod -R 755 api-hom.virtualt.com.br/storage/
chmod -R 755 api-hom.virtualt.com.br/bootstrap/cache/
chmod 644 api-hom.virtualt.com.br/.env

# Permissões para Frontend
chmod -R 755 app-hom.virtualt.com.br/
chmod 644 app-hom.virtualt.com.br/*.html
chmod 644 app-hom.virtualt.com.br/*.css
chmod 644 app-hom.virtualt.com.br/*.js
```

### 6. Configurar .htaccess

#### API (.htaccess)

```bash
cd api-hom.virtualt.com.br

cat > .htaccess << 'EOF'
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
EOF
```

#### Frontend (.htaccess)

```bash
cd app-hom.virtualt.com.br

cat > .htaccess << 'EOF'
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
EOF
```

### 7. Testar Deploy

```bash
# Fazer deploy manual
cd /home/usuario
git pull origin hostinger-hom

# Verificar se os arquivos foram criados
ls -la api-hom.virtualt.com.br/
ls -la app-hom.virtualt.com.br/

# Verificar logs
tail -f deploy.log
```

### 8. Verificar Subdomínios

```bash
# Testar API
curl -I https://api-hom.virtualt.com.br

# Testar Frontend
curl -I https://app-hom.virtualt.com.br
```

## 🔍 Troubleshooting

### Problema: HTTP 403 (Forbidden)

```bash
# Verificar permissões
ls -la api-hom.virtualt.com.br/
ls -la app-hom.virtualt.com.br/

# Corrigir permissões
chmod -R 755 api-hom.virtualt.com.br/
chmod -R 755 app-hom.virtualt.com.br/
```

### Problema: HTTP 404 (Not Found)

```bash
# Verificar se os arquivos existem
ls -la api-hom.virtualt.com.br/index.php
ls -la app-hom.virtualt.com.br/index.html

# Verificar se o deploy foi executado
tail -f deploy.log
```

### Problema: HTTP 500 (Internal Server Error)

```bash
# Verificar logs do Laravel
tail -f api-hom.virtualt.com.br/storage/logs/laravel.log

# Verificar configuração do .env
cat api-hom.virtualt.com.br/.env

# Verificar conectividade com banco
cd api-hom.virtualt.com.br
php artisan tinker
```

### Problema: Git Hook não executa

```bash
# Verificar se o hook existe
ls -la .git/hooks/post-receive

# Verificar se é executável
chmod +x .git/hooks/post-receive

# Testar hook manualmente
echo "test" | .git/hooks/post-receive
```

## 📞 Comandos Úteis

### Verificar Status

```bash
# Status dos subdomínios
curl -I https://api-hom.virtualt.com.br
curl -I https://app-hom.virtualt.com.br

# Status do Git
git status
git log --oneline -5

# Status do deploy
tail -f deploy.log
```

### Deploy Manual

```bash
# Deploy completo
cd /home/usuario
git pull origin hostinger-hom

# Deploy apenas backend
cd api-hom.virtualt.com.br
composer install --no-dev
php artisan config:cache
php artisan migrate --force

# Deploy apenas frontend
cd frontend
npm ci
npm run build
cp -r dist/* ../app-hom.virtualt.com.br/
```

### Logs e Debug

```bash
# Logs do deploy
tail -f deploy.log

# Logs do Laravel
tail -f api-hom.virtualt.com.br/storage/logs/laravel.log

# Logs do servidor web
tail -f /var/log/apache2/error.log
```

## ✅ Checklist Final

- [ ] Git Hook configurado e executável
- [ ] Diretórios dos subdomínios criados
- [ ] Variáveis de ambiente configuradas
- [ ] Permissões corretas (755/644)
- [ ] Arquivos .htaccess configurados
- [ ] Banco de dados configurado
- [ ] Migrações executadas
- [ ] Subdomínios respondendo (HTTP 200)
- [ ] Deploy automático funcionando

## 🎯 Próximos Passos

1. **Execute o setup no servidor** usando as instruções acima
2. **Configure as variáveis de ambiente** (banco de dados, URLs)
3. **Teste o deploy manual** primeiro
4. **Faça push da branch** para testar o deploy automático
5. **Monitore os logs** para identificar problemas
6. **Verifique os subdomínios** após o deploy

---

**📞 Suporte**: Se encontrar problemas, verifique os logs e use os comandos de troubleshooting acima.
