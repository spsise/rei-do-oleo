#!/bin/bash

# 🎉 Welcome Script para Ambiente Simplificado
# Este script exibe informações de boas-vindas para o ambiente simplificado

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

clear

echo -e "${PURPLE}"
echo "╔══════════════════════════════════════════════════════════════════════════════╗"
echo "║                                                                              ║"
echo "║                    🛠️  REI DO ÓLEO - AMBIENTE SIMPLIFICADO                  ║"
echo "║                                                                              ║"
echo "║                        Ambiente de Desenvolvimento Leve                     ║"
echo "║                                                                              ║"
echo "╚══════════════════════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${CYAN}🚀 Bem-vindo ao ambiente de desenvolvimento simplificado!${NC}"
echo ""

echo -e "${BLUE}📋 SERVIÇOS DISPONÍVEIS:${NC}"
echo -e "  ✅ ${GREEN}Laravel Backend${NC}     - http://localhost:8000"
echo -e "  ✅ ${GREEN}React Frontend${NC}      - http://localhost:3000"
echo -e "  ✅ ${GREEN}MySQL Database${NC}      - localhost:3310"
echo -e "  ✅ ${GREEN}phpMyAdmin${NC}          - http://localhost:8110"
echo ""

echo -e "${YELLOW}⚠️  SERVIÇOS DESABILITADOS (AMBIENTE SIMPLIFICADO):${NC}"
echo -e "  ❌ ${RED}Redis Cache${NC}          - Usando File Cache"
echo -e "  ❌ ${RED}MailHog${NC}              - Usando Log Driver"
echo -e "  ❌ ${RED}Queue Worker${NC}         - Usando Sync"
echo -e "  ❌ ${RED}Laravel Scheduler${NC}    - Desabilitado"
echo -e "  ❌ ${RED}MinIO Storage${NC}        - Usando Local Storage"
echo -e "  ❌ ${RED}Redis Commander${NC}      - Desabilitado"
echo ""

echo -e "${BLUE}🛠️  COMANDOS ÚTEIS:${NC}"
echo ""
echo -e "${CYAN}Backend (Laravel):${NC}"
echo "  cd /workspace/backend"
echo "  php artisan serve                    # Iniciar servidor Laravel"
echo "  php artisan migrate                  # Executar migrações"
echo "  php artisan test                     # Executar testes"
echo "  php artisan tinker                   # Console interativo"
echo ""

echo -e "${CYAN}Frontend (React):${NC}"
echo "  cd /workspace/frontend"
echo "  npm run dev                          # Iniciar servidor de desenvolvimento"
echo "  npm run build                        # Build de produção"
echo "  npm run type-check                   # Verificar tipos TypeScript"
echo "  npm run lint                         # Verificar linting"
echo ""

echo -e "${CYAN}Banco de Dados:${NC}"
echo "  mysql -h localhost -P 3310 -u rei_do_oleo -p rei_do_oleo_dev"
echo "  # Acesse phpMyAdmin: http://localhost:8110"
echo ""

echo -e "${BLUE}📁 ESTRUTURA DO PROJETO:${NC}"
echo "  /workspace/backend/                  # Laravel API"
echo "  /workspace/frontend/                 # React Frontend"
echo "  /workspace/.devcontainer/            # Configurações do DevContainer"
echo ""

echo -e "${GREEN}✨ CONFIGURAÇÕES SIMPLIFICADAS:${NC}"
echo "  • Cache: File Driver (sem Redis)"
echo "  • Sessões: File Driver (sem Redis)"
echo "  • Queue: Sync (sem Redis)"
echo "  • Email: Log Driver (sem MailHog)"
echo "  • Storage: Local (sem MinIO)"
echo ""

echo -e "${PURPLE}🎯 PRÓXIMOS PASSOS:${NC}"
echo "  1. Execute: cd /workspace/backend && php artisan serve"
echo "  2. Execute: cd /workspace/frontend && npm run dev"
echo "  3. Acesse: http://localhost:8000 (Backend)"
echo "  4. Acesse: http://localhost:3000 (Frontend)"
echo ""

echo -e "${YELLOW}💡 DICA:${NC} Para usar o ambiente completo com Redis, MailHog, etc.,"
echo "   use o devcontainer.json padrão em vez do devcontainer.simple.json"
echo ""

echo -e "${BLUE}🔧 TROUBLESHOOTING:${NC}"
echo "  • Problemas de permissão: sudo chown -R www-data:www-data storage"
echo "  • Cache limpo: php artisan cache:clear"
echo "  • Dependências: composer install && npm install"
echo ""

echo -e "${GREEN}🚀 Ambiente pronto para desenvolvimento!${NC}"
echo ""

