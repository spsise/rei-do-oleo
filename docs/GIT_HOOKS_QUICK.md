# 🚀 Git Hooks na Hostinger - Passo a Passo Rápido

## 📋 **RESUMO EXECUTIVO**

Este é o **passo a passo simplificado** para configurar deploy automatizado via Git Hooks na Hostinger.

---

## 🎯 **PRÉ-REQUISITOS**

- ✅ Conta Hostinger com SSH habilitado
- ✅ Repositório GitHub do projeto
- ✅ Acesso SSH ao servidor
- ✅ Terminal/SSH client

---

## 🚀 **PASSO A PASSO RÁPIDO**

### **PASSO 1: Preparação Local**

```bash
# 1. Verificar estrutura do projeto
ls -la
# Deve mostrar: backend/, frontend/, scripts/

# 2. Testar build local
cd frontend && npm ci && npm run build && cd ..
cd backend && composer install --no-dev && cd ..

# 3. Preparar arquivos .env
cp backend/.env.example backend/.env.production
cp frontend/.env.example frontend/.env.production
# Editar os arquivos com as configurações de produção
```

### **PASSO 2: Configurar SSH**

```bash
# 1. Testar conexão SSH
ssh usuario@seudominio.com

# 2. Se não funcionar, configurar chave SSH
ssh-keygen -t rsa -b 4096
ssh-copy-id usuario@seudominio.com
```

### **PASSO 3: Upload e Configuração**

```bash
# 1. Upload do script
scp scripts/setup-git-hooks.sh usuario@seudominio.com:~/

# 2. Upload dos arquivos .env
scp backend/.env.production usuario@seudominio.com:~/backend.env
scp frontend/.env.production usuario@seudominio.com:~/frontend.env

# 3. Acessar servidor
ssh usuario@seudominio.com

# 4. Executar configuração
chmod +x setup-git-hooks.sh
./setup-git-hooks.sh
```

### **PASSO 4: Configurar Repositório**

```bash
# No servidor
cd public_html
git remote set-url origin https://github.com/SEU_USUARIO/rei-do-oleo.git

# Configurar credenciais (se necessário)
git config user.name "Deploy Bot"
git config user.email "deploy@seudominio.com"
```

### **PASSO 5: Configurar Ambiente**

```bash
# 1. Configurar backend
mkdir -p api
mv ~/backend.env api/.env
chmod 644 api/.env

# 2. Configurar frontend
mv ~/frontend.env .env.frontend
chmod 644 .env.frontend
```

### **PASSO 6: Primeiro Deploy**

```bash
# No seu computador local
git add .
git commit -m "🚀 Initial deploy setup"
git push origin main
```

### **PASSO 7: Verificar Deploy**

```bash
# No servidor
tail -f deploy.log

# Verificar se funcionou
ls -la public_html/
ls -la public_html/api/
```

---

## 🔧 **CONFIGURAÇÕES NECESSÁRIAS**

### **Backend (.env)**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com/api
DB_HOST=localhost
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### **Frontend (.env)**

```env
VITE_API_URL=https://seudominio.com/api
VITE_APP_URL=https://seudominio.com
```

---

## 🧪 **TESTE RÁPIDO**

### **1. Fazer Alteração de Teste**

```bash
# No seu computador
echo "<!-- Test deploy -->" >> frontend/index.html
git add .
git commit -m "🧪 Test deploy"
git push origin main
```

### **2. Monitorar Deploy**

```bash
# No servidor
tail -f deploy.log
```

### **3. Verificar Resultado**

```bash
# Testar frontend
curl https://seudominio.com

# Testar API
curl https://seudominio.com/api/health
```

---

## 🚨 **PROBLEMAS COMUNS**

### **Erro: Permissão Negada**

```bash
chmod -R 755 public_html
chmod -R 755 public_html/api/storage
chmod -R 755 public_html/api/bootstrap/cache
```

### **Erro: Git Hook não executa**

```bash
chmod +x public_html/.git/hooks/post-receive
```

### **Erro: Laravel não carrega**

```bash
# Verificar .htaccess
cat public_html/api/.htaccess

# Verificar logs
tail -f public_html/api/storage/logs/laravel.log
```

---

## 📊 **COMANDOS ÚTEIS**

### **Deploy Manual**

```bash
# No servidor
cd public_html
./deploy.sh
```

### **Verificar Status**

```bash
# Script de verificação
./scripts/verify-setup.sh
```

### **Logs**

```bash
# Logs do deploy
tail -f deploy.log

# Logs do Laravel
tail -f api/storage/logs/laravel.log
```

---

## ✅ **CHECKLIST FINAL**

- [ ] ✅ SSH configurado
- [ ] ✅ Script executado no servidor
- [ ] ✅ Repositório configurado
- [ ] ✅ Arquivos .env configurados
- [ ] ✅ Primeiro deploy realizado
- [ ] ✅ Frontend carregando
- [ ] ✅ API respondendo
- [ ] ✅ Deploy automático funcionando

---

## 🎉 **RESULTADO**

Após seguir estes passos, você terá:

✅ **Deploy automático** no `git push origin main`
✅ **Build otimizado** para produção
✅ **Backup automático** antes do deploy
✅ **Rollback fácil** se necessário
✅ **Logs detalhados** do processo

**🚀 Seu sistema de deploy automatizado está pronto!**

---

## 📖 **DOCUMENTAÇÃO COMPLETA**

Para detalhes completos, consulte:

- 📖 **Guia Detalhado**: `docs/GIT_HOOKS_SETUP.md`
- 🔍 **Script de Verificação**: `scripts/verify-setup.sh`
- 🚀 **Configuração Rápida**: `scripts/quick-setup.sh`
