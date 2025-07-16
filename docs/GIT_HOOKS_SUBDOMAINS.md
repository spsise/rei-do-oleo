# 🚀 Git Hooks com Subdomínios Separados - Hostinger

## 📋 **VISÃO GERAL**

Este guia apresenta a configuração de **Git Hooks para subdomínios separados**:

- **API**: `api-hom.virtualt.com.br` (Laravel)
- **Frontend**: `app-hom.virtualt.com.br` (React)

---

## 🎯 **ESTRUTURA DE SUBDOMÍNIOS**

### **Configuração dos Subdomínios**

```
virtualt.com.br/
├── api-hom.virtualt.com.br/     # Backend Laravel
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   ├── .htaccess
│   └── index.php
└── app-hom.virtualt.com.br/     # Frontend React
    ├── index.html
    ├── assets/
    ├── .htaccess
    └── [arquivos buildados]
```

---

## 🔧 **PASSO A PASSO - SUBDOMÍNIOS**

### **PASSO 1: Configurar Subdomínios na Hostinger**

1. **Acessar Painel Hostinger**

   - Painel → Domínios → virtualt.com.br
   - Subdomínios → Adicionar Subdomínio

2. **Criar Subdomínio API**

   - Nome: `api-hom`
   - Diretório: `api-hom.virtualt.com.br`
   - Document Root: `/home/usuario/api-hom.virtualt.com.br`

3. **Criar Subdomínio App**
   - Nome: `app-hom`
   - Diretório: `app-hom.virtualt.com.br`
   - Document Root: `/home/usuario/app-hom.virtualt.com.br`

### **PASSO 2: Verificar Status do Git (IMPORTANTE)**

**⚠️ ANTES de configurar, verifique se já existe um diretório .git:**

```bash
# 1. Upload do script de verificação
scp scripts/check-git-status.sh usuario@virtualt.com.br:~/

# 2. Acessar servidor
ssh usuario@virtualt.com.br

# 3. Executar verificação
chmod +x check-git-status.sh
./check-git-status.sh
```

**O script irá:**

- ✅ Verificar se existe um .git
- ✅ Identificar se é do projeto correto
- ✅ Mostrar configuração atual
- ✅ Oferecer opções de limpeza/backup
- ✅ Permitir decisão segura

### **PASSO 3: Preparação Local**

```bash
# 1. Verificar estrutura do projeto
ls -la
# Deve mostrar: backend/, frontend/, scripts/

# 2. Preparar arquivos .env para subdomínios
cd backend
cp .env.example .env.production
nano .env.production
# Configurar:
# APP_ENV=production
# APP_DEBUG=false
# APP_URL=https://api-hom.virtualt.com.br
# DB_HOST=localhost
# DB_DATABASE=seu_banco
# DB_USERNAME=seu_usuario
# DB_PASSWORD=sua_senha
cd ..

cd frontend
cp .env.example .env.production
nano .env.production
# Configurar:
# VITE_API_URL=https://api-hom.virtualt.com.br
# VITE_APP_URL=https://app-hom.virtualt.com.br
cd ..
```

### **PASSO 4: Upload e Configuração**

```bash
# 1. Upload do script específico para subdomínios
scp scripts/setup-git-hooks-subdomains.sh usuario@virtualt.com.br:~/

# 2. Upload dos arquivos .env
scp backend/.env.production usuario@virtualt.com.br:~/backend.env
scp frontend/.env.production usuario@virtualt.com.br:~/frontend.env

# 3. Acessar servidor
ssh usuario@virtualt.com.br

# 4. Executar configuração (agora com verificação automática)
chmod +x setup-git-hooks-subdomains.sh
./setup-git-hooks-subdomains.sh
```

**O script agora irá:**

- ✅ Verificar se já existe um .git
- ✅ Identificar se é do projeto correto
- ✅ Perguntar se deve sobrescrever (se for outro projeto)
- ✅ Configurar automaticamente se for o projeto correto

### **PASSO 5: Configurar Repositório**

```bash
# No servidor
cd ~
git remote set-url origin https://github.com/spsise/rei-do-oleo.git

# Configurar credenciais (se necessário)
git config user.name "Deploy Bot"
git config user.email "deploy@virtualt.com.br"
```

### **PASSO 6: Configurar Ambiente**

```bash
# 1. Configurar backend
mkdir -p api-hom.virtualt.com.br
mv ~/backend.env api-hom.virtualt.com.br/.env
chmod 644 api-hom.virtualt.com.br/.env

# 2. Configurar frontend
mkdir -p app-hom.virtualt.com.br
mv ~/frontend.env app-hom.virtualt.com.br/.env
chmod 644 app-hom.virtualt.com.br/.env
```

### **PASSO 7: Primeiro Deploy**

```bash
# No seu computador local
git add .
git commit -m "🚀 Setup subdomínios separados"
git push origin hostinger-hom
```

### **PASSO 8: Verificar Deploy**

```bash
# No servidor
tail -f deploy.log

# Verificar estrutura
ls -la api-hom.virtualt.com.br/
ls -la app-hom.virtualt.com.br/

# Testar subdomínios
./check-subdomains.sh
```

---

## 🔧 **CONFIGURAÇÕES ESPECÍFICAS**

### **Backend Laravel (.env)**

```env
APP_NAME="Rei do Óleo API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api-hom.virtualt.com.br

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=rei_do_oleo_hom
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# CORS para subdomínios
CORS_ALLOWED_ORIGINS=https://app-hom.virtualt.com.br
```

### **Frontend React (.env)**

```env
VITE_API_URL=https://api-hom.virtualt.com.br
VITE_APP_NAME="Rei do Óleo"
VITE_APP_URL=https://app-hom.virtualt.com.br
VITE_APP_ENV=homologation
```

