#!/bin/bash

# 🚀 Rei do Óleo - Post Create Script
# Script executado após a criação do devcontainer

set -e

echo "🔧 Configurando ambiente de desenvolvimento..."

# Aguardar MySQL estar pronto
echo "⏳ Aguardando MySQL estar pronto..."
until mysqladmin ping -h mysql -u root -proot123 --silent; do
    echo "Aguardando MySQL..."
    sleep 2
done

echo "✅ MySQL está pronto!"

# Aguardar Redis estar pronto
echo "⏳ Aguardando Redis estar pronto..."
until redis-cli -h redis ping; do
    echo "Aguardando Redis..."
    sleep 1
done

echo "✅ Redis está pronto!"

# Configurar permissões do workspace
sudo chown -R vscode:vscode /workspace
chmod -R 755 /workspace

# Navegar para o backend
cd /workspace/backend

# Verificar se o composer.json existe
if [ ! -f "composer.json" ]; then
    echo "❌ Arquivo composer.json não encontrado no backend!"
    echo "Criando structure básica do Laravel..."
    
    # Criar projeto Laravel básico
    composer create-project laravel/laravel . --prefer-dist --no-dev
    
    # Configurar .env
    cp .env.example .env
    php artisan key:generate
else
    echo "📦 Instalando dependências PHP..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Configurar .env se não existir
if [ ! -f ".env" ]; then
    echo "⚙️ Configurando .env..."
    cp .env.example .env
    php artisan key:generate
fi

# Atualizar configuração do banco de dados no .env
echo "🗄️ Configurando banco de dados..."
sed -i "s/DB_HOST=.*/DB_HOST=mysql/" .env
sed -i "s/DB_PORT=.*/DB_PORT=3306/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=rei_do_oleo_dev/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=rei_do_oleo/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=secret123/" .env

# Configurar Redis
sed -i "s/REDIS_HOST=.*/REDIS_HOST=redis/" .env
sed -i "s/REDIS_PORT=.*/REDIS_PORT=6379/" .env

# Configurar cache e sessões
sed -i "s/CACHE_DRIVER=.*/CACHE_DRIVER=redis/" .env
sed -i "s/SESSION_DRIVER=.*/SESSION_DRIVER=redis/" .env

# Configurar mail para desenvolvimento
sed -i "s/MAIL_MAILER=.*/MAIL_MAILER=smtp/" .env
sed -i "s/MAIL_HOST=.*/MAIL_HOST=mailhog/" .env
sed -i "s/MAIL_PORT=.*/MAIL_PORT=1025/" .env

# Limpar cache
echo "🧹 Limpando cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Testar conexão com banco
echo "🔍 Testando conexão com banco de dados..."
php artisan migrate:status || echo "⚠️  Migrations ainda não foram executadas"

# Configurar frontend se existir
if [ -d "/workspace/frontend" ]; then
    echo "📦 Configurando frontend..."
    cd /workspace/frontend
    
    if [ -f "package.json" ]; then
        npm ci
        echo "✅ Dependências do frontend instaladas!"
    else
        echo "⚠️  package.json não encontrado no frontend"
    fi
fi

echo ""
echo "🎉 Ambiente configurado com sucesso!"
echo ""
echo "🚀 Comandos úteis:"
echo "  - php artisan serve --host=0.0.0.0 --port=8000  # Iniciar Laravel"
echo "  - npm run dev                                    # Iniciar React"
echo "  - php artisan migrate:fresh --seed              # Reset database"
echo "  - php artisan tinker                            # Laravel console"
echo ""
echo "🌐 URLs disponíveis:"
echo "  - Laravel API: http://localhost:8000"
echo "  - React App: http://localhost:3000"
echo "  - phpMyAdmin: http://localhost:8080"
echo "  - Redis Commander: http://localhost:6380"
echo "  - MailHog: http://localhost:8025"
echo "" 