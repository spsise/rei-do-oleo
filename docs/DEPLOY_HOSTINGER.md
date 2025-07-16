# 🚀 Guia de Deploy Automatizado - Hostinger

Este guia apresenta **4 propostas** para automatizar o deploy das aplicações Laravel e React na hospedagem compartilhada da Hostinger.

## 📊 **COMPARAÇÃO DAS PROPOSTAS**

| Método            | Facilidade | Segurança  | Velocidade | Controle   | Recomendação    |
| ----------------- | ---------- | ---------- | ---------- | ---------- | --------------- |
| **FTP/SFTP**      | ⭐⭐⭐     | ⭐⭐       | ⭐⭐       | ⭐⭐       | Para iniciantes |
| **Git Hooks**     | ⭐⭐⭐⭐   | ⭐⭐⭐⭐   | ⭐⭐⭐⭐   | ⭐⭐⭐⭐   | **RECOMENDADO** |
| **SSH**           | ⭐⭐⭐     | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐   | ⭐⭐⭐⭐⭐ | Para avançados  |
| **API Hostinger** | ⭐⭐       | ⭐⭐⭐⭐   | ⭐⭐⭐⭐⭐ | ⭐⭐⭐     | Para integração |

---

## 🎯 **PROPOSTA 1: DEPLOY VIA FTP/SFTP (MAIS SIMPLES)**

### **Configuração:**

1. **Adicionar Secrets no GitHub:**

   ```
   HOSTINGER_DOMAIN=seudominio.com
   HOSTINGER_USERNAME=seu_usuario
   HOSTINGER_PASSWORD=sua_senha
   HOSTINGER_PORT=21
   ```

2. **Ativar o Workflow:**
   - Vá para `.github/workflows/deploy-hostinger.yml`
   - O deploy acontece automaticamente no push para `main`

### **Vantagens:**

- ✅ Configuração simples
- ✅ Compatível com qualquer plano Hostinger
- ✅ Não requer SSH habilitado

### **Desvantagens:**

- ❌ Menos seguro (senha em texto)
- ❌ Mais lento para arquivos grandes
- ❌ Sem controle de versão no servidor

---

## 🎯 **PROPOSTA 2: DEPLOY VIA GIT HOOKS (RECOMENDADO)**

### **Configuração:**

1. **Acessar servidor via SSH:**

   ```bash
   ssh usuario@seudominio.com
   ```

2. **Executar script de configuração:**

   ```bash
   # Fazer upload do script
   scp scripts/setup-git-hooks.sh usuario@seudominio.com:~/

   # Executar no servidor
   ssh usuario@seudominio.com
   chmod +x setup-git-hooks.sh
   ./setup-git-hooks.sh
   ```

3. **Configurar repositório:**

   ```bash
   cd public_html
   git remote set-url origin https://github.com/SEU_USUARIO/rei-do-oleo.git
   ```

4. **Configurar ambiente Laravel:**
   ```bash
   cd api
   cp .env.example .env
   nano .env
   # Configurar: DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL
   ```

### **Como usar:**

```bash
# Deploy automático (push para main)
git push origin main

# Deploy manual
./deploy.sh
```

### **Vantagens:**

- ✅ Controle de versão no servidor
- ✅ Rollback fácil
- ✅ Deploy rápido
- ✅ Backup automático

---

## 🎯 **PROPOSTA 3: DEPLOY VIA SSH**

### **Configuração:**

1. **Gerar chave SSH:**

   ```bash
   ssh-keygen -t rsa -b 4096 -C "deploy@seudominio.com"
   ```

2. **Adicionar chave no servidor:**

   ```bash
   ssh-copy-id usuario@seudominio.com
   ```

3. **Adicionar Secrets no GitHub:**
   ```
   HOSTINGER_HOST=seudominio.com
   HOSTINGER_USERNAME=seu_usuario
   HOSTINGER_SSH_KEY=conteudo_da_chave_privada
   HOSTINGER_DOMAIN=seudominio.com
   ```

### **Vantagens:**

- ✅ Mais seguro (chaves SSH)
- ✅ Controle total
- ✅ Logs detalhados
- ✅ Backup automático

