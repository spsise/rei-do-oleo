# 🚀 Processo de Build do DevContainer - Sistema Rei do Óleo

## 📋 Visão Geral

Este documento detalha o processo completo de inicialização e configuração do ambiente de desenvolvimento do Sistema Rei do Óleo através do DevContainer. O processo é automatizado e garante que todas as dependências, serviços e configurações estejam prontas para desenvolvimento.

## 🔄 Fluxo Completo do Processo

### 1. **Inicialização do DevContainer**

#### Arquivo: `.devcontainer/devcontainer.json`
- **Função**: Configuração principal do DevContainer
- **Ação**: Define o ambiente de desenvolvimento
- **Comandos executados**:
  - `postCreateCommand`: Executa o script de setup inicial
  - `postStartCommand`: Executa o script de inicialização dos serviços
  - `postAttachCommand`: Executa o script de boas-vindas

#### Arquivo: `.devcontainer/docker-compose.yml`
- **Função**: Orquestra todos os serviços necessários
- **Serviços iniciados**:
  - `devcontainer`: Container principal de desenvolvimento
  - `mysql`: Banco de dados MySQL 8.0
  - `redis`: Cache Redis 7.x
  - `phpmyadmin`: Interface web para MySQL
  - `redis-commander`: Interface web para Redis
  - `mailhog`: Captura de emails para desenvolvimento

### 2. **Script de Setup Inicial**

#### Arquivo: `.devcontainer/scripts/setup.sh`
- **Execução**: Automática após criação do container
- **Duração**: ~5-10 minutos (dependendo da conexão)

#### Etapas do Setup:

##### **2.1 Aguardar Serviços (60 segundos máximo)**
```bash
# Verifica se MySQL e Redis estão prontos
mysqladmin ping -h mysql -u root -proot123
redis-cli -h redis ping
```

##### **2.2 Configurar Backend Laravel**
- **Verificação**: Se diretório `backend` existe
- **Se não existir**:
  - Cria novo projeto Laravel 11.0
  - Instala dependências essenciais:
    - `laravel/sanctum` (autenticação)
    - `laravel/horizon` (filas)
    - `spatie/laravel-permission` (permissões)
    - `spatie/laravel-query-builder` (queries)
    - `spatie/laravel-backup` (backups)
    - `barryvdh/laravel-cors` (CORS)
    - `league/flysystem-aws-s3-v3` (S3/MinIO)
  - Instala dependências de desenvolvimento:
    - `laravel/telescope` (debugging)
    - `barryvdh/laravel-debugbar` (debug)
    - `phpunit/phpunit` (testes)
    - `friendsofphp/php-cs-fixer` (formatação)
    - `phpstan/phpstan` (análise estática)
- **Se existir**:
  - Executa `composer install` para atualizar dependências

##### **2.3 Configurar Frontend React**
- **Verificação**: Se diretório `frontend` existe
- **Se não existir**:
  - Cria projeto React com Vite e TypeScript
  - Instala dependências essenciais:
    - `@tanstack/react-query` (gerenciamento de estado)
    - `react-router-dom` (roteamento)
    - `axios` (requisições HTTP)
    - `@headlessui/react` (componentes UI)
    - `@heroicons/react` (ícones)
    - `tailwindcss` (CSS framework)
    - `react-hook-form` (formulários)
    - `@vite-pwa/vite-plugin` (PWA)
  - Instala dependências de desenvolvimento:
    - `@vitejs/plugin-react-swc` (compilador)
    - `@typescript-eslint/eslint-plugin` (linting)
    - `@testing-library/react` (testes)
    - `vitest` (testes unitários)
- **Se existir**:
  - Verifica e instala dependências faltantes
  - Garante que `@vitejs/plugin-react-swc` está instalado

