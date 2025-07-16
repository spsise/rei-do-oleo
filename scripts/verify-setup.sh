#!/bin/bash

# Script de Verificação - Git Hooks Setup
# Execute este script para verificar se tudo está configurado corretamente

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

echo "🔍 Verificação da Configuração Git Hooks"
echo "========================================"

# Verificar se estamos no servidor
if [[ "$(hostname)" == *"hostinger"* ]] || [[ "$(pwd)" == *"public_html"* ]]; then
    print_info "Executando no servidor Hostinger..."
    IS_SERVER=true
else
    print_info "Executando localmente..."
    IS_SERVER=false
fi

echo ""

# Verificações locais
if [ "$IS_SERVER" = false ]; then
    print_info "Verificações locais:"

    # Verificar se o script existe
    if [ -f "scripts/setup-git-hooks.sh" ]; then
        print_status "Script setup-git-hooks.sh encontrado"
    else
        print_error "Script setup-git-hooks.sh não encontrado"
    fi

    # Verificar se o projeto tem a estrutura correta
    if [ -d "backend" ] && [ -d "frontend" ]; then
        print_status "Estrutura do projeto OK"
    else
        print_error "Estrutura do projeto incorreta"
    fi

    # Verificar se o Git está configurado
    if git remote -v | grep -q "origin"; then
        print_status "Repositório Git configurado"
        git remote -v | grep origin
    else
        print_warning "Repositório Git não configurado"
    fi

    # Verificar se os arquivos .env existem
    if [ -f "backend/.env.example" ]; then
        print_status "Arquivo .env.example do backend encontrado"
    else
        print_error "Arquivo .env.example do backend não encontrado"
    fi

    if [ -f "frontend/.env.example" ]; then
        print_status "Arquivo .env.example do frontend encontrado"
    else
        print_error "Arquivo .env.example do frontend não encontrado"
    fi

    echo ""
    print_info "Para continuar, execute no servidor:"
    echo "scp scripts/setup-git-hooks.sh usuario@seudominio.com:~/"
    echo "ssh usuario@seudominio.com"
    echo "chmod +x setup-git-hooks.sh"
    echo "./setup-git-hooks.sh"

    exit 0
fi

# Verificações no servidor
print_info "Verificações no servidor:"

# Verificar se estamos no diretório correto
if [[ "$(pwd)" == *"public_html"* ]]; then
    print_status "Diretório public_html OK"
else
    print_warning "Não está no diretório public_html"
    cd ~/public_html 2>/dev/null || {
        print_error "Não foi possível acessar public_html"
        exit 1
    }
fi

# Verificar se o Git está inicializado
if [ -d ".git" ]; then
    print_status "Repositório Git inicializado"
else
    print_error "Repositório Git não inicializado"
    print_info "Execute: ./setup-git-hooks.sh"
    exit 1
fi

# Verificar se o hook existe
if [ -f ".git/hooks/post-receive" ]; then
    print_status "Hook post-receive encontrado"

    # Verificar permissões
    if [ -x ".git/hooks/post-receive" ]; then
        print_status "Hook post-receive executável"
    else
        print_warning "Hook post-receive não é executável"
        chmod +x .git/hooks/post-receive
        print_status "Permissão corrigida"
    fi
else
    print_error "Hook post-receive não encontrado"
    print_info "Execute: ./setup-git-hooks.sh"
    exit 1
fi

# Verificar se o remote está configurado
if git remote -v | grep -q "origin"; then
    print_status "Remote origin configurado"
    git remote -v | grep origin
else
    print_warning "Remote origin não configurado"
    print_info "Execute: git remote set-url origin https://github.com/SEU_USUARIO/rei-do-oleo.git"
fi

# Verificar se o script de deploy manual existe
if [ -f "deploy.sh" ]; then
    print_status "Script deploy.sh encontrado"

    if [ -x "deploy.sh" ]; then
        print_status "Script deploy.sh executável"
    else
        print_warning "Script deploy.sh não é executável"
        chmod +x deploy.sh
        print_status "Permissão corrigida"
    fi
else
    print_error "Script deploy.sh não encontrado"
fi

# Verificar estrutura de diretórios
if [ -d "api" ]; then
    print_status "Diretório api encontrado"
