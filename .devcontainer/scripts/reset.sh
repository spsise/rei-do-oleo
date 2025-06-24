#!/bin/bash

# 🔄 Reset Script - Sistema Rei do Óleo
# Reset completo do ambiente (banco de dados + cache)

set -e

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log() { echo -e "${GREEN}[RESET]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }

cd /workspace

# Banner
echo -e "${RED}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                🔄 RESET DO AMBIENTE                       ║
║              ⚠️  OPERAÇÃO DESTRUTIVA ⚠️                   ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

warn "Este script irá:"
warn "- Resetar completamente o banco de dados"
warn "- Limpar todos os caches (Laravel + Redis)"
warn "- Recriar buckets MinIO"
warn "- Reinstalar dependências"

read -p "Tem certeza que deseja continuar? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    info "Operação cancelada."
    exit 0
fi

# 1. Reset do banco de dados
log "🗄️ Resetando banco de dados..."
if [ -d "backend" ]; then
    cd backend
    
    # Drop e recria o banco
    mysql -h mysql -u root -proot123 -e "DROP DATABASE IF EXISTS rei_do_oleo_dev;" 2>/dev/null || true
    mysql -h mysql -u root -proot123 -e "CREATE DATABASE rei_do_oleo_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true
    mysql -h mysql -u root -proot123 -e "GRANT ALL PRIVILEGES ON rei_do_oleo_dev.* TO 'rei_do_oleo'@'%';" 2>/dev/null || true
    mysql -h mysql -u root -proot123 -e "FLUSH PRIVILEGES;" 2>/dev/null || true
    
    # Execute migrações frescas
    log "🔄 Executando migrações frescas..."
    php artisan migrate:fresh --seed
    
    cd /workspace
fi

# 2. Limpar todos os caches
log "🧹 Limpando todos os caches..."

# Cache Laravel
if [ -d "backend" ]; then
    cd backend
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan queue:clear
    
    # Limpar storage/logs
    find storage/logs -name "*.log" -type f -delete 2>/dev/null || true
    
    cd /workspace
fi

# Cache Redis
log "📦 Limpando cache Redis..."
redis-cli -h redis FLUSHALL >/dev/null 2>&1 || true

# 3. Reset MinIO
log "📦 Resetando MinIO storage..."
if command -v mc >/dev/null 2>&1; then
    mc alias set minio http://minio:9000 reidooleo secret123456 >/dev/null 2>&1 || true
    mc rm --recursive --force minio/rei-do-oleo-storage >/dev/null 2>&1 || true
    mc mb minio/rei-do-oleo-storage >/dev/null 2>&1 || true
    mc policy set public minio/rei-do-oleo-storage >/dev/null 2>&1 || true
fi

# 4. Reinstalar dependências
log "📦 Reinstalando dependências..."

# Backend
if [ -d "backend" ]; then
    cd backend
    rm -rf vendor
    composer install
    cd /workspace
fi

# Frontend
if [ -d "frontend" ]; then
    cd frontend
    rm -rf node_modules
    npm install
    cd /workspace
fi

# 5. Recriar storage links
if [ -d "backend" ]; then
    cd backend
    php artisan storage:link
    cd /workspace
fi

log "✅ Reset completo realizado com sucesso!"
info "🎯 Ambiente resetado e pronto para uso"
info "🚀 Execute 'npm run dev' para iniciar o desenvolvimento" 