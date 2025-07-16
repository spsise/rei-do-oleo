#!/bin/bash

# Script para verificar e limpar diretórios .git existentes
# Execute este script antes de configurar o Git Hooks

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

echo "🔍 Verificação de Status do Git"
echo "================================"

# Diretório do projeto
PROJECT_ROOT="/home/$(whoami)"

echo ""
print_info "Verificando diretório: $PROJECT_ROOT"

# Verificar se existe .git
if [ -d "$PROJECT_ROOT/.git" ]; then
    print_warning "Diretório .git encontrado!"

    # Verificar se tem configuração válida
    if [ -f "$PROJECT_ROOT/.git/config" ]; then
        print_info "Arquivo de configuração Git encontrado"

        echo ""
        echo "📋 Configuração atual:"
        echo "======================"

        # Mostrar remotes configurados
        cd "$PROJECT_ROOT"
        echo "Remotes configurados:"
        git remote -v 2>/dev/null || echo "Nenhum remote configurado"

        echo ""
        echo "Branches disponíveis:"
        git branch -a 2>/dev/null || echo "Nenhuma branch encontrada"

        echo ""
        echo "Último commit:"
        git log --oneline -1 2>/dev/null || echo "Nenhum commit encontrado"

        # Verificar se é do projeto correto
        if grep -q "spsise/rei-do-oleo" "$PROJECT_ROOT/.git/config" 2>/dev/null; then
            echo ""
            print_status "✅ .git já configurado para o projeto correto (spsise/rei-do-oleo)"
            echo ""
            print_info "Opções disponíveis:"
            echo "1. Usar configuração atual (recomendado se já estiver funcionando)"
            echo "2. Limpar e reconfigurar (se houver problemas)"
            echo "3. Fazer backup e reconfigurar (mais seguro)"

            read -p "Escolha uma opção (1/2/3): " choice

            case $choice in
                1)
                    print_status "Usando configuração atual"
                    echo "Você pode prosseguir com a configuração do Git Hooks"
                    ;;
                2)
                    print_warning "Limpando configuração atual..."
                    read -p "Tem certeza? Isso removerá todo o histórico local (y/N): " -n 1 -r
                    echo
                    if [[ $REPLY =~ ^[Yy]$ ]]; then
                        rm -rf "$PROJECT_ROOT/.git"
                        print_status "Diretório .git removido"
                        echo "Execute o script de configuração novamente"
                    else
                        print_info "Operação cancelada"
                    fi
                    ;;
                3)
                    print_info "Fazendo backup da configuração atual..."
                    BACKUP_DIR="$PROJECT_ROOT/git-backup-$(date +%Y%m%d-%H%M%S)"
                    mkdir -p "$BACKUP_DIR"
                    cp -r "$PROJECT_ROOT/.git" "$BACKUP_DIR/"
                    print_status "Backup criado em: $BACKUP_DIR"

                    read -p "Deseja limpar e reconfigurar? (y/N): " -n 1 -r
                    echo
                    if [[ $REPLY =~ ^[Yy]$ ]]; then
                        rm -rf "$PROJECT_ROOT/.git"
                        print_status "Diretório .git removido"
                        echo "Execute o script de configuração novamente"
                    else
                        print_info "Operação cancelada"
                    fi
                    ;;
                *)
                    print_error "Opção inválida"
                    exit 1
                    ;;
            esac
        else
            echo ""
            print_warning "⚠️ .git existe mas é de outro projeto"

            # Mostrar qual projeto está configurado
            echo "Projeto configurado atualmente:"
            grep -E "(url|remote)" "$PROJECT_ROOT/.git/config" 2>/dev/null || echo "Sem configuração de remote"

            echo ""
            print_info "Opções disponíveis:"
            echo "1. Limpar e configurar para spsise/rei-do-oleo"
            echo "2. Fazer backup e configurar para spsise/rei-do-oleo"
            echo "3. Cancelar e configurar manualmente"

            read -p "Escolha uma opção (1/2/3): " choice

            case $choice in
                1)
                    print_warning "Limpando configuração atual..."
                    read -p "Tem certeza? (y/N): " -n 1 -r
                    echo
                    if [[ $REPLY =~ ^[Yy]$ ]]; then
                        rm -rf "$PROJECT_ROOT/.git"
                        print_status "Diretório .git removido"
                        echo "Execute o script de configuração novamente"
                    else
                        print_info "Operação cancelada"
                    fi
                    ;;
                2)
                    print_info "Fazendo backup da configuração atual..."
                    BACKUP_DIR="$PROJECT_ROOT/git-backup-$(date +%Y%m%d-%H%M%S)"
                    mkdir -p "$BACKUP_DIR"
                    cp -r "$PROJECT_ROOT/.git" "$BACKUP_DIR/"
                    print_status "Backup criado em: $BACKUP_DIR"

                    rm -rf "$PROJECT_ROOT/.git"
                    print_status "Diretório .git removido"
                    echo "Execute o script de configuração novamente"
                    ;;
                3)
                    print_info "Operação cancelada"
                    echo "Configure manualmente o .git ou remova-o antes de continuar"
                    ;;
                *)
                    print_error "Opção inválida"
                    exit 1
                    ;;
            esac
        fi
    else
        print_warning "⚠️ .git existe mas não tem configuração válida"

        read -p "Deseja limpar e reconfigurar? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            rm -rf "$PROJECT_ROOT/.git"
            print_status "Diretório .git removido"
            echo "Execute o script de configuração novamente"
        else
            print_info "Operação cancelada"
        fi
    fi
else
    print_status "✅ Nenhum diretório .git encontrado"
    echo "Pode prosseguir com a configuração do Git Hooks"
fi

echo ""
print_info "Verificando outros diretórios .git no sistema..."

# Verificar se existem outros .git em subdiretórios
find "$PROJECT_ROOT" -name ".git" -type d 2>/dev/null | while read git_dir; do
    if [ "$git_dir" != "$PROJECT_ROOT/.git" ]; then
        print_warning "⚠️ Encontrado .git em: $git_dir"
    fi
done

echo ""
print_info "Verificação concluída!"

echo ""
print_info "Próximos passos:"
echo "1. Se não há .git ou foi removido: execute setup-git-hooks-subdomains.sh"
echo "2. Se .git já está configurado corretamente: pode prosseguir com a configuração"
echo "3. Se há problemas: resolva manualmente ou use este script novamente"
