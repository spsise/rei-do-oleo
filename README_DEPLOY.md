# 🚀 Deploy Automatizado - Hostinger

## 📋 **RESUMO EXECUTIVO**

Este documento apresenta **4 propostas** para automatizar o deploy das aplicações **Laravel API** e **React Frontend** na hospedagem compartilhada da Hostinger.

---

## 🎯 **PROPOSTAS APRESENTADAS**

### **1. 🚀 Deploy via FTP/SFTP**

- **Arquivo**: `.github/workflows/deploy-hostinger.yml`
- **Dificuldade**: ⭐⭐⭐ (Fácil)
- **Segurança**: ⭐⭐ (Média)
- **Recomendação**: Para iniciantes

### **2. 🎯 Deploy via Git Hooks (RECOMENDADO)**

- **Arquivo**: `scripts/setup-git-hooks.sh`
- **Dificuldade**: ⭐⭐⭐⭐ (Médio)
- **Segurança**: ⭐⭐⭐⭐ (Alta)
- **Recomendação**: **MELHOR OPÇÃO**

### **3. 🔐 Deploy via SSH**

- **Arquivo**: `.github/workflows/deploy-ssh.yml`
- **Dificuldade**: ⭐⭐⭐ (Médio)
- **Segurança**: ⭐⭐⭐⭐⭐ (Máxima)
- **Recomendação**: Para avançados

### **4. 🔌 Deploy via API Hostinger**

- **Arquivo**: `scripts/hostinger-deploy.js`
- **Dificuldade**: ⭐⭐ (Difícil)
- **Segurança**: ⭐⭐⭐⭐ (Alta)
- **Recomendação**: Para integração

---

## 🏆 **RECOMENDAÇÃO: GIT HOOKS**

### **Por que escolher Git Hooks?**

✅ **Vantagens:**

- Controle de versão no servidor
- Rollback fácil e rápido
- Deploy automático no push
- Backup automático
- Compatível com hospedagem compartilhada
- Configuração simples

✅ **Funcionalidades:**

- Deploy automático no `git push origin main`
- Build otimizado para produção
- Limpeza automática de arquivos de desenvolvimento
- Configuração de permissões
- Logs detalhados

---

## 🚀 **COMO IMPLEMENTAR (PASSO A PASSO)**

### **Passo 1: Configuração Rápida**

```bash
# Execute o script de configuração
./scripts/quick-setup.sh
```

### **Passo 2: Configurar Servidor**

```bash
# 1. Acessar servidor via SSH
ssh usuario@seudominio.com

# 2. Fazer upload do script
scp scripts/setup-git-hooks.sh usuario@seudominio.com:~/

# 3. Executar configuração
chmod +x setup-git-hooks.sh
./setup-git-hooks.sh
```

### **Passo 3: Configurar Ambiente**

```bash
# 1. Configurar repositório
cd public_html
git remote set-url origin https://github.com/SEU_USUARIO/rei-do-oleo.git

# 2. Configurar Laravel
cd api
cp .env.example .env
nano .env
# Configurar: DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL
```

### **Passo 4: Primeiro Deploy**

```bash
# Deploy automático
git push origin main

# Ou deploy manual
./deploy.sh
```

---

## 🔧 **CONFIGURAÇÕES NECESSÁRIAS**

### **Backend Laravel (.env)**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com/api

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### **Frontend React (.env)**

```env
VITE_API_URL=https://seudominio.com/api
VITE_APP_URL=https://seudominio.com
```

---

## 📁 **ESTRUTURA FINAL NO SERVIDOR**

```
public_html/
├── index.html          # Frontend React
├── assets/             # Arquivos estáticos
├── api/                # Backend Laravel
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
└── .htaccess
```

---

## 🔒 **SEGURANÇA**

### **Checklist de Segurança:**

- [ ] `APP_DEBUG=false` no Laravel
- [ ] Senhas fortes no banco de dados
- [ ] HTTPS habilitado
- [ ] Headers de segurança configurados
- [ ] Permissões de arquivo corretas
- [ ] Backup automático configurado

---

## 📊 **MONITORAMENTO**

### **Health Check:**

```bash
# Testar API
curl https://seudominio.com/api/health

# Testar Frontend
curl https://seudominio.com
```

### **Logs:**

```bash
# Logs do Laravel
tail -f public_html/api/storage/logs/laravel.log

# Logs do servidor
tail -f /var/log/apache2/error.log
```

---

## 🚨 **TROUBLESHOOTING**

### **Problemas Comuns:**

1. **Erro 500 no Laravel:**

   ```bash
   chmod -R 755 public_html/api/storage
   chmod -R 755 public_html/api/bootstrap/cache
   ```

2. **API não responde:**

   - Verificar `.htaccess` na pasta `/api`
   - Verificar configuração do banco de dados
   - Verificar logs do Laravel

3. **Frontend não carrega:**
   - Verificar se o build foi gerado
   - Verificar se `VITE_API_URL` está correto
   - Verificar console do navegador

---

## 📞 **SUPORTE**

### **Documentação Completa:**

- 📖 **Guia Detalhado**: `docs/DEPLOY_HOSTINGER.md`
- 🚀 **Configuração Rápida**: `scripts/quick-setup.sh`
- 🔧 **Scripts de Deploy**: `scripts/`

### **Contato:**

- 📧 Email: suporte@seudominio.com
- 📱 WhatsApp: (11) 99999-9999
- 📖 Docs: https://seudominio.com/docs

---

## 🎯 **PRÓXIMOS PASSOS**

1. **Escolher método de deploy** (Recomendado: Git Hooks)
2. **Configurar servidor** com script de setup
3. **Configurar ambiente** (.env files)
4. **Testar deploy** em ambiente de desenvolvimento
5. **Fazer deploy de produção**
6. **Configurar monitoramento**

---

**🎉 Com essas propostas, você terá um sistema de deploy automatizado, seguro e confiável para suas aplicações Laravel e React na Hostinger!**
