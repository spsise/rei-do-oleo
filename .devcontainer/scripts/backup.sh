#!/bin/bash

# 💾 Backup Script - Sistema Rei do Óleo
# Backup automático (banco de dados + arquivos)

set -e

# Cores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log() { echo -e "${GREEN}[BACKUP]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Configurações
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/workspace/backups"
DB_NAME="rei_do_oleo_dev"
DB_USER="rei_do_oleo"
DB_PASSWORD="secret123"

cd /workspace

# Banner
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                💾 BACKUP DO SISTEMA                       ║
║            Banco de Dados + Arquivos                     ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Criar diretório de backup
mkdir -p "$BACKUP_DIR"/{database,files,config}

log "🚀 Iniciando backup do sistema..."

# 1. Backup do Banco de Dados
log "🗄️ Realizando backup do banco de dados..."
if mysqladmin ping -h mysql -u root -proot123 --silent 2>/dev/null; then
    BACKUP_FILE="$BACKUP_DIR/database/db_backup_$TIMESTAMP.sql"
    
    mysqldump -h mysql -u root -proot123 \
        --single-transaction \
        --routines \
        --triggers \
        --add-drop-database \
        --databases $DB_NAME > "$BACKUP_FILE"
    
    # Comprimir backup
    gzip "$BACKUP_FILE"
    
    # Calcular tamanho
    DB_SIZE=$(mysql -h mysql -u root -proot123 -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'Size_MB' FROM information_schema.tables WHERE table_schema='$DB_NAME';" | tail -n1)
    
    log "✅ Backup do banco: db_backup_$TIMESTAMP.sql.gz"
    info "📊 Tamanho do banco: ${DB_SIZE}MB"
else
    error "❌ MySQL não está disponível!"
    exit 1
fi

# 2. Backup dos Arquivos de Storage
log "📁 Backup dos arquivos de storage..."
if [ -d "backend/storage" ]; then
    STORAGE_BACKUP="$BACKUP_DIR/files/storage_$TIMESTAMP.tar.gz"
    tar -czf "$STORAGE_BACKUP" -C backend storage/
    log "✅ Storage Laravel: storage_$TIMESTAMP.tar.gz"
else
    warn "⚠️ Diretório storage não encontrado"
fi

# 3. Backup do MinIO
log "📦 Backup do MinIO..."
if command -v mc >/dev/null 2>&1; then
    mc alias set minio http://minio:9000 reidooleo secret123456 >/dev/null 2>&1 || true
    
    MINIO_BACKUP="$BACKUP_DIR/files/minio_backup_$TIMESTAMP.tar.gz"
    TEMP_DIR="/tmp/minio_backup_$TIMESTAMP"
    
    mkdir -p "$TEMP_DIR"
    mc mirror minio/rei-do-oleo-storage "$TEMP_DIR" >/dev/null 2>&1 || true
    
    if [ "$(ls -A $TEMP_DIR)" ]; then
        tar -czf "$MINIO_BACKUP" -C "$TEMP_DIR" .
        log "✅ MinIO storage: minio_backup_$TIMESTAMP.tar.gz"
    else
        warn "⚠️ MinIO storage está vazio"
    fi
    
    rm -rf "$TEMP_DIR"
else
    warn "⚠️ MinIO client não disponível"
fi

# 4. Backup das Configurações
log "⚙️ Backup das configurações..."
CONFIG_BACKUP="$BACKUP_DIR/config/config_$TIMESTAMP.tar.gz"

tar -czf "$CONFIG_BACKUP" \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.git' \
    --exclude='dist' \
    --exclude='build' \
    --exclude='backups' \
    .env* \
    docker-compose*.yml \
    .devcontainer/ \
    package*.json \
    composer.json \
    composer.lock \
    backend/.env* \
    backend/config/ \
    frontend/.env* \
    frontend/package*.json \
    2>/dev/null || true

log "✅ Configurações: config_$TIMESTAMP.tar.gz"

# 5. Backup do Código Fonte (Git)
log "📝 Backup do repositório Git..."
if [ -d ".git" ]; then
    GIT_BACKUP="$BACKUP_DIR/files/git_backup_$TIMESTAMP.tar.gz"
    tar -czf "$GIT_BACKUP" .git/
    log "✅ Git repository: git_backup_$TIMESTAMP.tar.gz"
    
    # Informações do Git
    CURRENT_BRANCH=$(git branch --show-current 2>/dev/null || echo "unknown")
    LAST_COMMIT=$(git log -1 --format="%h - %s" 2>/dev/null || echo "unknown")
    info "📋 Branch atual: $CURRENT_BRANCH"
    info "📋 Último commit: $LAST_COMMIT"
else
    warn "⚠️ Repositório Git não encontrado"
fi

# 6. Criar arquivo de informações
log "📄 Criando arquivo de informações..."
INFO_FILE="$BACKUP_DIR/backup_info_$TIMESTAMP.txt"

cat > "$INFO_FILE" << EOF
=======================================================
       BACKUP SYSTEM - REI DO ÓLEO MVP
=======================================================

Data/Hora: $(date)
Timestamp: $TIMESTAMP
Container: $(hostname)
User: $(whoami)
Workspace: /workspace

=======================================================
            INFORMAÇÕES DO SISTEMA
=======================================================

PHP Version: $(php --version | head -n1)
Composer Version: $(composer --version)
Node Version: $(node --version)
NPM Version: $(npm --version)

=======================================================
           INFORMAÇÕES DO BACKUP
=======================================================

Banco de Dados:
- Nome: $DB_NAME
- Tamanho: ${DB_SIZE}MB
- Arquivo: db_backup_$TIMESTAMP.sql.gz

Arquivos:
- Storage Laravel: storage_$TIMESTAMP.tar.gz
- MinIO Storage: minio_backup_$TIMESTAMP.tar.gz
- Configurações: config_$TIMESTAMP.tar.gz
- Git Repository: git_backup_$TIMESTAMP.tar.gz

=======================================================
           INFORMAÇÕES DO GIT
=======================================================

Branch: $CURRENT_BRANCH
Último Commit: $LAST_COMMIT

=======================================================
EOF

# 7. Limpeza de backups antigos (manter últimos 7 dias)
log "🧹 Limpando backups antigos..."
find "$BACKUP_DIR" -name "*.gz" -mtime +7 -delete 2>/dev/null || true
find "$BACKUP_DIR" -name "*.txt" -mtime +7 -delete 2>/dev/null || true

# 8. Relatório final
BACKUP_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)
TOTAL_FILES=$(find "$BACKUP_DIR" -name "*$TIMESTAMP*" | wc -l)

log "✅ Backup completo realizado!"
echo
echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                   📊 RESUMO DO BACKUP                     ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
echo -e "  📁 Diretório: $BACKUP_DIR"
echo -e "  📦 Tamanho total: $BACKUP_SIZE"
echo -e "  📄 Arquivos criados: $TOTAL_FILES"
echo -e "  🕐 Timestamp: $TIMESTAMP"
echo
info "🎯 Backup salvo com sucesso!" 