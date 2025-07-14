#!/bin/bash

# 🧪 Setup Database de Teste - Sistema Rei do Óleo
# Script para configurar automaticamente o banco de dados de teste

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Funções de logging
log() { echo -e "${GREEN}[TEST-DB]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${PURPLE}[SUCCESS]${NC} $1"; }
step() { echo -e "${CYAN}[STEP]${NC} $1"; }

# Função para executar comandos no backend
backend_exec() {
    (cd /workspace/backend && "$@")
}

# Banner
echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                🧪 TEST DATABASE SETUP                     ║
║           Configuração do Banco de Teste                  ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

cd /workspace

# 1. Verificar se o MySQL está disponível
step "🔍 Verificando disponibilidade do MySQL..."
if ! mysqladmin ping -h mysql -u root -proot123 --silent 2>/dev/null; then
    error "❌ MySQL não está disponível. Aguardando..."

    # Aguardar MySQL estar pronto
    for i in {1..30}; do
        if mysqladmin ping -h mysql -u root -proot123 --silent 2>/dev/null; then
            success "✅ MySQL está disponível!"
            break
        fi
        if [ $i -eq 30 ]; then
            error "❌ Timeout aguardando MySQL"
            exit 1
        fi
        echo -n "."
        sleep 2
    done
    echo
else
    success "✅ MySQL está disponível!"
fi

# 2. Criar banco de dados de teste se não existir
step "🗄️ Criando banco de dados de teste..."
if ! mysql -h mysql -u root -proot123 -e "USE rei_do_oleo_test;" 2>/dev/null; then
    log "Criando banco de dados rei_do_oleo_test..."
    mysql -h mysql -u root -proot123 -e "CREATE DATABASE IF NOT EXISTS rei_do_oleo_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    success "✅ Banco de dados de teste criado"
else
    info "ℹ️ Banco de dados de teste já existe"
fi

# 3. Criar usuário de teste se não existir
step "👤 Configurando usuário de teste..."
if ! mysql -h mysql -u root -proot123 -e "SELECT User FROM mysql.user WHERE User='rei_do_oleo';" 2>/dev/null | grep -q "rei_do_oleo"; then
    log "Criando usuário rei_do_oleo..."
    mysql -h mysql -u root -proot123 -e "CREATE USER IF NOT EXISTS 'rei_do_oleo'@'%' IDENTIFIED BY 'secret123';"
    mysql -h mysql -u root -proot123 -e "GRANT ALL PRIVILEGES ON rei_do_oleo_test.* TO 'rei_do_oleo'@'%';"
    mysql -h mysql -u root -proot123 -e "FLUSH PRIVILEGES;"
    success "✅ Usuário de teste criado e configurado"
else
    info "ℹ️ Usuário de teste já existe"
fi

# 4. Verificar se o arquivo .env.testing existe
step "📄 Configurando arquivo .env.testing..."
if [ ! -f "backend/.env.testing" ]; then
    log "Criando arquivo .env.testing..."
    cp backend/.env backend/.env.testing

    # Configurar variáveis específicas para teste
    sed -i 's/APP_ENV=local/APP_ENV=testing/' backend/.env.testing
    sed -i 's/DB_DATABASE=rei_do_oleo_dev/DB_DATABASE=rei_do_oleo_test/' backend/.env.testing
    sed -i 's/DB_USERNAME=rei_do_oleo_user/DB_USERNAME=rei_do_oleo/' backend/.env.testing
    sed -i 's/DB_PASSWORD=secret123rei_do_oleo_password/DB_PASSWORD=secret123/' backend/.env.testing
    sed -i 's/SESSION_DRIVER=redis/SESSION_DRIVER=array/' backend/.env.testing
    sed -i 's/QUEUE_CONNECTION=redis/QUEUE_CONNECTION=sync/' backend/.env.testing
    sed -i 's/MAIL_MAILER=log/MAIL_MAILER=array/' backend/.env.testing
    sed -i 's/CACHE_STORE=redis/CACHE_STORE=array/' backend/.env.testing

    # Adicionar configurações específicas de teste
    echo "" >> backend/.env.testing
    echo "# Test Configuration" >> backend/.env.testing
    echo "PULSE_ENABLED=false" >> backend/.env.testing
    echo "TELESCOPE_ENABLED=false" >> backend/.env.testing
    echo "LOG_CHANNEL=single" >> backend/.env.testing
    echo "BCRYPT_ROUNDS=4" >> backend/.env.testing

    success "✅ Arquivo .env.testing criado e configurado"
else
    info "ℹ️ Arquivo .env.testing já existe"
fi

# 5. Executar migrações no banco de teste
step "🔄 Executando migrações no banco de teste..."
if backend_exec php artisan migrate --env=testing --force; then
    success "✅ Migrações executadas no banco de teste"
else
    warn "⚠️ Erro ao executar migrações no banco de teste"
    # Tentar novamente com mais detalhes
    backend_exec php artisan migrate --env=testing --force --verbose
fi

# 6. Verificar se as tabelas foram criadas
step "🔍 Verificando tabelas no banco de teste..."
TABLE_COUNT=$(mysql -h mysql -u rei_do_oleo -psecret123 rei_do_oleo_test -e "SHOW TABLES;" 2>/dev/null | wc -l)
if [ "$TABLE_COUNT" -gt 1 ]; then
    success "✅ Banco de teste configurado com $((TABLE_COUNT - 1)) tabelas"
else
    warn "⚠️ Poucas tabelas encontradas no banco de teste"
fi

# 7. Limpar caches do Laravel
step "🧹 Limpando caches..."
backend_exec php artisan config:clear --env=testing
backend_exec php artisan cache:clear --env=testing
backend_exec php artisan route:clear --env=testing
backend_exec php artisan view:clear --env=testing
success "✅ Caches limpos"

# 8. Testar conexão com o banco de teste
step "🧪 Testando conexão com banco de teste..."
if backend_exec php artisan tinker --env=testing --execute="echo 'Conexão com banco de teste OK'; exit;" 2>/dev/null; then
    success "✅ Conexão com banco de teste funcionando"
else
    warn "⚠️ Problema na conexão com banco de teste"
fi

# 9. Verificar configuração do PHPUnit
step "📋 Verificando configuração do PHPUnit..."
if [ -f "backend/phpunit.xml" ]; then
    # Verificar se as configurações de banco estão corretas
    if grep -q "rei_do_oleo_test" backend/phpunit.xml; then
        success "✅ PHPUnit configurado para usar banco de teste"
    else
        warn "⚠️ PHPUnit pode não estar configurado corretamente"
    fi
else
    warn "⚠️ Arquivo phpunit.xml não encontrado"
fi

# 10. Finalização
success "🎉 Configuração do banco de teste concluída!"
echo -e "${GREEN}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    🧪 TEST DB READY!                      ║
╠═══════════════════════════════════════════════════════════╣
║  🗄️ Database: rei_do_oleo_test                          ║
║  👤 User: rei_do_oleo                                    ║
║  🔑 Password: secret123                                  ║
║  📄 Config: backend/.env.testing                         ║
╠═══════════════════════════════════════════════════════════╣
║  🧪 Para executar testes:                                ║
║  cd backend && php artisan test                          ║
║                                                          ║
║  🔍 Para verificar status:                               ║
║  cd backend && php artisan migrate:status --env=testing  ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

info "🚀 Banco de dados de teste configurado e pronto para uso!"
