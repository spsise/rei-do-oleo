# 🚀 Git Hooks com Subdomínios - Resumo Executivo

## 📋 **CONFIGURAÇÃO PARA SUBDOMÍNIOS SEPARADOS**

### **Subdomínios Configurados:**

- **API**: `api-hom.virtualt.com.br` (Laravel)
- **Frontend**: `app-hom.virtualt.com.br` (React)

---

## 🎯 **DIFERENÇAS PARA SUBDOMÍNIOS**

### **Estrutura de Diretórios:**

```
/home/usuario/
├── .git/                          # Repositório Git
├── api-hom.virtualt.com.br/       # Backend Laravel
├── app-hom.virtualt.com.br/       # Frontend React
├── deploy.sh                      # Script de deploy manual
├── check-subdomains.sh            # Script de verificação
└── deploy.log                     # Logs de deploy
```

### **Principais Mudanças:**

- ✅ **Diretórios separados** para cada subdomínio
- ✅ **Configuração CORS** entre subdomínios
- ✅ **URLs específicas** para cada ambiente
- ✅ **Script adaptado** para subdomínios

---

## 🚀 **PASSO A PASSO RÁPIDO**

### **PASSO 1: Configurar Subdomínios na Hostinger**

1. Painel → Domínios → virtualt.com.br
2. Subdomínios → Adicionar Subdomínio
3. **API**: `api-hom` → `/home/usuario/api-hom.virtualt.com.br`
4. **App**: `app-hom` → `/home/usuario/app-hom.virtualt.com.br`

### **PASSO 2: Preparação Local**

```bash
# Preparar .env para subdomínios
cd backend
cp .env.example .env.production
# Configurar: APP_URL=https://api-hom.virtualt.com.br

cd frontend
cp .env.example .env.production
# Configurar: VITE_API_URL=https://api-hom.virtualt.com.br
```

### **PASSO 3: Upload e Configuração**

```bash
# Upload do script específico
scp scripts/setup-git-hooks-subdomains.sh usuario@virtualt.com.br:~/

# Upload dos .env
scp backend/.env.production usuario@virtualt.com.br:~/backend.env
scp frontend/.env.production usuario@virtualt.com.br:~/frontend.env

# Acessar servidor
ssh usuario@virtualt.com.br

# Executar configuração
chmod +x setup-git-hooks-subdomains.sh
./setup-git-hooks-subdomains.sh
```

### **PASSO 4: Configurar Repositório**

```bash
# No servidor
cd ~
git remote set-url origin https://github.com/SEU_USUARIO/rei-do-oleo.git
```

### **PASSO 5: Configurar Ambiente**

```bash
# Backend
cd ~/api-hom.virtualt.com.br
mv ~/backend.env .env
chmod 644 .env

# Frontend
cd ~/app-hom.virtualt.com.br
mv ~/frontend.env .env
chmod 644 .env
```

### **PASSO 6: Primeiro Deploy**

```bash
# No seu computador
git add .
git commit -m "🚀 Setup subdomínios separados"
git push origin main
```

### **PASSO 7: Verificar Deploy**

```bash
# No servidor
tail -f ~/deploy.log
./check-subdomains.sh
```

---

## 🔧 **CONFIGURAÇÕES NECESSÁRIAS**

### **Backend (.env)**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api-hom.virtualt.com.br
DB_HOST=localhost
DB_DATABASE=rei_do_oleo_hom
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
CORS_ALLOWED_ORIGINS=https://app-hom.virtualt.com.br
```

### **Frontend (.env)**

```env
VITE_API_URL=https://api-hom.virtualt.com.br
VITE_APP_URL=https://app-hom.virtualt.com.br
VITE_APP_ENV=homologation
```

---

## 🧪 **TESTE RÁPIDO**

### **1. Testar Subdomínios**

```bash
# Testar API
curl https://api-hom.virtualt.com.br/api/health

# Testar Frontend
curl https://app-hom.virtualt.com.br
```

### **2. Testar CORS**

```bash
# No navegador, acessar:
# https://app-hom.virtualt.com.br
# Verificar se consegue fazer requisições para a API
```

### **3. Fazer Alteração de Teste**

```bash
# No seu computador
echo "<!-- Test subdomains -->" >> frontend/index.html
git add .
git commit -m "🧪 Test subdomains"
git push origin main
```

---

## 🚨 **PROBLEMAS COMUNS - SUBDOMÍNIOS**

### **Erro de CORS**

```bash
# Verificar configuração CORS no Laravel
cd ~/api-hom.virtualt.com.br
nano config/cors.php
# Adicionar: 'allowed_origins' => ['https://app-hom.virtualt.com.br']
```

### **Subdomínio não responde**

```bash
# Verificar configuração na Hostinger
# Painel → Domínios → Subdomínios
# Verificar Document Root

# Verificar permissões
chmod -R 755 ~/api-hom.virtualt.com.br
chmod -R 755 ~/app-hom.virtualt.com.br
```

### **API não carrega**

```bash
# Verificar .htaccess e logs
cat ~/api-hom.virtualt.com.br/.htaccess
tail -f ~/api-hom.virtualt.com.br/storage/logs/laravel.log
chmod -R 755 ~/api-hom.virtualt.com.br/storage
```

---

## 📊 **COMANDOS ÚTEIS**

### **Verificar Status**

```bash
# Script de verificação específico
./check-subdomains.sh
```

### **Deploy Manual**

```bash
# No servidor
cd ~
./deploy.sh
```

### **Logs**

```bash
# Logs do deploy
tail -f ~/deploy.log

# Logs do Laravel
tail -f ~/api-hom.virtualt.com.br/storage/logs/laravel.log
```

---

## ✅ **CHECKLIST - SUBDOMÍNIOS**

- [ ] ✅ Subdomínios criados na Hostinger
- [ ] ✅ Document Root configurado corretamente
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
✅ **Deploy automático** no `git push origin main`
✅ **CORS configurado** entre os subdomínios
✅ **Build otimizado** para produção
✅ **Backup automático** antes do deploy
✅ **Logs detalhados** do processo

**🚀 Seu sistema de deploy automatizado com subdomínios separados está pronto!**

---

## 📖 **DOCUMENTAÇÃO COMPLETA**

Para detalhes completos, consulte:

- 📖 **Guia Detalhado**: `docs/GIT_HOOKS_SUBDOMAINS.md`
- 🚀 **Script de Configuração**: `scripts/setup-git-hooks-subdomains.sh`
- 🔍 **Script de Verificação**: `scripts/verify-setup.sh`

---

## 🔄 **MIGRAÇÃO DE ESTRUTURA SIMPLES**

Se você já tem a configuração simples e quer migrar para subdomínios:

1. **Fazer backup** da configuração atual
2. **Criar subdomínios** na Hostinger
3. **Executar script** de configuração para subdomínios
4. **Atualizar URLs** nos arquivos .env
5. **Fazer deploy** para testar

**🎯 A configuração com subdomínios oferece melhor organização e separação de responsabilidades!**
