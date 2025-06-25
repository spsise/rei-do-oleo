#!/bin/bash

# 🧪 Script de teste para PHPUnit - Sistema Rei do Óleo

echo "🔍 Verificando configuração do PHPUnit..."

# Verificar se estamos no diretório correto
if [[ ! -f "backend/vendor/bin/phpunit" ]]; then
    echo "❌ PHPUnit não encontrado em backend/vendor/bin/phpunit"
    echo "📁 Diretório atual: $(pwd)"
    echo "📋 Conteúdo:"
    ls -la
    exit 1
fi

echo "✅ PHPUnit encontrado!"
echo "📍 Caminho: $(realpath backend/vendor/bin/phpunit)"

# Testar execução do PHPUnit
echo ""
echo "🧪 Testando execução básica..."
cd backend

# Verificar versão
echo "📋 Versão do PHPUnit:"
./vendor/bin/phpunit --version

echo ""
echo "🔧 Testando configuração..."
./vendor/bin/phpunit --configuration=phpunit.xml --list-tests | head -10

echo ""
echo "🧪 Executando um teste específico..."
./vendor/bin/phpunit tests/Unit/Models/ClientTest.php --filter=it_has_correct_fillable_attributes

echo ""
echo "✅ Teste concluído! Se chegou até aqui, o PHPUnit está funcionando."
echo "🔧 Para usar no VS Code:"
echo "   1. Abra o arquivo: rei-do-oleo.code-workspace"
echo "   2. Clique em 'Open Workspace'"
echo "   3. Instale as extensões recomendadas"
echo "   4. Os links 'Run Test' devem aparecer nos arquivos de teste"
