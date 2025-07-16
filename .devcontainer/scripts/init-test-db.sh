#!/bin/bash

# 🚀 Inicialização Rápida do Banco de Teste
# Script para inicializar rapidamente o banco de dados de teste

set -e

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log() { echo -e "${GREEN}[INIT-TEST-DB]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }

cd /workspace

# Verificar se o MySQL está disponível
if ! mysqladmin ping -h mysql -u root -proot123 --silent 2>/dev/null; then
    warn "MySQL não está disponível. Aguardando..."
    for i in {1..15}; do
        if mysqladmin ping -h mysql -u root -proot123 --silent 2>/dev/null; then
            log "MySQL está disponível!"
            break
        fi
        if [ $i -eq 15 ]; then
            warn "Timeout aguardando MySQL"
            exit 1
        fi
        echo -n "."
        sleep 2
    done
    echo
fi

# Criar banco de teste se não existir
if ! mysql -h mysql -u root -proot123 -e "USE rei_do_oleo_test;" 2>/dev/null; then
    log "Criando banco de dados rei_do_oleo_test..."
    mysql -h mysql -u root -proot123 -e "CREATE DATABASE IF NOT EXISTS rei_do_oleo_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
fi

# Criar usuário se não existir
if ! mysql -h mysql -u root -proot123 -e "SELECT User FROM mysql.user WHERE User='rei_do_oleo';" 2>/dev/null | grep -q "rei_do_oleo"; then
    log "Criando usuário rei_do_oleo..."
    mysql -h mysql -u root -proot123 -e "CREATE USER IF NOT EXISTS 'rei_do_oleo'@'%' IDENTIFIED BY 'secret123';"
    mysql -h mysql -u root -proot123 -e "GRANT ALL PRIVILEGES ON rei_do_oleo_test.* TO 'rei_do_oleo'@'%';"
    mysql -h mysql -u root -proot123 -e "FLUSH PRIVILEGES;"
fi

# Executar migrações
log "Executando migrações..."
cd backend
php artisan migrate --env=testing --force --quiet

# Limpar caches
log "Limpando caches..."
php artisan config:clear --env=testing --quiet
php artisan cache:clear --env=testing --quiet

log "✅ Banco de dados de teste inicializado com sucesso!"
