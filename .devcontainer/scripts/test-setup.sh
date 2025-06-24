#!/bin/bash

# 🧪 Rei do Óleo - Script de Teste do Ambiente
# Verifica se todos os serviços estão funcionando corretamente

set -e

echo "🧪 Testando ambiente de desenvolvimento..."
echo "========================================"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para testar serviços
test_service() {
    local service_name=$1
    local test_command=$2
    local expected_result=$3
    
    echo -n "Testing $service_name... "
    
    if eval "$test_command" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ OK${NC}"
        return 0
    else
        echo -e "${RED}❌ FAIL${NC}"
        return 1
    fi
}

# Função para testar porta
test_port() {
    local service_name=$1
    local host=$2
    local port=$3
    
    echo -n "Testing $service_name port $port... "
    
    if timeout 5 bash -c "</dev/tcp/$host/$port" 2>/dev/null; then
        echo -e "${GREEN}✅ OK${NC}"
        return 0
    else
        echo -e "${RED}❌ FAIL${NC}"
        return 1
    fi
}

echo ""
echo "🔧 Testando ferramentas básicas..."
echo "--------------------------------"

# Testar PHP
test_service "PHP 8.2" "php --version | grep -q '8.2'" || true

# Testar Composer
test_service "Composer" "composer --version" || true

# Testar Node.js
test_service "Node.js" "node --version" || true

# Testar NPM
test_service "NPM" "npm --version" || true

echo ""
echo "🗄️ Testando serviços de banco de dados..."
echo "----------------------------------------"

# Testar MySQL
test_service "MySQL Connection" "mysql -h mysql -u rei_do_oleo -psecret123 -e 'SELECT 1' 2>/dev/null" || true

# Testar Redis
test_service "Redis Connection" "redis-cli -h redis ping | grep -q PONG" || true

echo ""
echo "🌐 Testando portas dos serviços..."
echo "--------------------------------"

# Testar portas locais
test_port "MySQL" "localhost" "3309" || true
test_port "Redis" "localhost" "6379" || true
test_port "phpMyAdmin" "localhost" "8081" || true
test_port "Redis Commander" "localhost" "6380" || true
test_port "MailHog" "localhost" "8025" || true

echo ""
echo "📁 Testando estrutura do projeto..."
echo "---------------------------------"

# Verificar estrutura
test_service "Backend directory" "[ -d '/workspace/backend' ]" || true
test_service "Frontend directory" "[ -d '/workspace/frontend' ]" || true
test_service "Composer.json" "[ -f '/workspace/backend/composer.json' ]" || true

echo ""
echo "🎯 Testando comandos Laravel..."
echo "-----------------------------"

if [ -d "/workspace/backend" ]; then
    cd /workspace/backend
    
    # Testar Artisan
    test_service "Laravel Artisan" "php artisan --version" || true
    
    # Testar .env
    test_service "Laravel .env" "[ -f '.env' ]" || true
    
    # Testar conexão com banco
    if [ -f ".env" ]; then
        test_service "Laravel DB Connection" "php artisan migrate:status 2>/dev/null" || true
    fi
fi

echo ""
echo "📊 Resumo dos Serviços..."
echo "========================"

echo "🌐 URLs disponíveis:"
echo "  - Laravel API: http://localhost:8000"
echo "  - React App: http://localhost:3000"
echo "  - phpMyAdmin: http://localhost:8081"
echo "  - Redis Commander: http://localhost:6380"
echo "  - MailHog: http://localhost:8025"

echo ""
echo "🔧 Comandos úteis:"
echo "  - art serve --host=0.0.0.0 --port=8000"
echo "  - npm run dev"
echo "  - mysql-cli"
echo "  - redis-cli -h redis"

echo ""
echo "✅ Teste concluído!"
echo ""

# Verificar se há erros críticos
if ! mysql -h mysql -u rei_do_oleo -psecret123 -e 'SELECT 1' > /dev/null 2>&1; then
    echo -e "${YELLOW}⚠️  MySQL pode não estar completamente inicializado ainda.${NC}"
    echo "   Aguarde alguns minutos e tente novamente."
fi

if ! redis-cli -h redis ping > /dev/null 2>&1; then
    echo -e "${YELLOW}⚠️  Redis pode não estar acessível.${NC}"
    echo "   Verifique se o container Redis está rodando."
fi

echo "🎉 Ambiente pronto para desenvolvimento!" 