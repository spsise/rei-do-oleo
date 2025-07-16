# 🚀 Configuração Detalhada - Git Hooks na Hostinger

## 📋 **VISÃO GERAL**

Este guia apresenta um **passo a passo completo** para configurar deploy automatizado via Git Hooks na hospedagem compartilhada da Hostinger.

### **O que é Git Hooks?**

Git Hooks são scripts que são executados automaticamente quando certas ações do Git acontecem. No nosso caso, usaremos o `post-receive` hook para fazer deploy automático quando você fizer `git push`.

---

## 🎯 **PRÉ-REQUISITOS**

### **1. Acesso SSH à Hostinger**

- ✅ Conta Hostinger com SSH habilitado
- ✅ Credenciais de acesso SSH
- ✅ Terminal/SSH client

### **2. Repositório GitHub**

- ✅ Projeto no GitHub
- ✅ Acesso de escrita ao repositório
- ✅ Branch `main` configurada

### **3. Conhecimentos Básicos**

- ✅ Comandos básicos de terminal
- ✅ Conceitos básicos de Git
- ✅ Estrutura do projeto Laravel + React

---

## 🔧 **PASSO 1: PREPARAÇÃO LOCAL**

### **1.1 Verificar Estrutura do Projeto**

```bash
# No diretório raiz do projeto
ls -la
# Deve mostrar: backend/, frontend/, scripts/, etc.

# Verificar se o script existe
ls -la scripts/setup-git-hooks.sh
```

### **1.2 Testar Build Local**

```bash
# Testar build do frontend
cd frontend
npm ci
npm run build
cd ..

# Testar build do backend
cd backend
composer install --no-dev --optimize-autoloader
php artisan config:cache
cd ..
```

### **1.3 Preparar Arquivos de Configuração**

```bash
# Backend - Criar .env de produção
cd backend
cp .env.example .env.production
nano .env.production
# Configurar:
# APP_ENV=production
# APP_DEBUG=false
# APP_URL=https://seudominio.com/api
# DB_HOST=localhost
# DB_DATABASE=seu_banco
# DB_USERNAME=seu_usuario
# DB_PASSWORD=sua_senha
cd ..

# Frontend - Criar .env de produção
cd frontend
cp .env.example .env.production
nano .env.production
# Configurar:
# VITE_API_URL=https://seudominio.com/api
# VITE_APP_URL=https://seudominio.com
cd ..
```

---

## 🔑 **PASSO 2: CONFIGURAÇÃO SSH**

### **2.1 Gerar Chave SSH (se necessário)**

```bash
# Gerar nova chave SSH
ssh-keygen -t rsa -b 4096 -C "deploy@seudominio.com"

# Ou usar chave existente
ls -la ~/.ssh/
```

### **2.2 Testar Conexão SSH**

```bash
# Testar conexão com Hostinger
ssh usuario@seudominio.com

# Se funcionar, você verá algo como:
# Welcome to Ubuntu 20.04.3 LTS (GNU/Linux 5.4.0-74-generic x86_64)
# usuario@hostname:~$
```

### **2.3 Configurar SSH Key (se necessário)**

```bash
# Copiar chave pública para servidor
ssh-copy-id usuario@seudominio.com

# Ou manualmente:
cat ~/.ssh/id_rsa.pub
# Copiar o conteúdo e adicionar no painel da Hostinger
```

---

## 📤 **PASSO 3: UPLOAD DO SCRIPT**

### **3.1 Fazer Upload do Script**

```bash
# Upload do script de configuração
scp scripts/setup-git-hooks.sh usuario@seudominio.com:~/

# Verificar se foi enviado
ssh usuario@seudominio.com "ls -la ~/setup-git-hooks.sh"
```

### **3.2 Upload dos Arquivos de Configuração**

```bash
# Upload do .env do backend
scp backend/.env.production usuario@seudominio.com:~/backend.env

# Upload do .env do frontend
scp frontend/.env.production usuario@seudominio.com:~/frontend.env
```

---

## ⚙️ **PASSO 4: CONFIGURAÇÃO NO SERVIDOR**

### **4.1 Acessar Servidor**

```bash
ssh usuario@seudominio.com
```

### **4.2 Verificar Estrutura do Servidor**

```bash
# Verificar diretório public_html
ls -la ~/public_html/

# Verificar se já existe algum projeto
ls -la ~/public_html/ | head -10
```

### **4.3 Executar Script de Configuração**

