#!/bin/bash

# Script de Deploy - Sistema Rei do Óleo MVP
set -e

# Configurações
ENVIRONMENT=${1:-staging}
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

log() { echo -e "${GREEN}[DEPLOY]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }

echo "🚀 Deploy do Sistema Rei do Óleo - Ambiente: $ENVIRONMENT"

# Verificações pré-deploy
log "Verificando pré-requisitos..."
if [ ! -f "docker-compose.prod.yml" ]; then
    error "Arquivo docker-compose.prod.yml não encontrado!"
    exit 1
fi

# Backup antes do deploy
log "Criando backup pré-deploy..."
bash scripts/backup.sh

# 1. Build das imagens
log "Construindo imagens Docker..."
docker-compose -f docker-compose.prod.yml build --no-cache

# 2. Parar serviços atuais
log "Parando serviços atuais..."
docker-compose -f docker-compose.prod.yml down

# 3. Executar migrações
log "Executando migrações..."
docker-compose -f docker-compose.prod.yml up -d postgres redis
sleep 10
docker-compose -f docker-compose.prod.yml run --rm backend php artisan migrate --force

# 4. Otimizações Laravel
log "Otimizando Laravel para produção..."
docker-compose -f docker-compose.prod.yml run --rm backend php artisan config:cache
docker-compose -f docker-compose.prod.yml run --rm backend php artisan route:cache
docker-compose -f docker-compose.prod.yml run --rm backend php artisan view:cache

# 5. Build do Frontend
log "Construindo Frontend..."
docker-compose -f docker-compose.prod.yml run --rm frontend npm run build

# 6. Iniciar todos os serviços
log "Iniciando serviços..."
docker-compose -f docker-compose.prod.yml up -d

# 7. Health Check
log "Verificando saúde dos serviços..."
sleep 30
if curl -f http://localhost/health > /dev/null 2>&1; then
    log "✅ Deploy concluído com sucesso!"
else
    error "❌ Falha no health check!"
    exit 1
fi

echo "🎉 Deploy finalizado - Ambiente: $ENVIRONMENT" 