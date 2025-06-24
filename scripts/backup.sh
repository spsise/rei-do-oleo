#!/bin/bash

# 💾 Script de Backup - Sistema Rei do Óleo MVP
# Este script realiza backup completo do sistema

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para logging
log() {
    echo -e "${GREEN}[BACKUP]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

# Configurações
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="backups"
DB_NAME="reidooleo_dev"
DB_USER="reidooleo"
DB_PASSWORD="reidooleo123"
RETENTION_DAYS=30

# Banner
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    💾 REI DO ÓLEO MVP                     ║
║                   Sistema de Backup                       ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Verificar se estamos no diretório correto
if [ ! -f "docker-compose.yml" ]; then
    error "Execute este script na raiz do projeto!"
    exit 1
fi

# Criar diretório de backup
mkdir -p $BACKUP_DIR/{database,files,configs}

log "🚀 Iniciando backup do Sistema Rei do Óleo..."

# 1. Backup do Banco de Dados
log "🗄️ Realizando backup do banco de dados..."
if docker-compose ps postgres | grep -q "Up"; then
    BACKUP_FILE="$BACKUP_DIR/database/db_$TIMESTAMP.sql"
    
    docker-compose exec -T postgres pg_dump \
        -U $DB_USER \
        -h localhost \
        -d $DB_NAME \
        --verbose \
        --clean \
        --no-owner \
        --no-privileges > $BACKUP_FILE
    
    # Comprimir backup
    gzip $BACKUP_FILE
    log "✅ Banco: db_$TIMESTAMP.sql.gz"
    
    # Informações do backup
    DB_SIZE=$(docker-compose exec -T postgres psql -U $DB_USER -d $DB_NAME -t -c "SELECT pg_size_pretty(pg_database_size('$DB_NAME'));")
    info "📊 Tamanho do banco: $(echo $DB_SIZE | xargs)"
else
    error "Container PostgreSQL não está rodando!"
    exit 1
fi

# 2. Backup dos Arquivos de Upload/Storage
log "📁 Realizando backup dos arquivos..."
if [ -d "backend/storage" ]; then
    STORAGE_BACKUP="$BACKUP_DIR/files/storage_$TIMESTAMP.tar.gz"
    tar -czf $STORAGE_BACKUP -C backend storage/
    log "✅ Storage: storage_$TIMESTAMP.tar.gz"
else
    warn "Diretório storage não encontrado"
fi

# Backup do MinIO (se estiver rodando)
if docker-compose ps minio | grep -q "Up"; then
    log "📦 Realizando backup do MinIO..."
    MINIO_BACKUP="$BACKUP_DIR/files/minio_backup_$TIMESTAMP.tar.gz"
    
    # Backup usando mc (MinIO Client)
    docker-compose exec -T devcontainer bash -c "
        # Configurar mc se não estiver configurado
        mc alias set minio http://minio:9000 reidooleo reidooleo123 &> /dev/null || true
        
        # Fazer backup do bucket
        mc mirror minio/reidooleo-storage /tmp/minio_backup/
        tar -czf /tmp/minio_backup.tar.gz -C /tmp minio_backup/
    " && docker-compose cp devcontainer:/tmp/minio_backup.tar.gz $MINIO_BACKUP
    
    log "✅ Backup do MinIO salvo em: $MINIO_BACKUP"
else
    warn "MinIO não está rodando"
fi

# 3. Backup das Configurações
log "⚙️ Realizando backup das configurações..."
CONFIG_BACKUP="$BACKUP_DIR/configs/configs_$TIMESTAMP.tar.gz"

# Arquivos de configuração importantes
tar -czf $CONFIG_BACKUP \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.git' \
    --exclude='dist' \
    --exclude='build' \
    .env* \
    docker-compose*.yml \
    .devcontainer/ \
    scripts/ \
    .github/ \
    backend/.env* \
    backend/config/ \
    frontend/.env* \
    2>/dev/null || true

log "✅ Configs: configs_$TIMESTAMP.tar.gz"

# 4. Backup do Código Fonte (Git)
log "📝 Realizando backup do código fonte..."
if [ -d ".git" ]; then
    GIT_BACKUP="$BACKUP_DIR/files/git_backup_$TIMESTAMP.tar.gz"
    tar -czf $GIT_BACKUP .git/
    log "✅ Backup do Git salvo em: $GIT_BACKUP"
    
    # Informações do Git
    CURRENT_BRANCH=$(git branch --show-current 2>/dev/null || echo "unknown")
    LAST_COMMIT=$(git log -1 --format="%h - %s" 2>/dev/null || echo "unknown")
    info "📋 Branch atual: $CURRENT_BRANCH"
    info "📋 Último commit: $LAST_COMMIT"
else
    warn "Repositório Git não encontrado"
fi

# 5. Criar arquivo de informações do backup
log "📄 Criando arquivo de informações..."
INFO_FILE="$BACKUP_DIR/backup_info_$TIMESTAMP.txt"

cat > $INFO_FILE << EOF
=======================================================
         BACKUP SYSTEM - REI DO ÓLEO MVP
=======================================================

Data/Hora: $(date)
Timestamp: $TIMESTAMP
Hostname: $(hostname)
User: $(whoami)
Path: $(pwd)

=======================================================
                 INFORMAÇÕES DO SISTEMA
=======================================================

Docker Version: $(docker --version)
Docker Compose Version: $(docker-compose --version)

=======================================================
                INFORMAÇÕES DO BACKUP
=======================================================

Banco de Dados:
- Nome: $DB_NAME
- Tamanho: $(echo $DB_SIZE | xargs)
- Arquivo: db_$TIMESTAMP.sql.gz

Arquivos:
- Storage Laravel: storage_$TIMESTAMP.tar.gz
- MinIO: minio_backup_$TIMESTAMP.tar.gz
- Configurações: configs_$TIMESTAMP.tar.gz
- Git: git_backup_$TIMESTAMP.tar.gz

=======================================================
                INFORMAÇÕES DO GIT
=======================================================

Branch: $CURRENT_BRANCH
Último Commit: $LAST_COMMIT

=======================================================
                ARQUIVOS DO BACKUP
=======================================================

$(find $BACKUP_DIR -name "*$TIMESTAMP*" -type f -exec basename {} \; | sort)

=======================================================
EOF

log "✅ Arquivo de informações salvo em: $INFO_FILE"

# 6. Limpeza de backups antigos
log "🧹 Limpando backups antigos (>$RETENTION_DAYS dias)..."
find $BACKUP_DIR -name "*.gz" -mtime +$RETENTION_DAYS -delete 2>/dev/null || true
find $BACKUP_DIR -name "*.sql" -mtime +$RETENTION_DAYS -delete 2>/dev/null || true
find $BACKUP_DIR -name "*.txt" -mtime +$RETENTION_DAYS -delete 2>/dev/null || true

DELETED_COUNT=$(find $BACKUP_DIR -name "*$(date -d "$RETENTION_DAYS days ago" +%Y%m%d)*" 2>/dev/null | wc -l)
if [ $DELETED_COUNT -gt 0 ]; then
    log "🗑️ Removidos $DELETED_COUNT backups antigos"
fi

# 7. Estatísticas do backup
log "📊 Calculando estatísticas..."
TOTAL_SIZE=$(du -sh $BACKUP_DIR | cut -f1)
BACKUP_COUNT=$(find $BACKUP_DIR -name "*.gz" -o -name "*.sql" | wc -l)

# 8. Upload para cloud (se configurado)
if [ ! -z "$AWS_ACCESS_KEY_ID" ] && [ ! -z "$AWS_SECRET_ACCESS_KEY" ] && [ ! -z "$AWS_BUCKET" ]; then
    log "☁️ Enviando backup para AWS S3..."
    
    # Instalar AWS CLI se não estiver instalado
    if ! command -v aws &> /dev/null; then
        warn "AWS CLI não encontrado. Instale para habilitar upload automático."
    else
        aws s3 sync $BACKUP_DIR s3://$AWS_BUCKET/backups/rei-do-oleo/
        log "✅ Backup enviado para S3"
    fi
else
    info "ℹ️ Configurações AWS não encontradas. Backup local apenas."
fi

# 9. Finalização
log "✅ Backup concluído com sucesso!"
echo -e "${GREEN}"
cat << EOF
╔═══════════════════════════════════════════════════════════╗
║                   💾 BACKUP CONCLUÍDO!                   ║
╠═══════════════════════════════════════════════════════════╣
║  📊 Estatísticas:                                        ║
║     • Tamanho total: $TOTAL_SIZE                               ║
║     • Total de arquivos: $BACKUP_COUNT                           ║
║     • Localização: $BACKUP_DIR/                          ║
║                                                           ║
║  📁 Arquivos criados:                                    ║
║     • db_$TIMESTAMP.sql.gz        ║
║     • storage_$TIMESTAMP.tar.gz          ║
║     • configs_$TIMESTAMP.tar.gz          ║
║     • backup_info_$TIMESTAMP.txt                ║
║                                                           ║
║  🔄 Retenção: $RETENTION_DAYS dias                               ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

info "Para restaurar um backup, use: bash scripts/restore.sh <timestamp>"
info "Exemplo: bash scripts/restore.sh $TIMESTAMP" 