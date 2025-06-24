# 📚 Documentação - Sistema Rei do Óleo MVP

## 🚀 Visão Geral

O **Sistema Rei do Óleo MVP** é uma aplicação completa para gestão de troca de óleo automotivo, desenvolvida como um monorepo com tecnologias modernas.

### 🏗️ Arquitetura

- **Backend**: Laravel 12 + PostgreSQL + Redis
- **Frontend**: React 18 + TypeScript + Vite + PWA
- **Containerização**: Docker + Docker Compose
- **CI/CD**: GitHub Actions
- **Monitoramento**: Prometheus + Grafana

## 🎯 Funcionalidades MVP

### 👥 Gestão de Clientes
- Cadastro por placa veicular (máximo 500/filial)
- Histórico de serviços
- Notificações automáticas

### 🔧 Tipos de Serviços
- Troca de óleo motor
- Filtro de óleo
- Filtro de ar
- Filtro de combustível
- Fluidos diversos

### 📱 Interface
- PWA responsiva
- Funcionalidade offline
- Interface mobile-first

### 📊 Dashboard
- Métricas em tempo real
- Relatórios de serviços
- Controle de estoque

### 🔐 Autenticação
- JWT com Laravel Sanctum
- Controle de permissões
- Sessões seguras

## 🛠️ Início Rápido

### Pré-requisitos
- Docker & Docker Compose
- Git
- Node.js 18+ (opcional)
- PHP 8.2+ (opcional)

### Instalação

```bash
# 1. Clonar repositório
git clone <repo-url>
cd rei-do-oleo

# 2. Executar setup
bash scripts/setup.sh

# 3. Iniciar desenvolvimento
bash scripts/start.sh
```

### URLs de Acesso

- 🌐 **Frontend**: http://frontend.reidooleo.local
- 🔧 **API**: http://api.reidooleo.local
- 📧 **MailHog**: http://localhost:8025
- 🗄️ **Adminer**: http://localhost:8081
- 📊 **Redis UI**: http://localhost:8082
- 📦 **MinIO Console**: http://localhost:9001

## 📁 Estrutura do Projeto

```
rei-do-oleo/
├── .devcontainer/          # VSCode Dev Container
├── .github/workflows/      # CI/CD GitHub Actions
├── backend/               # Laravel 12 API
├── frontend/              # React 18 + Vite
├── docker/               # Configurações Docker
├── scripts/              # Scripts de automação
├── docs/                 # Documentação
├── docker-compose.yml    # Orquestração desenvolvimento
├── docker-compose.prod.yml # Orquestração produção
└── README.md
```

## 🔧 Comandos Úteis

### Desenvolvimento
```bash
# Iniciar serviços
bash scripts/start.sh

# Ver logs
docker-compose logs -f

# Entrar no container
docker-compose exec backend bash

# Artisan commands
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan tinker

# Frontend development
docker-compose exec frontend npm run dev
```

### Produção
```bash
# Deploy
bash scripts/deploy.sh production

# Backup
bash scripts/backup.sh

# Monitoring
docker-compose -f docker-compose.prod.yml logs -f
```

## 🧪 Testes

### Backend (Laravel)
```bash
cd backend
php artisan test
./vendor/bin/phpstan analyse
```

### Frontend (React)
```bash
cd frontend
npm test
npm run lint
npm run type-check
```

## 🔒 Segurança

### Configurações Implementadas
- Rate limiting na API
- Headers de segurança
- Validação de entrada
- Proteção CSRF
- Autenticação JWT
- Criptografia de senhas

### SSL/HTTPS
```bash
# Gerar certificados SSL
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/private.key \
  -out docker/nginx/ssl/certificate.crt
```

## 🚀 Deploy

### Staging
```bash
git push origin develop
# Deploy automático via GitHub Actions
```

### Produção
```bash
git push origin main
# Deploy automático via GitHub Actions
```

### Manual
```bash
bash scripts/deploy.sh production
```

## 📊 Monitoramento

### Métricas Disponíveis
- Tempo de resposta API
- Taxa de erro
- Uso de recursos
- Conexões banco de dados
- Cache hit rate

### Alertas
- CPU > 80%
- Memória > 85%
- Disco > 90%
- Tempo resposta > 2s
- Taxa erro > 5%

## 🔧 Configuração

### Variáveis de Ambiente
Veja `.env.example` para todas as configurações disponíveis.

### White Label
```env
WHITE_LABEL_ENABLED=true
WHITE_LABEL_LOGO_URL=https://example.com/logo.png
WHITE_LABEL_PRIMARY_COLOR=#1f2937
WHITE_LABEL_SECONDARY_COLOR=#3b82f6
```

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch feature (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas mudanças (`git commit -m '✨ feat: Nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está licenciado sob a [MIT License](LICENSE).

## 🆘 Suporte

- 📧 Email: suporte@reidooleo.com
- 💬 Discord: [Servidor Discord](https://discord.gg/reidooleo)
- 📋 Issues: [GitHub Issues](https://github.com/org/rei-do-oleo/issues)

## 🗺️ Roadmap

### Fase 1 (MVP) ✅
- [x] Gestão básica de clientes
- [x] 5 tipos de serviços
- [x] Interface PWA
- [x] Dashboard básico

### Fase 2
- [ ] Agendamento online
- [ ] Integração WhatsApp
- [ ] Relatórios avançados
- [ ] Multi-tenancy

### Fase 3
- [ ] App móvel nativo
- [ ] Integração fiscal
- [ ] BI avançado
- [ ] Marketplace 