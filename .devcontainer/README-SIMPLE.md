# 🛠️ Ambiente de Desenvolvimento Simplificado

Este documento explica como usar o ambiente de desenvolvimento simplificado do projeto Rei do Óleo, que remove dependências não essenciais para tornar o ambiente mais leve e rápido de inicializar.

## 🎯 Objetivo

O ambiente simplificado foi criado para:
- **Reduzir o tempo de inicialização** do devcontainer
- **Diminuir o uso de recursos** (CPU, RAM, disco)
- **Simplificar a configuração** para novos desenvolvedores
- **Manter apenas os serviços essenciais** para desenvolvimento

## 📋 Serviços Incluídos

### ✅ Serviços Essenciais
- **devcontainer**: Container principal de desenvolvimento
- **mysql**: Banco de dados MySQL 8.0
- **phpmyadmin**: Interface web para MySQL (opcional)

### ❌ Serviços Removidos
- **redis**: Cache e sessões (usando File Driver)
- **mailhog**: Captura de emails (usando Log Driver)
- **queue**: Worker de filas (usando Sync)
- **scheduler**: Agendador de tarefas (desabilitado)
- **minio**: Armazenamento de objetos (usando Local Storage)
- **redis-commander**: Interface web para Redis (desabilitado)

## 🚀 Como Usar

### Opção 1: Usar DevContainer Simplificado

1. **Renomear arquivos de configuração:**
   ```bash
   # Fazer backup da configuração atual
   mv .devcontainer/devcontainer.json .devcontainer/devcontainer.full.json
   mv .devcontainer/docker-compose.yml .devcontainer/docker-compose.full.yml
   
   # Usar configuração simplificada
   mv .devcontainer/devcontainer.simple.json .devcontainer/devcontainer.json
   mv .devcontainer/docker-compose.simple.yml .devcontainer/docker-compose.yml
   ```

2. **Abrir no VS Code:**
   ```bash
   code .
   # Ou usar Command Palette: "Dev Containers: Reopen in Container"
   ```

### Opção 2: Usar Docker Compose Diretamente

1. **Iniciar apenas os serviços essenciais:**
   ```bash
   cd .devcontainer
   docker-compose -f docker-compose.simple.yml up -d
   ```

2. **Acessar o container:**
   ```bash
   docker-compose -f docker-compose.simple.yml exec devcontainer bash
   ```

## ⚙️ Configurações Simplificadas

### Laravel (Backend)
```env
# Cache e Sessões
CACHE_DRIVER=file
SESSION_DRIVER=file

# Queue
QUEUE_CONNECTION=sync

# Email
MAIL_MAILER=log

# Database
DB_HOST=mysql
DB_DATABASE=rei_do_oleo_dev
DB_USERNAME=rei_do_oleo
DB_PASSWORD=secret123
```

### React (Frontend)
```env
VITE_API_URL=http://localhost:8000/api
VITE_APP_URL=http://localhost:3000
VITE_APP_ENV=development
```

## 🛠️ Comandos de Desenvolvimento

### Backend (Laravel)
```bash
cd /workspace/backend

# Iniciar servidor
php artisan serve

# Executar migrações
php artisan migrate

# Executar testes
php artisan test

# Console interativo
php artisan tinker

# Limpar cache
php artisan cache:clear
php artisan config:clear
```

### Frontend (React)
```bash
cd /workspace/frontend

# Iniciar servidor de desenvolvimento
npm run dev

# Build de produção
npm run build

# Verificar tipos
npm run type-check

# Verificar linting
npm run lint
```

## 🌐 URLs de Acesso

- **Backend Laravel**: http://localhost:8000
- **Frontend React**: http://localhost:3000
- **phpMyAdmin**: http://localhost:8110
- **MySQL**: localhost:3310

## 📊 Comparação de Recursos

| Recurso | Ambiente Completo | Ambiente Simplificado |
|---------|------------------|----------------------|
| **Containers** | 8 | 3 |
| **Volumes** | 6 | 4 |
| **Portas** | 12 | 4 |
| **RAM Estimada** | ~2GB | ~800MB |
| **Tempo de Inicialização** | ~3-5 min | ~1-2 min |
| **Disco** | ~1.5GB | ~800MB |

## ⚠️ Limitações do Ambiente Simplificado

### Funcionalidades Desabilitadas
- **Cache Redis**: Usando File Cache (mais lento)
- **Sessões Redis**: Usando File Sessions (não escalável)
- **Queue Workers**: Processamento síncrono (pode ser lento)
- **Email Testing**: Emails salvos em logs (não visualizáveis)
- **Scheduled Tasks**: Cron jobs desabilitados
- **Object Storage**: Usando storage local (não escalável)

### Quando Usar o Ambiente Completo
- Desenvolvimento de funcionalidades que dependem de Redis
- Testes de email e notificações
- Desenvolvimento de funcionalidades de fila
- Testes de performance com cache
- Desenvolvimento de funcionalidades de agendamento

## 🔄 Alternando Entre Ambientes

### Para Ambiente Completo
```bash
# Restaurar configuração completa
mv .devcontainer/devcontainer.json .devcontainer/devcontainer.simple.json
mv .devcontainer/devcontainer.full.json .devcontainer/devcontainer.json

mv .devcontainer/docker-compose.yml .devcontainer/docker-compose.simple.yml
mv .devcontainer/docker-compose.full.yml .devcontainer/docker-compose.yml
```

### Para Ambiente Simplificado
```bash
# Usar configuração simplificada
mv .devcontainer/devcontainer.json .devcontainer/devcontainer.full.json
mv .devcontainer/devcontainer.simple.json .devcontainer/devcontainer.json

mv .devcontainer/docker-compose.yml .devcontainer/docker-compose.full.yml
mv .devcontainer/docker-compose.simple.yml .devcontainer/docker-compose.yml
```

## 🐛 Troubleshooting

### Problemas Comuns

1. **Erro de conexão com banco:**
   ```bash
   # Verificar se MySQL está rodando
   docker-compose -f docker-compose.simple.yml ps mysql
   
   # Verificar logs
   docker-compose -f docker-compose.simple.yml logs mysql
   ```

2. **Erro de permissões no Laravel:**
   ```bash
   cd /workspace/backend
   sudo chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

3. **Dependências não instaladas:**
   ```bash
   # Backend
   cd /workspace/backend
   composer install
   
   # Frontend
   cd /workspace/frontend
   npm install
   ```

4. **Cache corrompido:**
   ```bash
   cd /workspace/backend
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

## 📝 Scripts Disponíveis

- `setup-simple.sh`: Configura o ambiente simplificado
- `start-simple.sh`: Inicia os serviços essenciais
- `welcome-simple.sh`: Exibe informações de boas-vindas

## 🎯 Recomendações

### Para Novos Desenvolvedores
- Comece com o ambiente simplificado
- Migre para o ambiente completo quando necessário

### Para Desenvolvimento Rápido
- Use o ambiente simplificado para prototipagem
- Use o ambiente completo para testes finais

### Para Produção
- Sempre teste no ambiente completo antes do deploy
- O ambiente simplificado não é adequado para produção

---

**💡 Dica**: O ambiente simplificado é ideal para desenvolvimento inicial, prototipagem e quando você precisa de um ambiente rápido para trabalhar. Use o ambiente completo quando precisar testar funcionalidades que dependem dos serviços removidos.