```bash
# Tornar script executável
chmod +x ~/setup-git-hooks.sh

# Executar script
./setup-git-hooks.sh
```

**Saída esperada:**

```
🚀 Configurando Git Hooks para deploy automático...
📁 Inicializando repositório Git...
✅ Git hook configurado em: /home/usuario/public_html/.git/hooks/post-receive
✅ Script de deploy manual criado: /home/usuario/public_html/deploy.sh
✅ .gitignore configurado

🎯 PRÓXIMOS PASSOS:
1. Configure o repositório remoto:
   cd /home/usuario/public_html
   git remote set-url origin https://github.com/SEU_USUARIO/rei-do-oleo.git
...
```

---

## 🔗 **PASSO 5: CONFIGURAÇÃO DO REPOSITÓRIO**

### **5.1 Configurar Remote Origin**

```bash
# Ainda no servidor
cd ~/public_html

# Configurar repositório remoto
git remote set-url origin https://github.com/SEU_USUARIO/rei-do-oleo.git

# Verificar configuração
git remote -v
```

### **5.2 Configurar Credenciais Git (se necessário)**

```bash
# Configurar usuário Git
git config user.name "Deploy Bot"
git config user.email "deploy@seudominio.com"

# Ou usar token de acesso pessoal
git remote set-url origin https://TOKEN@github.com/SEU_USUARIO/rei-do-oleo.git
```

---

## ⚙️ **PASSO 6: CONFIGURAÇÃO DO AMBIENTE**

### **6.1 Configurar Backend Laravel**

```bash
# Criar diretório da API
mkdir -p ~/public_html/api

# Mover arquivo .env
mv ~/backend.env ~/public_html/api/.env

# Configurar permissões
chmod 644 ~/public_html/api/.env
```

### **6.2 Configurar Frontend React**

```bash
# Mover arquivo .env do frontend
mv ~/frontend.env ~/public_html/.env.frontend

# Configurar permissões
chmod 644 ~/public_html/.env.frontend
```

### **6.3 Verificar Configurações**

```bash
# Verificar estrutura
ls -la ~/public_html/
ls -la ~/public_html/api/

# Verificar arquivos .env
cat ~/public_html/api/.env | head -5
cat ~/public_html/.env.frontend
```

---

## 🧪 **PASSO 7: TESTE INICIAL**

### **7.1 Fazer Primeiro Deploy**

```bash
# No seu computador local
git add .
git commit -m "🐘 Backend ✨ feat: Configuração inicial para deploy"
git push origin main
```

### **7.2 Monitorar Deploy no Servidor**

```bash
# No servidor, monitorar logs
tail -f ~/public_html/deploy.log

# Ou verificar processo
ps aux | grep git
```

### **7.3 Verificar Resultado**

```bash
# Verificar se os arquivos foram deployados
ls -la ~/public_html/
ls -la ~/public_html/api/

# Verificar se o Laravel está funcionando
cd ~/public_html/api
php artisan --version
```

---

## 🔧 **PASSO 8: CONFIGURAÇÕES ADICIONAIS**

### **8.1 Configurar Banco de Dados**

```bash
# Acessar MySQL (se disponível)
mysql -u usuario -p

# Ou usar phpMyAdmin via painel Hostinger
# Criar banco de dados e usuário
```

### **8.2 Executar Migrações**

