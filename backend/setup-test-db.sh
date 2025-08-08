#!/bin/bash

# Script para configurar banco de dados de teste
echo "🔧 Configurando banco de dados de teste..."

# Criar banco de dados de teste
docker-compose exec mysql mysql -u reidooleo -preidooleo123 -e "CREATE DATABASE IF NOT EXISTS rei_do_oleo_test;"

# Executar migrations no banco de teste
docker-compose exec backend php artisan migrate --env=testing

# Executar seeders no banco de teste (se necessário)
# docker-compose exec backend php artisan db:seed --env=testing

echo "✅ Banco de dados de teste configurado!"
echo "🚀 Execute os testes com: docker-compose exec backend php artisan test"
