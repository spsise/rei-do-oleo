#!/bin/bash

# 🔧 Fix Laravel Namespace Issue - Sistema Rei do Óleo
# Script para corrigir o problema "Unable to detect application namespace"

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
log() { echo -e "${GREEN}[FIX-NAMESPACE]${NC} $1"; }
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
║                🔧 FIX LARAVEL NAMESPACE                   ║
║           Corrigindo problema de namespace                ║
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

# 2. Verificar e corrigir autoloader do Composer
step "📦 Verificando autoloader do Composer..."
if [ ! -f "backend/vendor/autoload.php" ]; then
    log "Autoloader não encontrado, instalando dependências..."
    backend_exec composer install --no-interaction --prefer-dist --optimize-autoloader
else
    log "Regenerando autoloader..."
    backend_exec composer dump-autoload --optimize
fi

success "✅ Autoloader do Composer configurado"

# 3. Verificar arquivos essenciais do Laravel
step "🔍 Verificando arquivos essenciais do Laravel..."

# Verificar se existe o arquivo bootstrap/app.php
if [ ! -f "backend/bootstrap/app.php" ]; then
    error "❌ bootstrap/app.php não encontrado!"
    log "Criando bootstrap/app.php..."
    cat > backend/bootstrap/app.php << 'EOF'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(replace: [
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class => \App\Http\Middleware\VerifyCsrfToken::class,
        ]);

        $middleware->alias([
            'api.response' => \App\Http\Middleware\ApiResponse::class,
        ]);

        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
EOF
    success "✅ bootstrap/app.php criado"
else
    info "ℹ️ bootstrap/app.php já existe"
fi

# Verificar se existe o arquivo artisan
if [ ! -f "backend/artisan" ]; then
    error "❌ arquivo artisan não encontrado!"
    log "Criando artisan..."
    cat > backend/artisan << 'EOF'
#!/usr/bin/env php
<?php

use Illuminate\Foundation\Application;
use Symfony\Component\Console\Input\ArgvInput;

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the command...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$status = $app->handleCommand(new ArgvInput);

exit($status);
EOF
    chmod +x backend/artisan
    success "✅ arquivo artisan criado"
else
    info "ℹ️ arquivo artisan já existe"
fi

# 4. Verificar e criar diretórios essenciais
step "📁 Verificando diretórios essenciais..."

# Criar diretórios se não existirem
mkdir -p backend/app/Http/Controllers
mkdir -p backend/app/Http/Middleware
mkdir -p backend/app/Providers
mkdir -p backend/config
mkdir -p backend/routes
mkdir -p backend/database/migrations
mkdir -p backend/database/seeders
mkdir -p backend/storage/app/public
mkdir -p backend/storage/framework/cache
mkdir -p backend/storage/framework/sessions
mkdir -p backend/storage/framework/views
mkdir -p backend/storage/logs
mkdir -p backend/bootstrap/cache

success "✅ Diretórios essenciais verificados"

# 5. Verificar e criar arquivos de configuração essenciais
step "⚙️ Verificando arquivos de configuração..."

# Verificar config/app.php
if [ ! -f "backend/config/app.php" ]; then
    log "Criando config/app.php..."
    cat > backend/config/app.php << 'EOF'
<?php

return [
    'name' => env('APP_NAME', 'Laravel'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
    'providers' => [
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ],
];
EOF
    success "✅ config/app.php criado"
else
    info "ℹ️ config/app.php já existe"
fi

# 6. Verificar e criar providers essenciais
step "🔧 Verificando service providers..."

# AppServiceProvider
if [ ! -f "backend/app/Providers/AppServiceProvider.php" ]; then
    log "Criando AppServiceProvider..."
    cat > backend/app/Providers/AppServiceProvider.php << 'EOF'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
EOF
    success "✅ AppServiceProvider criado"
else
    info "ℹ️ AppServiceProvider já existe"
fi

# RouteServiceProvider
if [ ! -f "backend/app/Providers/RouteServiceProvider.php" ]; then
    log "Criando RouteServiceProvider..."
    cat > backend/app/Providers/RouteServiceProvider.php << 'EOF'
<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
EOF
    success "✅ RouteServiceProvider criado"
else
    info "ℹ️ RouteServiceProvider já existe"
fi

# 7. Verificar e criar arquivos de rota
step "🛣️ Verificando arquivos de rota..."

# routes/api.php
if [ ! -f "backend/routes/api.php" ]; then
    log "Criando routes/api.php..."
    cat > backend/routes/api.php << 'EOF'
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
EOF
    success "✅ routes/api.php criado"
else
    info "ℹ️ routes/api.php já existe"
fi

# routes/web.php
if [ ! -f "backend/routes/web.php" ]; then
    log "Criando routes/web.php..."
    cat > backend/routes/web.php << 'EOF'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
EOF
    success "✅ routes/web.php criado"
else
    info "ℹ️ routes/web.php já existe"
fi

# 8. Limpar caches e regenerar autoloader
step "🧹 Limpando caches e regenerando autoloader..."

# Limpar caches do Laravel
backend_exec php artisan config:clear 2>/dev/null || true
backend_exec php artisan cache:clear 2>/dev/null || true
backend_exec php artisan route:clear 2>/dev/null || true
backend_exec php artisan view:clear 2>/dev/null || true

# Regenerar autoloader
backend_exec composer dump-autoload --optimize

success "✅ Caches limpos e autoloader regenerado"

# 9. Testar se o problema foi resolvido
step "🧪 Testando se o problema foi resolvido..."

if backend_exec php artisan --version >/dev/null 2>&1; then
    success "✅ Laravel funcionando corretamente!"
    info "Versão do Laravel: $(backend_exec php artisan --version)"
else
    error "❌ Laravel ainda não está funcionando"
    warn "⚠️ Verifique os logs para mais detalhes"
    exit 1
fi

# 10. Finalização
success "🎉 Problema de namespace corrigido com sucesso!"
echo -e "${GREEN}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    ✅ NAMESPACE FIXED!                    ║
╠═══════════════════════════════════════════════════════════╣
║  🔧 Laravel namespace detectado corretamente             ║
║  📦 Autoloader regenerado                                 ║
║  🧹 Caches limpos                                         ║
║  🛣️ Rotas configuradas                                    ║
╠═══════════════════════════════════════════════════════════╣
║  🚀 Agora você pode executar:                            ║
║  cd backend && php artisan migrate                        ║
║  cd backend && php artisan test                           ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

info "🚀 Laravel está pronto para uso!" 