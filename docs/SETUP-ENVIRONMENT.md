# 🔧 Setup Automático de Variáveis de Ambiente

## 📋 Visão Geral

Este documento descreve o sistema automático de configuração de variáveis de ambiente implementado no projeto **Rei do Óleo**. O sistema garante que os arquivos `.env` sejam sempre criados automaticamente quando o projeto for clonado em novos ambientes.

## 🚀 Como Funciona

### 1. **Scripts Automáticos**

O projeto possui scripts que executam automaticamente:

- **Git Hooks**: Executam após `checkout` e `merge`
- **DevContainer**: Executa na criação do container
- **Composer/NPM**: Scripts de setup específicos

### 2. **Arquivos de Template**

- `backend/.env.example` - Template para ambiente de desenvolvimento
- `backend/.env.testing.example` - Template para ambiente de testes
- `frontend/.env.example` - Template para ambiente frontend

### 3. **Script Principal**

`scripts/setup-env.sh` - Script principal que:

- Cria arquivos `.env` a partir dos templates
- Aplica configurações específicas para desenvolvimento
- Valida se os arquivos foram criados corretamente

## 🛠️ Execução Manual

### Setup Completo

```bash
# Executa setup completo de ambiente
npm run setup:env
```

### Setup Individual

```bash
# Backend apenas
npm run setup:backend

# Frontend apenas
npm run setup:frontend

# Setup rápido (ambiente + dependências)
npm run setup:quick
```

### Comandos Diretos

```bash
# Script principal
bash scripts/setup-env.sh

# Composer (backend)
cd backend && composer run setup:env

# NPM (frontend)
cd frontend && npm run setup:env
```

## 🔄 Execução Automática

### Git Hooks

Os hooks são executados automaticamente:

1. **post-checkout**: Após mudança de branch
2. **post-merge**: Após merge de branches

### DevContainer

O script é executado automaticamente na criação do container:

```json
"postCreateCommand": "bash /workspace/.devcontainer/scripts/setup.sh"
```

### Composer/NPM

Scripts configurados para execução automática:

```json
// composer.json
"setup:env": [
    "@php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
    "@php -r \"file_exists('.env.testing') || copy('.env.testing.example', '.env.testing');\"",
    "@php artisan key:generate --force --ansi"
]

// package.json (frontend)
"setup:env": "cp .env.example .env"
```

## 📁 Estrutura de Arquivos

```
rei-do-oleo/
├── backend/
│   ├── .env.example              # Template principal
│   ├── .env.testing.example      # Template para testes
│   ├── .env                      # Arquivo real (não versionado)
│   └── .env.testing              # Arquivo real (não versionado)
├── frontend/
│   ├── .env.example              # Template principal
│   └── .env                      # Arquivo real (não versionado)
├── scripts/
│   └── setup-env.sh              # Script principal
└── .git/hooks/
    ├── post-checkout             # Hook após checkout
    └── post-merge                # Hook após merge
```

## 🔧 Configurações Aplicadas

### Backend (.env)

O script aplica automaticamente:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=rei_do_oleo_dev
DB_USERNAME=rei_do_oleo
DB_PASSWORD=secret123

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (MailHog)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025

# MinIO S3
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=reidooleo
AWS_SECRET_ACCESS_KEY=secret123456
AWS_ENDPOINT=http://minio:9000
```

### Frontend (.env)

```env
VITE_APP_NAME="Rei do Óleo"
VITE_API_URL=http://localhost:8000/api
VITE_APP_URL=http://localhost:3000
VITE_APP_ENV=development
```

## 🚨 Troubleshooting

### Problema: Script não executa

```bash
# Verificar permissões
chmod +x scripts/setup-env.sh
chmod +x .git/hooks/post-checkout
chmod +x .git/hooks/post-merge
```

### Problema: Arquivos .env não são criados

```bash
# Verificar se templates existem
ls -la backend/.env.example
ls -la frontend/.env.example

# Executar manualmente
bash scripts/setup-env.sh
```

### Problema: Configurações incorretas

```bash
# Recriar arquivos
rm backend/.env frontend/.env
npm run setup:env
```

## 📝 Adicionando Novas Variáveis

### 1. Atualizar Templates

Edite os arquivos `.env.example`:

```bash
# Backend
nano backend/.env.example

# Frontend
nano frontend/.env.example
```

### 2. Atualizar Script

Edite `scripts/setup-env.sh` para aplicar novas configurações:

```bash
# Adicionar novas configurações
echo "NOVA_VARIAVEL=valor" >> backend/.env
```

### 3. Testar

```bash
# Testar setup
rm backend/.env frontend/.env
npm run setup:env
```

## 🔒 Segurança

### Arquivos Ignorados

Os seguintes arquivos são ignorados pelo Git:

```gitignore
# Environment Files
.env
.env.*
!.env.example
```

### Boas Práticas

1. **Nunca commitar** arquivos `.env` reais
2. **Sempre atualizar** os templates `.env.example`
3. **Usar valores seguros** nos templates
4. **Documentar** novas variáveis

## 📚 Comandos Úteis

```bash
# Verificar status dos arquivos .env
find . -name ".env*" -type f

# Verificar diferenças entre .env e .env.example
diff backend/.env backend/.env.example

# Backup dos arquivos .env
cp backend/.env backend/.env.backup
cp frontend/.env frontend/.env.backup

# Restaurar backup
cp backend/.env.backup backend/.env
cp frontend/.env.backup frontend/.env
```

## 🎯 Checklist de Setup

- [ ] Arquivos `.env.example` existem
- [ ] Script `setup-env.sh` é executável
- [ ] Git hooks estão configurados
- [ ] Scripts npm/composer funcionam
- [ ] DevContainer executa setup automaticamente
- [ ] Documentação está atualizada

## 📞 Suporte

Se encontrar problemas:

1. Verifique os logs do script
2. Execute manualmente: `bash scripts/setup-env.sh`
3. Verifique permissões dos arquivos
4. Consulte a documentação do projeto
5. Abra uma issue no GitHub
