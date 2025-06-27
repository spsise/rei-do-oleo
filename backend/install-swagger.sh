#!/bin/bash

# 🚀 Script de Instalação do Swagger - Rei do Óleo API
# Autor: Equipe de Desenvolvimento
# Data: $(date)

echo "🚀 Iniciando instalação do Swagger na API Rei do Óleo..."

# Verificar se está no diretório correto
if [ ! -f "artisan" ]; then
    echo "❌ Erro: Execute este script no diretório raiz do Laravel (onde está o arquivo artisan)"
    exit 1
fi

# 1. Instalar dependência do L5 Swagger (se não estiver instalada)
echo "📦 Verificando dependência do L5 Swagger..."
if ! grep -q "darkaonline/l5-swagger" composer.json; then
    echo "📦 Instalando L5 Swagger..."
    composer require "darkaonline/l5-swagger"
else
    echo "✅ L5 Swagger já está instalado"
fi

# 2. Publicar configurações (se não existirem)
echo "⚙️  Verificando configurações..."
if [ ! -f "config/l5-swagger.php" ]; then
    echo "⚙️  Publicando configurações do L5 Swagger..."
    php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
else
    echo "✅ Configurações já existem"
fi

# 3. Gerar documentação
echo "📚 Gerando documentação Swagger..."
php artisan l5-swagger:generate

# 4. Limpar cache
echo "🧹 Limpando cache..."
php artisan config:clear
php artisan route:clear

# 5. Verificar se o servidor está rodando
echo "🔍 Verificando status da API..."
if curl -s http://localhost:8000/api/health > /dev/null 2>&1; then
    echo "✅ API está rodando em http://localhost:8000"
else
    echo "⚠️  API não está rodando. Iniciando servidor..."
    echo "🚀 Executando: php artisan serve --port=8000"
    echo "   (Ctrl+C para parar o servidor)"
    echo ""
    php artisan serve --port=8000 &
    sleep 3
fi

# 6. Exibir informações finais
echo ""
echo "🎉 Instalação do Swagger concluída com sucesso!"
echo ""
echo "📋 Informações importantes:"
echo "   • Documentação Swagger: http://localhost:8000/api/documentation"
echo "   • JSON da API: http://localhost:8000/docs"
echo "   • Health Check: http://localhost:8000/api/health"
echo ""
echo "🔐 Para usar endpoints protegidos:"
echo "   1. Faça login em: POST /api/v1/auth/login"
echo "   2. Copie o token retornado"
echo "   3. Na documentação, clique em 'Authorize'"
echo "   4. Digite: Bearer {seu_token}"
echo ""
echo "🛠️  Para regenerar a documentação:"
echo "   php artisan l5-swagger:generate"
echo ""
echo "📚 Documentação detalhada em: SWAGGER_DOCS.md"
echo ""
echo "✨ Happy coding! 🚀"