##### **2.4 Configurar Variáveis de Ambiente**
- **Backend (.env)**:
  - Copia `.env.example` para `.env`
  - Configura conexão MySQL:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=rei_do_oleo_dev
    DB_USERNAME=rei_do_oleo
    DB_PASSWORD=secret123
    ```
  - Configura Redis:
    ```env
    REDIS_HOST=redis
    REDIS_PASSWORD=null
    REDIS_PORT=6379
    ```
  - Configura MailHog:
    ```env
    MAIL_MAILER=smtp
    MAIL_HOST=mailhog
    MAIL_PORT=1025
    ```
  - Configura MinIO (S3):
    ```env
    FILESYSTEM_DISK=s3
    AWS_ACCESS_KEY_ID=reidooleo
    AWS_SECRET_ACCESS_KEY=secret123456
    AWS_BUCKET=rei-do-oleo-storage
    AWS_ENDPOINT=http://minio:9000
    ```

- **Frontend (.env)**:
  ```env
  VITE_APP_NAME="Rei do Óleo"
  VITE_API_URL=http://localhost:8000/api
  VITE_APP_URL=http://localhost:3000
  VITE_APP_ENV=development
  ```

##### **2.5 Configurar Laravel**
- Gera chave da aplicação: `php artisan key:generate`
- Aguarda banco estar disponível (30 segundos máximo)
- Executa migrações: `php artisan migrate --force`
- Publica configurações dos pacotes:
  - Sanctum: `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
  - Permissions: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
- Cria link simbólico para storage: `php artisan storage:link`

##### **2.6 Configurar Ferramentas de Desenvolvimento**
- **PHP CS Fixer** (`.php-cs-fixer.php`):
  - Configura regras de formatação PSR-12
  - Define diretórios para análise
- **PHPStan** (`phpstan.neon`):
  - Configura análise estática nível 8
  - Define paths e exclusões
- **ESLint** (`frontend/.eslintrc.js`):
  - Configura linting para React/TypeScript
  - Define regras de qualidade de código
- **Prettier** (`.prettierrc`):
  - Configura formatação automática
  - Define padrões de código

##### **2.7 Configurar Package.json na Raiz**
- Cria scripts de desenvolvimento:
  ```json
  {
    "dev": "concurrently \"cd backend && php artisan serve\" \"cd frontend && npm run dev\"",
    "build": "cd frontend && npm run build",
    "test": "concurrently \"cd backend && php artisan test\" \"cd frontend && npm test\"",
    "lint": "concurrently \"cd backend && ./vendor/bin/php-cs-fixer fix --dry-run\" \"cd frontend && npm run lint\"",
    "lint:fix": "concurrently \"cd backend && ./vendor/bin/php-cs-fixer fix\" \"cd frontend && npm run lint:fix\""
  }
  ```

##### **2.8 Configurar Git Hooks (Husky)**
- Instala Husky para Git hooks
- Configura lint-staged para validação automática
- Define hooks para PHP e JavaScript/TypeScript

##### **2.9 Configurar Banco de Teste**
- Executa script `setup-test-db.sh`
- Configura banco de dados para testes
- Cria usuários e permissões de teste

##### **2.10 Configurar MinIO Storage**
- Configura bucket `rei-do-oleo-storage`
- Define políticas de acesso público
- Configura credenciais de acesso

##### **2.11 Verificação Final**
- Verifica dependências críticas do frontend
- Corrige permissões de diretórios
- Configura usuário Git global

### 3. **Script de Inicialização dos Serviços**

#### Arquivo: `.devcontainer/scripts/start.sh`
- **Execução**: Automática após cada start do container
- **Duração**: ~1-2 minutos

#### Etapas do Start:

##### **3.1 Verificar Serviços**
- Aguarda MySQL estar disponível (30 segundos)
- Aguarda Redis estar disponível (10 segundos)

##### **3.2 Verificar Dependências**
- **Backend**: Verifica se `vendor/` existe, instala se necessário
- **Frontend**: Verifica se `node_modules/` existe, instala se necessário

##### **3.3 Verificar Migrações**
- Executa `php artisan migrate:status`
- Executa migrações se houver pendências

##### **3.4 Limpar Caches Laravel**
- `php artisan cache:clear`
- `php artisan config:clear`
- `php artisan route:clear`
- `php artisan view:clear`

### 4. **Script de Boas-vindas**

#### Arquivo: `.devcontainer/scripts/welcome.sh`
- **Execução**: Automática ao conectar no container
- **Função**: Exibe informações úteis e comandos disponíveis

## 🌐 URLs e Portas Disponíveis

Após o setup completo, os seguintes serviços estarão disponíveis:

