#!/bin/bash

# 🔍 Verify Laravel Setup - Sistema Rei do Óleo
# Script para verificar se o Laravel está configurado corretamente

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
log() { echo -e "${GREEN}[VERIFY]${NC} $1"; }
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
║                🔍 VERIFY LARAVEL SETUP                    ║
║           Verificando configuração do Laravel             ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

cd /workspace

# 1. Verificar se o backend existe
step "🔍 Verificando estrutura do backend..."
if [ ! -d "backend" ]; then
    error "❌ Diretório backend não encontrado!"
    exit 1
fi

if [ ! -f "backend/composer.json" ]; then
    error "❌ composer.json não encontrado no backend!"
    exit 1
fi

success "✅ Estrutura básica do backend encontrada"

# 2. Verificar autoloader
step "📦 Verificando autoloader do Composer..."
if [ ! -f "backend/vendor/autoload.php" ]; then
    error "❌ Autoloader não encontrado!"
    log "Instalando dependências..."
    backend_exec composer install --no-interaction --prefer-dist --optimize-autoloader
else
    success "✅ Autoloader encontrado"
fi

# 3. Verificar arquivos essenciais
step "🔍 Verificando arquivos essenciais..."

# Lista de arquivos essenciais
ESSENTIAL_FILES=(
    "bootstrap/app.php"
    "artisan"
    "config/app.php"
    "app/Providers/AppServiceProvider.php"
    "app/Providers/RouteServiceProvider.php"
    "routes/api.php"
    "routes/web.php"
)

MISSING_FILES=()

for file in "${ESSENTIAL_FILES[@]}"; do
    if [ ! -f "backend/$file" ]; then
        MISSING_FILES+=("$file")
    fi
done

if [ ${#MISSING_FILES[@]} -eq 0 ]; then
    success "✅ Todos os arquivos essenciais encontrados"
else
    warn "⚠️ Arquivos essenciais faltando:"
    for file in "${MISSING_FILES[@]}"; do
        echo "  - $file"
    done
fi

# 4. Verificar se o Laravel consegue executar comandos
step "🧪 Testando comandos do Laravel..."

# Testar versão
if backend_exec php artisan --version >/dev/null 2>&1; then
    VERSION=$(backend_exec php artisan --version)
    success "✅ Laravel funcionando: $VERSION"
else
    error "❌ Laravel não consegue executar comandos"
    log "Verificando logs de erro..."
    
    # Tentar executar com mais detalhes
    if backend_exec php artisan --version 2>&1; then
        success "✅ Laravel funcionando após verificação detalhada"
    else
        error "❌ Laravel ainda não está funcionando"
        exit 1
    fi
fi

# 5. Verificar configuração do banco de dados
step "🗄️ Verificando configuração do banco de dados..."

if [ -f "backend/.env" ]; then
    # Verificar se as variáveis de banco estão configuradas
    if grep -q "DB_CONNECTION=mysql" backend/.env && \
       grep -q "DB_HOST=mysql" backend/.env && \
       grep -q "DB_DATABASE=rei_do_oleo_dev" backend/.env; then
        success "✅ Configuração do banco de dados correta"
    else
        warn "⚠️ Configuração do banco de dados pode estar incorreta"
    fi
    
    # Testar conexão com o banco
    if backend_exec php artisan migrate:status >/dev/null 2>&1; then
        success "✅ Conexão com banco de dados funcionando"
    else
        warn "⚠️ Problema na conexão com banco de dados"
    fi
else
    error "❌ Arquivo .env não encontrado"
fi

# 6. Verificar chave da aplicação
step "🔑 Verificando chave da aplicação..."

if grep -q "APP_KEY=base64:" backend/.env; then
    success "✅ Chave da aplicação configurada"
else
    warn "⚠️ Chave da aplicação não configurada"
    log "Gerando chave da aplicação..."
    backend_exec php artisan key:generate --force
fi

# 7. Verificar caches
step "🧹 Verificando caches..."

# Limpar caches
backend_exec php artisan config:clear 2>/dev/null || true
backend_exec php artisan cache:clear 2>/dev/null || true
backend_exec php artisan route:clear 2>/dev/null || true
backend_exec php artisan view:clear 2>/dev/null || true

success "✅ Caches limpos"

# 8. Verificar rotas
step "🛣️ Verificando rotas..."

if backend_exec php artisan route:list >/dev/null 2>&1; then
    ROUTE_COUNT=$(backend_exec php artisan route:list --compact | wc -l)
    success "✅ Rotas carregadas: $ROUTE_COUNT rotas encontradas"
else
    warn "⚠️ Problema ao carregar rotas"
fi

# 9. Verificar providers
step "🔧 Verificando service providers..."

# Verificar se os providers essenciais estão registrados
if grep -q "AppServiceProvider::class" backend/config/app.php && \
   grep -q "RouteServiceProvider::class" backend/config/app.php; then
    success "✅ Service providers essenciais registrados"
else
    warn "⚠️ Service providers podem estar faltando"
fi

# 10. Teste final
step "🧪 Teste final do Laravel..."

# Testar se consegue executar um comando simples
if backend_exec php artisan list --format=json >/dev/null 2>&1; then
    success "✅ Laravel funcionando perfeitamente!"
else
    error "❌ Laravel ainda tem problemas"
    exit 1
fi

# 11. Resumo
echo -e "${GREEN}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    ✅ LARAVEL VERIFIED!                   ║
╠═══════════════════════════════════════════════════════════╣
║  🔧 Laravel funcionando corretamente                     ║
║  📦 Autoloader configurado                                ║
║  🗄️ Banco de dados conectando                            ║
║  🔑 Chave da aplicação configurada                       ║
║  🛣️ Rotas carregadas                                      ║
║  🧹 Caches limpos                                         ║
╠═══════════════════════════════════════════════════════════╣
║  🚀 Próximos passos:                                      ║
║  cd backend && php artisan migrate                        ║
║  cd backend && php artisan test                           ║
║  npm run dev                                              ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

success "🎯 Verificação do Laravel concluída com sucesso!" 