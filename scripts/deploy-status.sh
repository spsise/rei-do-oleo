#!/bin/bash

# Script para verificar status completo do deploy
# Execute este script no servidor para verificar se tudo está funcionando

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir com cores
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️ $1${NC}"
}

echo "🔍 Verificação Completa do Deploy"
echo "=================================="

# Diretórios
PROJECT_ROOT="/home/$(whoami)"
API_DIR="$PROJECT_ROOT/api-hom.virtualt.com.br"
FRONTEND_DIR="$PROJECT_ROOT/app-hom.virtualt.com.br"

echo ""
print_info "1. Verificando estrutura de diretórios..."

# Verificar se os diretórios existem
if [ -d "$API_DIR" ]; then
    print_status "Diretório da API existe"
else
    print_error "Diretório da API não existe"
fi

if [ -d "$FRONTEND_DIR" ]; then
    print_status "Diretório do Frontend existe"
else
    print_error "Diretório do Frontend não existe"
fi

echo ""
print_info "2. Verificando arquivos da API..."

# Verificar arquivos essenciais da API
if [ -f "$API_DIR/.env" ]; then
    print_status "Arquivo .env da API existe"
else
    print_error "Arquivo .env da API não existe"
fi

if [ -d "$API_DIR/vendor" ]; then
    print_status "Dependências Composer instaladas"
else
    print_warning "Dependências Composer não encontradas"
fi

if [ -f "$API_DIR/artisan" ]; then
    print_status "Laravel Artisan encontrado"
else
    print_error "Laravel Artisan não encontrado"
fi

echo ""
print_info "3. Verificando permissões..."

# Verificar permissões
if [ -r "$API_DIR/storage" ] && [ -w "$API_DIR/storage" ]; then
    print_status "Permissões do storage OK"
else
    print_warning "Permissões do storage podem estar incorretas"
fi

if [ -r "$API_DIR/bootstrap/cache" ] && [ -w "$API_DIR/bootstrap/cache" ]; then
    print_status "Permissões do cache OK"
else
    print_warning "Permissões do cache podem estar incorretas"
fi

echo ""
print_info "4. Verificando configuração do banco..."

# Verificar configuração do banco
if [ -f "$API_DIR/.env" ]; then
    cd "$API_DIR"

    # Verificar se as variáveis do banco estão configuradas
    if grep -q "DB_HOST=" .env && grep -q "DB_DATABASE=" .env; then
        print_status "Configuração do banco encontrada"

        # Testar conexão com banco
        if php artisan migrate:status >/dev/null 2>&1; then
            print_status "Conexão com banco OK"
        else
            print_error "Erro na conexão com banco"
            echo "Verifique: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
        fi
    else
        print_error "Configuração do banco incompleta"
    fi
fi

echo ""
print_info "5. Verificando arquivos do Frontend..."

# Verificar arquivos do frontend
if [ -f "$FRONTEND_DIR/index.html" ]; then
    print_status "index.html encontrado"
else
    print_error "index.html não encontrado"
fi

if [ -d "$FRONTEND_DIR/assets" ]; then
    print_status "Diretório assets encontrado"
    echo "Arquivos assets: $(ls "$FRONTEND_DIR/assets" | wc -l)"
else
    print_error "Diretório assets não encontrado"
fi

echo ""
print_info "6. Verificando .htaccess..."

# Verificar .htaccess
if [ -f "$API_DIR/.htaccess" ]; then
    print_status ".htaccess da API encontrado"
else
    print_error ".htaccess da API não encontrado"
fi

if [ -f "$FRONTEND_DIR/.htaccess" ]; then
    print_status ".htaccess do Frontend encontrado"
else
    print_error ".htaccess do Frontend não encontrado"
fi

echo ""
print_info "7. Verificando logs..."

# Verificar logs
if [ -f "$PROJECT_ROOT/deploy.log" ]; then
    print_status "Log de deploy encontrado"
    echo "Últimas 5 linhas do log:"
    tail -5 "$PROJECT_ROOT/deploy.log"
else
    print_warning "Log de deploy não encontrado"
fi

if [ -f "$API_DIR/storage/logs/laravel.log" ]; then
    print_status "Log do Laravel encontrado"
    echo "Últimas 3 linhas do log:"
    tail -3 "$API_DIR/storage/logs/laravel.log"
else
    print_warning "Log do Laravel não encontrado"
fi

echo ""
print_info "8. Testando conectividade..."

# Testar conectividade
echo "Testando API:"
if curl -s -I https://api-hom.virtualt.com.br | grep -q "200\|301\|302"; then
    print_status "API respondendo"
else
    print_error "API não responde"
fi

echo "Testando Frontend:"
if curl -s -I https://app-hom.virtualt.com.br | grep -q "200\|301\|302"; then
    print_status "Frontend respondendo"
else
    print_error "Frontend não responde"
fi

echo ""
print_info "9. Verificando Git Hook..."

# Verificar Git Hook
if [ -f "$PROJECT_ROOT/.git/hooks/post-receive" ]; then
    print_status "Git Hook encontrado"

    if [ -x "$PROJECT_ROOT/.git/hooks/post-receive" ]; then
        print_status "Git Hook executável"
    else
        print_warning "Git Hook não é executável"
        chmod +x "$PROJECT_ROOT/.git/hooks/post-receive"
        print_status "Permissão corrigida"
    fi
else
    print_error "Git Hook não encontrado"
fi

echo ""
print_info "10. Verificando configuração Git..."

# Verificar configuração Git
cd "$PROJECT_ROOT"
if git remote -v | grep -q "spsise/rei-do-oleo"; then
    print_status "Remote Git configurado corretamente"
    git remote -v
else
    print_error "Remote Git não configurado corretamente"
fi

echo ""
print_info "Resumo da verificação:"

# Contar erros e avisos
errors=$(grep -c "❌" <<< "$(cat /dev/stdin)" 2>/dev/null || echo "0")
warnings=$(grep -c "⚠️" <<< "$(cat /dev/stdin)" 2>/dev/null || echo "0")

if [ "$errors" -gt 0 ]; then
    print_error "Encontrados $errors erro(s) que precisam ser corrigidos"
    echo ""
    print_info "Para corrigir os erros:"
    echo "1. Verifique as configurações do .env"
    echo "2. Execute: chmod -R 755 $API_DIR/storage"
    echo "3. Execute: chmod -R 755 $API_DIR/bootstrap/cache"
    echo "4. Verifique a configuração do banco de dados"
    echo "5. Execute: composer install --no-dev em $API_DIR"
fi

if [ "$warnings" -gt 0 ]; then
    print_warning "Encontrados $warnings aviso(s) - verifique se necessário"
fi

if [ "$errors" -eq 0 ] && [ "$warnings" -eq 0 ]; then
    print_status "Tudo configurado corretamente!"
    echo ""
    print_info "Para testar o deploy:"
    echo "1. Faça uma alteração no código"
    echo "2. Commit e push: git add . && git commit -m 'test' && git push origin hostinger-hom"
    echo "3. Monitore: tail -f ~/deploy.log"
fi

echo ""
print_info "Para monitoramento contínuo:"
echo "tail -f ~/deploy.log"
echo "tail -f $API_DIR/storage/logs/laravel.log"
echo "./check-subdomains.sh"