```bash
# No servidor
cd ~/public_html/api

# Executar migrações
php artisan migrate --force

# Se der erro de permissão
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### **8.3 Configurar Permissões**

```bash
# Configurar permissões corretas
chmod -R 755 ~/public_html
chmod -R 644 ~/public_html/*.html
chmod -R 644 ~/public_html/*.css
chmod -R 644 ~/public_html/*.js

# Permissões específicas do Laravel
chmod -R 755 ~/public_html/api/storage
chmod -R 755 ~/public_html/api/bootstrap/cache
chmod 644 ~/public_html/api/.env
```

---

## 🧪 **PASSO 9: TESTES E VALIDAÇÃO**

### **9.1 Testar Frontend**

```bash
# Testar se o frontend carrega
curl -I https://seudominio.com

# Verificar se os assets estão carregando
curl -I https://seudominio.com/assets/index-*.js
```

### **9.2 Testar Backend**

```bash
# Testar se a API responde
curl -I https://seudominio.com/api

# Testar endpoint específico
curl https://seudominio.com/api/health
```

### **9.3 Verificar Logs**

```bash
# Logs do Laravel
tail -f ~/public_html/api/storage/logs/laravel.log

# Logs do servidor
tail -f /var/log/apache2/error.log
```

---

## 🔄 **PASSO 10: DEPLOY AUTOMÁTICO**

### **10.1 Fazer Alteração de Teste**

```bash
# No seu computador local
# Fazer uma pequena alteração
echo "<!-- Deploy test -->" >> frontend/index.html

# Commit e push
git add .
git commit -m "⚛️ Frontend 🧪 test: Teste de deploy automático"
git push origin main
```

### **10.2 Monitorar Deploy**

```bash
# No servidor
tail -f ~/public_html/deploy.log

# Verificar se a alteração foi aplicada
cat ~/public_html/index.html | grep "Deploy test"
```

---

## 🚨 **TROUBLESHOOTING**

### **Problema 1: Erro de Permissão**

```bash
# Solução
chmod -R 755 ~/public_html
chmod -R 755 ~/public_html/api/storage
chmod -R 755 ~/public_html/api/bootstrap/cache
```

### **Problema 2: Git Hook não executa**

```bash
# Verificar se o hook existe
ls -la ~/public_html/.git/hooks/post-receive

# Verificar permissões
chmod +x ~/public_html/.git/hooks/post-receive

# Testar manualmente
echo "main" | ~/public_html/.git/hooks/post-receive
```

### **Problema 3: Erro de Conexão com GitHub**

```bash
# Verificar remote
cd ~/public_html
git remote -v

# Reconfigurar com token
git remote set-url origin https://TOKEN@github.com/SEU_USUARIO/rei-do-oleo.git
```

### **Problema 4: Laravel não carrega**

```bash
# Verificar .htaccess
cat ~/public_html/api/.htaccess

# Verificar permissões
ls -la ~/public_html/api/

# Verificar logs
tail -f ~/public_html/api/storage/logs/laravel.log
```

---

## 📊 **MONITORAMENTO CONTÍNUO**

### **Script de Monitoramento**

```bash
# Criar script de monitoramento
cat > ~/monitor.sh << 'EOF'
#!/bin/bash

echo "=== Monitoramento $(date) ==="

# Verificar se a API está funcionando
if curl -s https://seudominio.com/api/health > /dev/null; then
    echo "✅ API funcionando"
else
    echo "❌ API com problema"
fi

# Verificar se o frontend está funcionando
if curl -s https://seudominio.com > /dev/null; then
    echo "✅ Frontend funcionando"
else
    echo "❌ Frontend com problema"
fi

# Verificar espaço em disco
df -h | grep public_html

# Verificar logs recentes
echo "=== Últimos logs ==="
tail -5 ~/public_html/api/storage/logs/laravel.log
EOF

chmod +x ~/monitor.sh
```

### **Agendar Monitoramento**

```bash
# Adicionar ao crontab
crontab -e

# Adicionar linha:
# */30 * * * * ~/monitor.sh >> ~/monitor.log 2>&1
```

---

## 🎯 **COMANDOS ÚTEIS**

### **Deploy Manual**

```bash
# No servidor
cd ~/public_html
./deploy.sh

# Ou via Git
git pull origin main
```

### **Rollback**

```bash
# Ver commits recentes
cd ~/public_html
git log --oneline -5

# Fazer rollback
git reset --hard COMMIT_ID
```

### **Logs e Debug**

```bash
# Logs do deploy
tail -f ~/public_html/deploy.log

# Logs do Laravel
tail -f ~/public_html/api/storage/logs/laravel.log

# Logs do servidor
tail -f /var/log/apache2/error.log
```

---

## ✅ **CHECKLIST FINAL**

- [ ] ✅ SSH configurado e funcionando
- [ ] ✅ Script de setup executado com sucesso
- [ ] ✅ Repositório remoto configurado
- [ ] ✅ Arquivos .env configurados
- [ ] ✅ Primeiro deploy realizado
- [ ] ✅ Frontend carregando corretamente
- [ ] ✅ API respondendo
- [ ] ✅ Deploy automático funcionando
- [ ] ✅ Monitoramento configurado
- [ ] ✅ Backup automático funcionando

---

## 🎉 **RESULTADO FINAL**

Após seguir todos os passos, você terá:

✅ **Deploy automático** no `git push origin main`
✅ **Build otimizado** para produção
✅ **Backup automático** antes do deploy
✅ **Rollback fácil** se necessário
✅ **Logs detalhados** do processo
✅ **Monitoramento** contínuo
✅ **Configuração de segurança** adequada

**🚀 Seu sistema de deploy automatizado está pronto para uso!**