---

## 🔒 **CONFIGURAÇÕES DE SEGURANÇA**

### **API .htaccess (api-hom.virtualt.com.br)**

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Access-Control-Allow-Origin "https://app-hom.virtualt.com.br"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
```

### **Frontend .htaccess (app-hom.virtualt.com.br)**

```apache
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
```

---

## 🧪 **TESTE DOS SUBDOMÍNIOS**

### **1. Teste da API**

```bash
# Testar se a API  responde
curl -I https://api-hom.virtualt.com.br

# Testar endpoint específico
curl https://api-hom.virtualt.com.br/api/health

# Testar CORS
curl -H "Origin: https://app-hom.virtualt.com.br" \
     -H "Access-Control-Request-Method: GET" \
     -H "Access-Control-Request-Headers: X-Requested-With" \
     -X OPTIONS https://api-hom.virtualt.com.br/api/health
```

### **2. Teste do Frontend**

```bash
# Testar se o frontend carrega
curl -I https://app-hom.virtualt.com.br

# Verificar se os assets carregam
curl -I https://app-hom.virtualt.com.br/assets/index-*.js
```

### **3. Teste de Integração**

```bash
# No navegador, acessar:
# https://app-hom.virtualt.com.br
# Verificar se consegue fazer requisições para a API
```

---

## 🚨 **TROUBLESHOOTING - SUBDOMÍNIOS**

### **Problema 1: CORS Error**

```bash
# Verificar configuração CORS no Laravel
cd ~/api-hom.virtualt.com.br
nano config/cors.php

# Adicionar:
'allowed_origins' => ['https://app-hom.virtualt.com.br'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

### **Problema 2: Subdomínio não responde**

```bash
# Verificar configuração na Hostinger
# Painel → Domínios → Subdomínios
# Verificar se o Document Root está correto

# Verificar permissões
chmod -R 755 ~/api-hom.virtualt.com.br
chmod -R 755 ~/app-hom.virtualt.com.br
```

### **Problema 3: API não carrega**

```bash
# Verificar .htaccess da API
cat ~/api-hom.virtualt.com.br/.htaccess

# Verificar logs do Laravel
tail -f ~/api-hom.virtualt.com.br/storage/logs/laravel.log

# Verificar permissões do storage
chmod -R 755 ~/api-hom.virtualt.com.br/storage
chmod -R 755 ~/api-hom.virtualt.com.br/bootstrap/cache
```

### **Problema 4: Frontend não carrega**

```bash
# Verificar se o build foi gerado
ls -la ~/app-hom.virtualt.com.br/

# Verificar .htaccess do frontend
cat ~/app-hom.virtualt.com.br/.htaccess

# Verificar se o VITE_API_URL está correto
cat ~/app-hom.virtualt.com.br/.env
```

### **Problema 5: Conflito com .git existente**

```bash
# Executar script de verificação
./check-git-status.sh

# Ou verificar manualmente
ls -la ~/.git
cat ~/.git/config

# Se for outro projeto, fazer backup e limpar
mkdir ~/git-backup
cp -r ~/.git ~/git-backup/
rm -rf ~/.git
```

---

## 📊 **MONITORAMENTO**

### **Script de Verificação**

```bash
# Executar verificação automática
./check-subdomains.sh
```

### **Logs de Deploy**

```bash
# Logs do deploy
tail -f ~/deploy.log

# Logs do Laravel
tail -f ~/api-hom.virtualt.com.br/storage/logs/laravel.log
```

### **Health Check**

```bash
# Verificar status dos subdomínios
curl -f https://api-hom.virtualt.com.br/api/health || echo "API com problema"
curl -f https://app-hom.virtualt.com.br || echo "Frontend com problema"
```

---

## 🔄 **COMANDOS ÚTEIS**

### **Deploy Manual**

```bash
# No servidor
cd ~
./deploy.sh
```

### **Verificar Status**

```bash
# Script de verificação
./check-subdomains.sh
```

### **Rollback**

```bash
# Ver commits recentes
cd ~
git log --oneline -5

# Fazer rollback
git reset --hard COMMIT_ID
```

---

## ✅ **CHECKLIST - SUBDOMÍNIOS**

- [ ] ✅ Subdomínios criados na Hostinger
- [ ] ✅ Document Root configurado corretamente
- [ ] ✅ Status do Git verificado (check-git-status.sh)
- [ ] ✅ Script de configuração executado
- [ ] ✅ Repositório configurado
- [ ] ✅ Arquivos .env configurados
- [ ] ✅ Primeiro deploy realizado
- [ ] ✅ API respondendo em api-hom.virtualt.com.br
- [ ] ✅ Frontend carregando em app-hom.virtualt.com.br
- [ ] ✅ CORS configurado corretamente
- [ ] ✅ Deploy automático funcionando

---

## 🎉 **RESULTADO FINAL**

Após a configuração, você terá:

✅ **API Laravel** rodando em `https://api-hom.virtualt.com.br`
✅ **Frontend React** rodando em `https://app-hom.virtualt.com.br`
✅ **Deploy automático** no `git push origin hostinger-hom`
✅ **CORS configurado** entre os subdomínios
✅ **Build otimizado** para produção
✅ **Backup automático** antes do deploy
✅ **Logs detalhados** do processo

**🚀 Seu sistema de deploy automatizado com subdomínios separados está pronto!**

---

## 📖 **DOCUMENTAÇÃO RELACIONADA**

- 📖 **Guia Detalhado**: `docs/GIT_HOOKS_SETUP.md`
- 🚀 **Script de Configuração**: `scripts/setup-git-hooks-subdomains.sh`
- 🔍 **Script de Verificação**: `scripts/verify-setup.sh`
- 🔍 **Script de Status Git**: `scripts/check-git-status.sh`
