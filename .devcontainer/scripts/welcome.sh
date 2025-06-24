#!/bin/bash

# 👋 Welcome Script - Sistema Rei do Óleo
# Exibe informações úteis quando o container é anexado

# Cores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m'

# Banner de boas-vindas
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║              👋 BEM-VINDO AO REI DO ÓLEO!                 ║
║            🛠️ Ambiente de Desenvolvimento                 ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

echo -e "${CYAN}🌐 SERVIÇOS DISPONÍVEIS:${NC}"
echo -e "  ${GREEN}🚀 Laravel API:${NC}      http://localhost:8000"
echo -e "  ${GREEN}⚛️ React Frontend:${NC}   http://localhost:3000"
echo -e "  ${GREEN}💾 phpMyAdmin:${NC}       http://localhost:8080"
echo -e "  ${GREEN}🔍 Redis Commander:${NC}  http://localhost:6380"
echo -e "  ${GREEN}📧 MailHog:${NC}          http://localhost:8025"
echo -e "  ${GREEN}📦 MinIO Console:${NC}    http://localhost:9001"
echo

echo -e "${CYAN}🛠️ COMANDOS ÚTEIS:${NC}"
echo -e "  ${YELLOW}npm run dev${NC}           # Iniciar desenvolvimento (Laravel + React)"
echo -e "  ${YELLOW}npm run dev:backend${NC}   # Apenas Laravel API"
echo -e "  ${YELLOW}npm run dev:frontend${NC}  # Apenas React Frontend"
echo -e "  ${YELLOW}npm run test${NC}          # Executar todos os testes"
echo -e "  ${YELLOW}npm run lint${NC}          # Verificar qualidade do código"
echo -e "  ${YELLOW}npm run fix${NC}           # Corrigir formatação automática"
echo

echo -e "${CYAN}🎯 ALIASES LARAVEL:${NC}"
echo -e "  ${YELLOW}art${NC}                   # php artisan"
echo -e "  ${YELLOW}tinker${NC}               # php artisan tinker"
echo -e "  ${YELLOW}migrate${NC}              # php artisan migrate"
echo -e "  ${YELLOW}serve${NC}                # php artisan serve"
echo -e "  ${YELLOW}queue${NC}                # php artisan queue:work"
echo

echo -e "${CYAN}🗄️ ACESSO AO BANCO:${NC}"
echo -e "  ${YELLOW}mysql-cli${NC}            # Conectar ao MySQL via CLI"
echo -e "  ${YELLOW}redis-cli${NC}            # Conectar ao Redis via CLI"
echo

echo -e "${CYAN}📁 ESTRUTURA DO PROJETO:${NC}"
echo -e "  ${PURPLE}backend/${NC}             # API Laravel 11"
echo -e "  ${PURPLE}frontend/${NC}            # React + TypeScript + Vite"
echo -e "  ${PURPLE}.devcontainer/${NC}       # Configuração do Dev Container"
echo -e "  ${PURPLE}scripts/${NC}             # Scripts de automação"
echo

echo -e "${GREEN}🚀 Para começar, execute: ${YELLOW}npm run dev${NC}"
echo 