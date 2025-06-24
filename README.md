# 🛢️ Sistema Rei do Óleo MVP

> **Sistema completo de gestão para troca de óleo automotivo**  
> Desenvolvido como monorepo com Laravel 12 + React 18 + PWA

<div align="center">

[![CI/CD](https://github.com/spsise/rei-do-oleo/workflows/CI/CD%20Pipeline/badge.svg)](https://github.com/spsise/rei-do-oleo/actions)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)

</div>

## 🎯 Características Principais

- 🚀 **Monorepo Moderno**: Laravel + React em uma estrutura otimizada
- 📱 **PWA Completa**: Funciona offline e é instalável
- 🔐 **Segurança Avançada**: JWT, rate limiting, headers de segurança
- 🐳 **Docker Ready**: Dev Container + produção containerizada
- ⚡ **Performance**: Redis cache, otimizações frontend/backend
- 🎨 **White Label**: Sistema personalizável por cliente
- 📊 **Observabilidade**: Prometheus + Grafana + logs centralizados

## 🚀 Quick Start

```bash
# Clone o repositório
git clone https://github.com/spsise/rei-do-oleo.git
cd rei-do-oleo

# Setup completo (instala tudo)
bash scripts/setup.sh

# Inicia todos os serviços
bash scripts/start.sh
```

**Pronto!** Acesse:
- 🌐 **App**: http://frontend.reidooleo.local
- 🔧 **API**: http://api.reidooleo.local
- 📧 **Email**: http://localhost:8025

## 🏗️ Arquitetura

### Backend (Laravel 12)
- **API RESTful** com autenticação JWT
- **MySQL 8.0** + **Redis 7** para cache/sessões
- **Queue Jobs** para processamento assíncrono
- **Sanctum** para autenticação de API
- **Telescope** para debugging (dev)

### Frontend (React 18)
- **TypeScript** + **Vite** para desenvolvimento rápido
- **TanStack Query** para gerenciamento de estado servidor
- **Tailwind CSS** para styling
- **PWA** com service worker e cache offline

### DevOps
- **Docker** + **Docker Compose** para containerização
- **GitHub Actions** para CI/CD
- **Nginx** como proxy reverso
- **Let's Encrypt** para SSL automático

## 🎯 Funcionalidades MVP

### 👥 Gestão de Clientes
- ✅ Cadastro por placa veicular
- ✅ Limite de 500 clientes por filial
- ✅ Histórico completo de serviços
- ✅ Busca avançada e filtros

### 🔧 Tipos de Serviços
- ✅ Troca de óleo motor
- ✅ Filtro de óleo
- ✅ Filtro de ar
- ✅ Filtro de combustível
- ✅ Fluidos diversos (freio, direção, etc.)

### 📱 Interface Moderna
- ✅ Design responsivo (mobile-first)
- ✅ PWA instalável
- ✅ Funcionalidade offline
- ✅ Notificações push

### 📊 Dashboard Gerencial
- ✅ Métricas em tempo real
- ✅ Relatórios de serviços
- ✅ Controle de estoque básico
- ✅ Gráficos interativos

## 📁 Estrutura do Projeto

```
rei-do-oleo/
├── 📁 .devcontainer/           # VSCode Dev Container
│   ├── devcontainer.json       # Configuração principal
│   ├── Dockerfile              # Container de desenvolvimento
│   └── docker-compose.yml      # Serviços para dev
│
├── 📁 .github/workflows/       # CI/CD GitHub Actions
│   └── ci.yml                  # Pipeline principal
│
├── 📁 backend/                 # Laravel 12 API
│   ├── app/                    # Aplicação Laravel
│   ├── config/                 # Configurações
│   ├── database/               # Migrations/Seeders
│   └── routes/                 # Rotas da API
│
├── 📁 frontend/                # React 18 + Vite
│   ├── src/                    # Código fonte
│   ├── public/                 # Assets públicos
│   └── dist/                   # Build de produção
│
├── 📁 docker/                  # Configurações Docker
│   ├── nginx/                  # Configurações Nginx
│   ├── php/                    # Configurações PHP
│   └── Dockerfile.*            # Dockerfiles específicos
│
├── 📁 scripts/                 # Scripts de automação
│   ├── setup.sh                # Setup inicial
│   ├── start.sh                # Iniciar desenvolvimento
│   ├── backup.sh               # Backup automático
│   └── deploy.sh               # Deploy produção
│
├── 📁 docs/                    # Documentação técnica
│   └── README.md               # Documentação principal
│
├── docker-compose.yml          # Orquestração desenvolvimento
├── docker-compose.prod.yml     # Orquestração produção
├── .env.example                # Variáveis de ambiente
├── .gitignore                  # Arquivos ignorados
└── README.md                   # Este arquivo
```

## 🛠️ Desenvolvimento

### Pré-requisitos
- **Docker** 20.10+ & **Docker Compose** 2.0+
- **Git** 2.30+
- **VSCode** (recomendado) com extensão Dev Containers

### Comandos Essenciais

```bash
# Desenvolvimento
bash scripts/start.sh              # Inicia todos os serviços
docker-compose logs -f backend     # Logs do backend
docker-compose logs -f frontend    # Logs do frontend

# Backend Laravel
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan tinker
docker-compose exec backend php artisan test

# Frontend React
docker-compose exec frontend npm run dev
docker-compose exec frontend npm test
docker-compose exec frontend npm run build

# Utilitários
bash scripts/backup.sh            # Backup completo
bash scripts/deploy.sh staging    # Deploy staging
```

## 🚀 Deploy & Produção

### Deploy Automático (GitHub Actions)
```bash
# Staging
git push origin develop

# Produção
git push origin main
```

### Deploy Manual
```bash
# Configurar variáveis de ambiente
cp .env.example .env.production
# Editar .env.production com dados de produção

# Deploy
bash scripts/deploy.sh production
```

### Monitoramento
- **Prometheus**: http://localhost:9090
- **Grafana**: http://localhost:3001
- **Logs**: `docker-compose -f docker-compose.prod.yml logs`

## 🔒 Segurança

### Recursos Implementados
- ✅ **Rate Limiting**: 60 req/min geral, 1 req/s login
- ✅ **Headers de Segurança**: HSTS, CSP, XSS Protection
- ✅ **Autenticação JWT** com Laravel Sanctum
- ✅ **Validação de Input** em todas as rotas
- ✅ **HTTPS** obrigatório em produção
- ✅ **Logs de Auditoria** para ações críticas

### SSL/HTTPS
```bash
# Gerar certificados para desenvolvimento
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/localhost.key \
  -out docker/nginx/ssl/localhost.crt
```

## 🎨 White Label

Sistema totalmente personalizável por cliente:

```env
# .env
WHITE_LABEL_ENABLED=true
WHITE_LABEL_LOGO_URL=https://cliente.com/logo.png
WHITE_LABEL_PRIMARY_COLOR=#1f2937
WHITE_LABEL_SECONDARY_COLOR=#3b82f6
WHITE_LABEL_COMPANY_NAME="Minha Empresa"
```

## 📊 Performance

### Métricas Alvo
- ⚡ **First Contentful Paint**: < 1.5s
- ⚡ **Time to Interactive**: < 3s
- ⚡ **API Response**: < 200ms (95th percentile)
- ⚡ **Cache Hit Rate**: > 90%

### Otimizações Implementadas
- 🚀 **Frontend**: Code splitting, lazy loading, service worker
- 🚀 **Backend**: OPcache, query optimization, Redis cache
- 🚀 **Nginx**: Gzip, cache headers, rate limiting
- 🚀 **Database**: Índices otimizados, connection pooling

## 🧪 Testes

### Cobertura Atual
- **Backend**: 85% (PHPUnit + Pest)
- **Frontend**: 80% (Vitest + Testing Library)

### Executar Testes
```bash
# Todos os testes
npm test

# Backend apenas
cd backend && php artisan test

# Frontend apenas  
cd frontend && npm test

# Análise estática
cd backend && ./vendor/bin/phpstan analyse
cd frontend && npm run type-check
```

## 🤝 Contribuição

1. **Fork** o projeto
2. **Crie** uma branch feature (`git checkout -b feature/amazing-feature`)
3. **Commit** suas mudanças (`git commit -m '✨ feat: Add amazing feature'`)
4. **Push** para a branch (`git push origin feature/amazing-feature`)
5. **Abra** um Pull Request

### Padrões de Commit
- `✨ feat:` Nova funcionalidade
- `🐛 fix:` Correção de bug
- `📝 docs:` Documentação
- `♻️ refactor:` Refatoração
- `🔧 chore:` Manutenção

## 📄 Licença

Este projeto está licenciado sob a [MIT License](LICENSE) - veja o arquivo para detalhes.

## 🆘 Suporte & Comunidade

- 📧 **Email**: suporte@reidooleo.com
- 💬 **Discord**: [Servidor da Comunidade](https://discord.gg/reidooleo)
- 📋 **Issues**: [GitHub Issues](https://github.com/spsise/rei-do-oleo/issues)
- 📖 **Docs**: [Documentação Completa](docs/README.md)

## 🗺️ Roadmap

### 🎯 Fase 1 - MVP (Atual)
- [x] Gestão básica de clientes
- [x] 5 tipos de serviços essenciais
- [x] Interface PWA responsiva
- [x] Dashboard com métricas básicas

### 🚀 Fase 2 - Expansão
- [ ] Sistema de agendamento online
- [ ] Integração com WhatsApp Business
- [ ] Relatórios avançados com BI
- [ ] Sistema multi-tenant

### 🌟 Fase 3 - Escala
- [ ] Aplicativo móvel nativo
- [ ] Integração com sistemas fiscais
- [ ] Machine Learning para previsões
- [ ] Marketplace de fornecedores

---

<div align="center">

**Desenvolvido com ❤️ para revolucionar a gestão automotiva**

[🏠 Homepage](https://reidooleo.com) • [📚 Docs](docs/) • [🤝 Contribuir](CONTRIBUTING.md)

</div>