else
    print_warning "Diretório api não encontrado (será criado no primeiro deploy)"
fi

# Verificar arquivos .env
if [ -f "api/.env" ]; then
    print_status "Arquivo .env do backend encontrado"

    # Verificar configurações importantes
    if grep -q "APP_ENV=production" api/.env; then
        print_status "APP_ENV=production configurado"
    else
        print_warning "APP_ENV não está configurado como production"
    fi

    if grep -q "APP_DEBUG=false" api/.env; then
        print_status "APP_DEBUG=false configurado"
    else
        print_warning "APP_DEBUG não está configurado como false"
    fi
else
    print_warning "Arquivo .env do backend não encontrado"
    print_info "Será criado no primeiro deploy"
fi

# Verificar permissões
print_info "Verificando permissões:"

# Verificar permissões do diretório atual
if [ -r "." ] && [ -w "." ] && [ -x "." ]; then
    print_status "Permissões do diretório atual OK"
else
    print_warning "Permissões do diretório atual podem estar incorretas"
fi

# Verificar permissões do .git
if [ -r ".git" ] && [ -x ".git" ]; then
    print_status "Permissões do .git OK"
else
    print_warning "Permissões do .git podem estar incorretas"
fi

# Verificar se o .htaccess existe
if [ -f ".htaccess" ]; then
    print_status "Arquivo .htaccess encontrado"
else
    print_warning "Arquivo .htaccess não encontrado (será criado no deploy)"
fi

# Verificar se o .htaccess da API existe
if [ -f "api/.htaccess" ]; then
    print_status "Arquivo .htaccess da API encontrado"
else
    print_warning "Arquivo .htaccess da API não encontrado (será criado no deploy)"
fi

# Verificar logs
print_info "Verificando logs:"

if [ -f "deploy.log" ]; then
    print_status "Arquivo de log deploy.log encontrado"
    echo "Últimas 5 linhas do log:"
    tail -5 deploy.log 2>/dev/null || echo "Log vazio"
else
    print_warning "Arquivo de log deploy.log não encontrado"
fi

# Verificar se o Laravel está funcionando (se existir)
if [ -d "api" ] && [ -f "api/artisan" ]; then
    print_info "Verificando Laravel:"

    cd api

    if php artisan --version >/dev/null 2>&1; then
        print_status "Laravel funcionando"

        # Verificar se as migrações foram executadas
        if php artisan migrate:status >/dev/null 2>&1; then
            print_status "Migrações configuradas"
        else
            print_warning "Migrações não configuradas"
        fi
    else
        print_warning "Laravel não está funcionando corretamente"
    fi

    cd ..
fi

# Verificar espaço em disco
print_info "Verificando espaço em disco:"
df -h . | grep -E "(Filesystem|%)"

# Verificar memória disponível
print_info "Verificando memória:"
free -h | grep -E "(Mem|Swap)"

echo ""
print_info "Resumo da verificação:"

# Contar erros e avisos
errors=$(grep -c "❌" <<< "$(cat /dev/stdin)" 2>/dev/null || echo "0")
warnings=$(grep -c "⚠️" <<< "$(cat /dev/stdin)" 2>/dev/null || echo "0")

if [ "$errors" -gt 0 ]; then
    print_error "Encontrados $errors erro(s) que precisam ser corrigidos"
    echo ""
    print_info "Para corrigir os erros:"
    echo "1. Execute: ./setup-git-hooks.sh"
    echo "2. Configure o remote: git remote set-url origin https://github.com/SEU_USUARIO/rei-do-oleo.git"
    echo "3. Configure o .env: cp .env.example .env && nano .env"
fi

if [ "$warnings" -gt 0 ]; then
    print_warning "Encontrados $warnings aviso(s) - verifique se necessário"
fi

if [ "$errors" -eq 0 ] && [ "$warnings" -eq 0 ]; then
    print_status "Tudo configurado corretamente!"
    echo ""
    print_info "Para testar o deploy:"
    echo "1. Faça uma alteração no código"
    echo "2. Commit e push: git add . && git commit -m 'test' && git push origin main"
    echo "3. Monitore: tail -f deploy.log"
fi

echo ""
print_info "Para mais informações, consulte: docs/GIT_HOOKS_SETUP.md"
