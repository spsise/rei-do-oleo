# 🚀 Dev Container - Rei do Óleo

## 📋 Visão Geral

Este é o ambiente de desenvolvimento containerizado para o projeto **Rei do Óleo**, que inclui:

- **🐘 PHP 8.2** com Laravel
- **⚛️ Node.js 20.x** com React/TypeScript
- **🗄️ MySQL 8.0** para banco de dados
- **📦 Redis 7.x** para cache e sessões
- **💾 phpMyAdmin** para gerenciamento do MySQL
- **🔍 Redis Commander** para gerenciamento do Redis
- **📧 MailHog** para captura de emails em desenvolvimento

## 🚀 Início Rápido

### 1. Pré-requisitos

- Docker instalado e rodando
- VS Code com extensão "Dev Containers"
- Git configurado

### 2. Abrir o Dev Container

```bash
# Opção 1: Via VS Code
1. Abra o projeto no VS Code
2. Pressione Ctrl+Shift+P
3. Digite "Dev Containers: Reopen in Container"
4. Selecione a configuração do Dev Container

# Opção 2: Via linha de comando
bash .devcontainer/scripts/init-devcontainer.sh
```

### 3. Verificar Status

```bash
# Verificar containers rodando
dc ps

# Verificar logs
dc logs

# Verificar logs de um serviço específico
dc logs devcontainer
```

## 🔧 Comandos Úteis

### Aliases Configurados

```bash
# Docker Compose Geral
dc          # docker-compose
dcb         # docker-compose build
dcu         # docker-compose up
dcd         # docker-compose down
dcr         # docker-compose restart
dcl         # docker-compose logs
dce         # docker-compose exec

# DevContainer Específicos
dcd         # docker-compose -f .devcontainer/docker-compose.yml
dcdc        # docker-compose -f .devcontainer/docker-compose.yml exec devcontainer
```

### Comandos Comuns

```bash
# Build da imagem
dcd build

# Iniciar todos os serviços
dcd up -d

# Parar todos os serviços
dcd down

# Rebuild sem cache
dcd build --no-cache

# Executar comando no container
dcdc bash

# Ver logs em tempo real
dcd logs -f devcontainer

# Status dos containers
dcd ps

# Logs de todos os serviços
dcd logs
```

## 🌐 Portas e Serviços

| Porta | Serviço | Descrição | URL |
|-------|---------|-----------|-----|
| 8000 | Laravel API | Backend Laravel | http://localhost:8000 |
| 3000 | React Frontend | Frontend React | http://localhost:3000 |
| 5200 | Vite Dev Server | Servidor de desenvolvimento | http://localhost:5200 |
| 3310 | MySQL | Banco de dados | localhost:3310 |
| 6400 | Redis | Cache e sessões | localhost:6400 |
| 8110 | phpMyAdmin | Interface MySQL | http://localhost:8110 |
| 6410 | Redis Commander | Interface Redis | http://localhost:6410 |
| 1030 | MailHog SMTP | Captura de emails | localhost:1030 |
| 8030 | MailHog Web | Interface de emails | http://localhost:8030 |

## 🛠️ Desenvolvimento

### Backend (Laravel)

```bash
# Acessar o container
dcdc bash

# Instalar dependências
composer install

# Executar migrações
php artisan migrate

# Executar seeders
php artisan db:seed

# Executar testes
php artisan test

# Iniciar servidor
php artisan serve --host=0.0.0.0 --port=8000
```

### Frontend (React)

```bash
# Acessar o container
dcdc bash

# Instalar dependências
npm install

# Executar em modo desenvolvimento
npm run dev

# Executar testes
npm test

# Build de produção
npm run build
```

## 🔍 Troubleshooting

### Problemas Comuns

#### 1. Erro "docker-compose not found"

```bash
# Instalar docker-compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

#### 2. Erro "buildx not found"

```bash
# Esta versão do Docker não suporta buildx
# O script irá continuar com o builder legado
```

#### 3. Portas em uso

```bash
# Verificar portas em uso
lsof -i :8000
lsof -i :3000

# Parar serviços conflitantes
sudo systemctl stop apache2
sudo systemctl stop nginx
```

#### 4. Permissões de Docker

```bash
# Adicionar usuário ao grupo docker
sudo usermod -aG docker $USER

# Aplicar mudanças
newgrp docker
```

### Scripts de Diagnóstico

```bash
# Diagnóstico completo do ambiente Docker
bash .devcontainer/scripts/diagnose-docker.sh

# Configuração das variáveis de ambiente
bash .devcontainer/scripts/setup-docker-env.sh

# Inicialização do devcontainer
bash .devcontainer/scripts/init-devcontainer.sh

# Correção de problemas
bash .devcontainer/scripts/fix-devcontainer.sh
```

## 📁 Estrutura de Arquivos

```
.devcontainer/
├── devcontainer.json          # Configuração principal do VS Code
├── docker-compose.yml         # Orquestração dos serviços
├── Dockerfile                 # Imagem base do container
├── scripts/                   # Scripts de automação
│   ├── init-devcontainer.sh  # Inicialização do ambiente
│   ├── diagnose-docker.sh    # Diagnóstico do Docker
│   ├── setup-docker-env.sh   # Configuração de variáveis
│   ├── fix-devcontainer.sh   # Correção de problemas
│   └── setup.sh              # Setup interno do container
├── mysql/                     # Configuração do MySQL
├── redis/                     # Configuração do Redis
└── nginx/                     # Configuração do Nginx
```

## 🔒 Segurança

### Variáveis de Ambiente

```bash
# Configuradas automaticamente
export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1
```

### Usuário do Container

- **Usuário**: `vscode`
- **UID**: `1000`
- **Shell**: `zsh` com Oh My Zsh
- **Permissões**: Acesso completo ao workspace

## 📚 Recursos Adicionais

### Extensões do VS Code

- **PHP**: Intelephense, PHP Debug, Laravel Blade
- **JavaScript/React**: Prettier, ESLint, React Snippets
- **Docker**: Docker extension
- **Git**: GitLens
- **Database**: MySQL Client

### Ferramentas Disponíveis

- **Composer**: Gerenciador de dependências PHP
- **Laravel Installer**: Instalador do Laravel
- **Node.js**: Runtime JavaScript
- **npm/yarn**: Gerenciadores de pacotes
- **Git**: Controle de versão
- **Oh My Zsh**: Framework para Zsh

## 🆘 Suporte

### Logs e Debugging

```bash
# Logs de todos os serviços
dcd logs

# Logs de um serviço específico
dcd logs devcontainer
dcd logs mysql
dcd logs redis

# Logs em tempo real
dcd logs -f devcontainer
```

### Recursos de Ajuda

- **Documentação**: `.devcontainer/README.md`
- **Troubleshooting**: `.devcontainer/TROUBLESHOOTING.md`
- **Build Guide**: `.devcontainer/BUILD-GUIDE.md`
- **Setup WSL2**: `.devcontainer/SETUP-WSL2.md`
- **Este Guia**: `.devcontainer/README-DEVCONTAINER.md`

### Comandos de Emergência

```bash
# Parar tudo e limpar
dcd down --volumes --remove-orphans
docker system prune -f

# Rebuild completo
dcd down
dcd build --no-cache
dcd up -d

# Reset completo
bash .devcontainer/scripts/init-devcontainer.sh
```

---

**🎯 Dica**: Use os aliases configurados para facilitar o trabalho:

- **Geral**: `dc`, `dcb`, `dcu`, `dcd`, `dcr`, `dcl`, `dce`
- **DevContainer**: `dcd` (para comandos docker-compose) e `dcdc` (para executar comandos no container)
