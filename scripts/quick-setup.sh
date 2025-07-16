#!/bin/bash

# Script de Configuração Rápida - Deploy Hostinger
# Execute este script para configurar rapidamente o deploy

set -e

echo "🚀 Configuração Rápida - Deploy Hostinger"
echo "=========================================="

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

# Verificar se estamos no diretório raiz do projeto
if [ ! -f "package.json" ] || [ ! -d "backend" ] || [ ! -d "frontend" ]; then
    print_error "Execute este script no diretório raiz do projeto!"
    exit 1
fi

echo ""
print_info "Escolha o método de deploy:"
echo "1) FTP/SFTP (Mais simples)"
echo "2) Git Hooks (Recomendado)"
echo "3) SSH (Avançado)"
echo "4) API Hostinger (Integração)"
echo ""

read -p "Digite o número da opção (1-4): " choice

case $choice in
    1)
        print_info "Configurando deploy via FTP/SFTP..."

        # Verificar se o workflow já existe
        if [ -f ".github/workflows/deploy-hostinger.yml" ]; then
            print_warning "Workflow FTP já existe!"
        else
            print_status "Workflow FTP configurado"
        fi

        echo ""
        print_info "Configure os secrets no GitHub:"
        echo "HOSTINGER_DOMAIN=seudominio.com"
        echo "HOSTINGER_USERNAME=seu_usuario"
        echo "HOSTINGER_PASSWORD=sua_senha"
        echo "HOSTINGER_PORT=21"
        ;;

    2)
        print_info "Configurando deploy via Git Hooks..."

        # Verificar se o script existe
        if [ -f "scripts/setup-git-hooks.sh" ]; then
            print_status "Script Git Hooks encontrado"
        else
            print_error "Script Git Hooks não encontrado!"
            exit 1
        fi

        echo ""
        print_info "Próximos passos:"
        echo "1. Acesse o servidor via SSH:"
        echo "   ssh usuario@seudominio.com"
        echo ""
        echo "2. Faça upload do script:"
        echo "   scp scripts/setup-git-hooks.sh usuario@seudominio.com:~/"
        echo ""
        echo "3. Execute no servidor:"
        echo "   chmod +x setup-git-hooks.sh"
        echo "   ./setup-git-hooks.sh"
        ;;

    3)
        print_info "Configurando deploy via SSH..."

        # Verificar se o workflow existe
        if [ -f ".github/workflows/deploy-ssh.yml" ]; then
            print_status "Workflow SSH encontrado"
        else
            print_error "Workflow SSH não encontrado!"
            exit 1
        fi

        echo ""
        print_info "Próximos passos:"
        echo "1. Gere uma chave SSH:"
        echo "   ssh-keygen -t rsa -b 4096 -C 'deploy@seudominio.com'"
        echo ""
        echo "2. Adicione a chave no servidor:"
        echo "   ssh-copy-id usuario@seudominio.com"
        echo ""
        echo "3. Configure os secrets no GitHub:"
        echo "   HOSTINGER_HOST=seudominio.com"
        echo "   HOSTINGER_USERNAME=seu_usuario"
        echo "   HOSTINGER_SSH_KEY=conteudo_da_chave_privada"
        echo "   HOSTINGER_DOMAIN=seudominio.com"
        ;;

    4)
        print_info "Configurando deploy via API Hostinger..."

        # Verificar se o script existe
        if [ -f "scripts/hostinger-deploy.js" ]; then
            print_status "Script API encontrado"
        else
            print_error "Script API não encontrado!"
            exit 1
        fi

        echo ""
        print_info "Próximos passos:"
        echo "1. Habilite a API na Hostinger:"
        echo "   Painel Hostinger → API → Gerar Token"
        echo ""
        echo "2. Configure as variáveis de ambiente:"
        echo "   export HOSTINGER_API_TOKEN=seu_token"
        echo "   export HOSTINGER_DOMAIN=seudominio.com"
        echo "   export HOSTINGER_USERNAME=seu_usuario"
        echo ""
        echo "3. Execute o deploy:"
        echo "   node scripts/hostinger-deploy.js"
        ;;

    *)
        print_error "Opção inválida!"
        exit 1
        ;;
esac

echo ""
print_info "Configuração do ambiente:"

# Verificar se os arquivos .env existem
if [ ! -f "backend/.env" ]; then
    print_warning "Arquivo .env do backend não encontrado"
    echo "Execute: cp backend/.env.example backend/.env"
fi

if [ ! -f "frontend/.env" ]; then
    print_warning "Arquivo .env do frontend não encontrado"
    echo "Execute: cp frontend/.env.example frontend/.env"
fi

echo ""
print_info "Configurações recomendadas para produção:"

# Backend .env
echo "Backend (.env):"
echo "APP_ENV=production"
echo "APP_DEBUG=false"
echo "APP_URL=https://seudominio.com/api"
echo "DB_HOST=localhost"
echo "DB_DATABASE=seu_banco"
echo "DB_USERNAME=seu_usuario"
echo "DB_PASSWORD=sua_senha"

echo ""
echo "Frontend (.env):"
echo "VITE_API_URL=https://seudominio.com/api"
echo "VITE_APP_URL=https://seudominio.com"

echo ""
print_info "Teste o build localmente antes do deploy:"

# Testar build do frontend
echo "Testando build do frontend..."
cd frontend
if npm run build > /dev/null 2>&1; then
    print_status "Build do frontend OK"
else
    print_error "Erro no build do frontend"
fi
cd ..

# Testar build do backend
echo "Testando build do backend..."
cd backend
if composer install --no-dev --optimize-autoloader > /dev/null 2>&1; then
    print_status "Build do backend OK"
else
    print_error "Erro no build do backend"
fi
cd ..

echo ""
print_status "Configuração concluída!"
echo ""
print_info "Documentação completa: docs/DEPLOY_HOSTINGER.md"
print_info "Para dúvidas, consulte a documentação ou entre em contato."