---

## 🎯 **PROPOSTA 4: DEPLOY VIA HOSTINGER API**

### **Configuração:**

1. **Habilitar API na Hostinger:**

   - Painel Hostinger → API
   - Gerar token de acesso

2. **Adicionar Secrets:**

   ```
   HOSTINGER_API_TOKEN=seu_token_api
   HOSTINGER_DOMAIN=seudominio.com
   HOSTINGER_USERNAME=seu_usuario
   ```

3. **Executar deploy:**
   ```bash
   node scripts/hostinger-deploy.js
   ```

---

## 🔧 **CONFIGURAÇÃO DO AMBIENTE**

### **Backend Laravel (.env):**

```env
APP_NAME="Rei do Óleo"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://seudominio.com/api

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=seudominio.com
MAIL_PORT=587
MAIL_USERNAME=seu_email
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@seudominio.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### **Frontend React (.env):**

```env
VITE_API_URL=https://seudominio.com/api
VITE_APP_NAME="Rei do Óleo"
VITE_APP_URL=https://seudominio.com
```

---

## 📁 **ESTRUTURA NO SERVIDOR**

Após o deploy, a estrutura será:

```
public_html/
├── index.html          # Frontend React
├── assets/             # Arquivos estáticos do React
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
│   ├── .htaccess       # Configuração Laravel
│   └── index.php
└── .htaccess           # Configuração geral
```

---

## 🛠️ **COMANDOS ÚTEIS**

### **Deploy Manual:**

```bash
# Via Git Hooks
git push origin main

# Via SSH
./deploy.sh

# Via API
node scripts/hostinger-deploy.js
```

### **Rollback:**

```bash
# Git Hooks
git reset --hard HEAD~1
git push origin main --force

# SSH
cd public_html
git log --oneline -5
git reset --hard COMMIT_ID
```

### **Logs e Debug:**

```bash
# Ver logs do Laravel
tail -f public_html/api/storage/logs/laravel.log

# Ver logs do servidor
tail -f /var/log/apache2/error.log

# Testar API
curl https://seudominio.com/api/health
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

### **Headers de Segurança (.htaccess):**

```apache
# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

---

## 📊 **MONITORAMENTO**

### **Health Check Endpoint:**

```php
// routes/api.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});
```

### **Logs de Deploy:**

- GitHub Actions: `.github/workflows/`
- Servidor: `/home/usuario/deploy-logs/`
- Laravel: `public_html/api/storage/logs/`

---

## 🚨 **TROUBLESHOOTING**

### **Problemas Comuns:**

1. **Erro 500 no Laravel:**

   ```bash
   chmod -R 755 public_html/api/storage
   chmod -R 755 public_html/api/bootstrap/cache
   ```

2. **Erro de permissão:**

   ```bash
   chmod -R 644 public_html/*.html
   chmod -R 644 public_html/*.css
   chmod -R 644 public_html/*.js
   ```

3. **API não responde:**

   - Verificar `.htaccess` na pasta `/api`
   - Verificar configuração do banco de dados
   - Verificar logs do Laravel

4. **Frontend não carrega:**
   - Verificar se o build foi gerado corretamente
   - Verificar se `VITE_API_URL` está correto
   - Verificar console do navegador

---

## 🎯 **RECOMENDAÇÃO FINAL**

**Para seu projeto, recomendo a PROPOSTA 2 (Git Hooks)** porque:

1. ✅ **Simples de configurar**
2. ✅ **Controle de versão no servidor**
3. ✅ **Rollback fácil**
4. ✅ **Deploy rápido**
5. ✅ **Compatível com hospedagem compartilhada**

### **Próximos Passos:**

1. Escolher uma proposta
2. Configurar os secrets no GitHub
3. Testar em ambiente de desenvolvimento
4. Fazer deploy de produção
5. Configurar monitoramento

---

## 📞 **SUPORTE**

Se precisar de ajuda:

- 📧 Email: suporte@seudominio.com
- 📱 WhatsApp: (11) 99999-9999
- 📖 Documentação: https://seudominio.com/docs