| Serviço | URL | Porta | Descrição |
|---------|-----|-------|-----------|
| **Laravel API** | http://localhost:8000 | 8000 | API principal do sistema |
| **React Frontend** | http://localhost:3000 | 3000 | Interface do usuário |
| **Vite Dev Server** | http://localhost:5173 | 5173 | Servidor de desenvolvimento Vite |
| **phpMyAdmin** | http://localhost:8110 | 8110 | Interface web MySQL |
| **Redis Commander** | http://localhost:6410 | 6410 | Interface web Redis |
| **MailHog** | http://localhost:8030 | 8030 | Captura de emails |
| **MySQL** | localhost:3310 | 3310 | Banco de dados (acesso direto) |
| **Redis** | localhost:6400 | 6400 | Cache (acesso direto) |

## 🚀 Comandos para Iniciar Desenvolvimento

### Comando Principal
```bash
npm run dev
```
Este comando inicia simultaneamente:
- Laravel API na porta 8000
- React Frontend na porta 3000

### Outros Comandos Úteis
```bash
# Build de produção do frontend
npm run build

# Executar testes
npm run test

# Análise de código
npm run lint

# Corrigir formatação
npm run lint:fix

# Reset completo do ambiente
npm run reset

# Backup do banco de dados
npm run backup
```

## 🔧 Troubleshooting

### Problemas Comuns

#### 1. **Serviços não iniciam**
```bash
# Verificar status dos containers
docker-compose ps

# Reiniciar serviços
docker-compose restart

# Ver logs
docker-compose logs [serviço]
```

#### 2. **Dependências não instalam**
```bash
# Limpar cache e reinstalar
rm -rf node_modules package-lock.json
npm install

# Para backend
rm -rf vendor composer.lock
composer install
```

#### 3. **Banco de dados não conecta**
```bash
# Verificar se MySQL está rodando
docker-compose ps mysql

# Verificar logs do MySQL
docker-compose logs mysql

# Testar conexão
mysql -h mysql -u rei_do_oleo -psecret123 rei_do_oleo_dev
```

#### 4. **Portas já em uso**
```bash
# Verificar processos nas portas
lsof -i :8000
lsof -i :3000

# Matar processos se necessário
kill -9 [PID]
```

## 📊 Monitoramento e Logs

### Logs dos Serviços
```bash
# Laravel logs
tail -f backend/storage/logs/laravel.log

# Nginx logs (se configurado)
docker-compose logs nginx

# MySQL logs
docker-compose logs mysql

# Redis logs
docker-compose logs redis
```

### Ferramentas de Debug
- **Laravel Telescope**: http://localhost:8000/telescope
- **Laravel Debugbar**: Disponível no frontend Laravel
- **phpMyAdmin**: http://localhost:8110
- **Redis Commander**: http://localhost:6410

## 🔄 Fluxo de Desenvolvimento

### 1. **Primeira Vez**
1. Abrir projeto no VS Code
2. Aguardar DevContainer buildar (~10-15 minutos)
3. Executar `npm run dev`
4. Acessar http://localhost:3000

### 2. **Desenvolvimento Diário**
1. Abrir projeto no VS Code
2. Aguardar serviços iniciarem (~1-2 minutos)
3. Executar `npm run dev`
4. Desenvolver normalmente

### 3. **Reset do Ambiente**
```bash
npm run reset
```
Este comando:
- Para todos os containers
- Remove volumes de dados
- Rebuilda containers
- Executa setup completo novamente

## 📝 Notas Importantes

### Performance
- **Primeira build**: 10-15 minutos (dependendo da conexão)
- **Builds subsequentes**: 1-2 minutos (cache Docker)
- **Inicialização diária**: 30-60 segundos

### Recursos Consumidos
- **RAM**: ~2-3GB (MySQL + Redis + Containers)
- **CPU**: ~2-4 cores durante build
- **Disco**: ~5-10GB (dependências + dados)

### Persistência de Dados
- **MySQL**: Dados persistem entre restarts
- **Redis**: Dados persistem entre restarts
- **Código**: Sincronizado com host
- **Dependências**: Cache persistente

## 🎯 Conclusão

O processo de build do DevContainer é completamente automatizado e garante que o ambiente de desenvolvimento esteja sempre consistente e pronto para uso. O sistema é resiliente a falhas e inclui verificações de saúde para todos os serviços essenciais.

Após o setup inicial, o desenvolvedor pode focar exclusivamente no desenvolvimento, sem se preocupar com configurações de ambiente ou dependências. 