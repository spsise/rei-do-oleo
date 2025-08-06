# Sistema de Logging Avançado - Rei do Óleo

## 📋 Visão Geral

O sistema de logging do Rei do Óleo foi projetado para fornecer logs estruturados, categorizados e seguros para todas as operações da aplicação. O sistema utiliza múltiplos canais de log para diferentes tipos de eventos e inclui recursos avançados de auditoria e monitoramento.

## 🏗️ Arquitetura do Sistema

### Princípios SOLID e Desacoplamento

O sistema segue os princípios SOLID, especialmente o **Dependency Inversion Principle (DIP)**:

- **Interface Segregation**: `LoggingServiceInterface` define contratos claros
- **Dependency Inversion**: Classes dependem de abstrações, não implementações
- **Open/Closed**: Fácil extensão sem modificar código existente

### Sistema de Fallback

O sistema implementa um **mecanismo de fallback robusto**:

1. **Primary Logger**: Usa a implementação configurada (`LoggingService` ou `FileLoggingService`)
2. **Fallback Logger**: Em caso de erro, automaticamente usa o `Log` facade do Laravel
3. **Error Tracking**: Logs erros do sistema de logging para debug
4. **Graceful Degradation**: Continua funcionando mesmo com falhas

### Implementações Disponíveis

| Implementação        | Descrição                   | Uso Recomendado          |
| -------------------- | --------------------------- | ------------------------ |
| `LoggingService`     | Logs via Laravel Log Facade | Produção                 |
| `FileLoggingService` | Logs em arquivos separados  | Desenvolvimento/Testes   |
| `SafeLoggingService` | Wrapper com fallback        | **Padrão (Recomendado)** |

### Canais de Log Configurados

| Canal         | Propósito                        | Retenção | Formato |
| ------------- | -------------------------------- | -------- | ------- |
| `api`         | Logs de API (requests/responses) | 30 dias  | JSON    |
| `business`    | Operações de negócio             | 90 dias  | JSON    |
| `security`    | Eventos de segurança             | 365 dias | JSON    |
| `performance` | Métricas de performance          | 30 dias  | JSON    |
| `telegram`    | Eventos do bot Telegram          | 30 dias  | JSON    |
| `whatsapp`    | Eventos do WhatsApp              | 30 dias  | JSON    |
| `audit`       | Trilha de auditoria              | 365 dias | JSON    |

### Componentes Principais

1. **LoggingServiceInterface** - Contrato para todas as implementações
2. **LoggingService** - Implementação padrão usando Laravel Log
3. **FileLoggingService** - Implementação alternativa em arquivos
4. **SafeLoggingService** - Wrapper com fallback automático
5. **HasSafeLogging** - Trait para migração gradual
6. **Canais de Log** - Configurações específicas para cada tipo de evento
7. **Middleware** - Logging automático de requests/responses
8. **Traits** - Logging automático de modelos
9. **Comandos Artisan** - Gerenciamento de logs

## 🔄 Configuração de Implementações

### Configuração Atual do Projeto

**IMPORTANTE**: O `LoggingServiceProvider` **não está registrado** no `config/app.php`, então as configurações customizadas não estão ativas.

### Configuração Padrão (Atual)

O sistema está usando as configurações padrão do Laravel:

```env
# .env
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```

### Para Ativar Configurações Customizadas

#### Opção 1: Registrar o LoggingServiceProvider

1. **Adicionar no `config/app.php`**:

```php
'providers' => [
    // ... outros providers
    App\Providers\LoggingServiceProvider::class,
],
```

2. **Adicionar no `.env`**:

```env
LOGGING_DRIVER=laravel  # ou 'file'
```

#### Opção 2: Usar Configuração Padrão do Laravel

```env
# .env
LOG_CHANNEL=database
```

E configurar o canal `database` no `config/logging.php`:

```php
'database' => [
    'driver' => 'monolog',
    'level' => 'debug',
    'handler' => \Monolog\Handler\DoctrineHandler::class,
    'handler_with' => [
        'connection' => 'mysql',
        'table' => 'logs',
    ],
],
```

### Via Service Provider (Quando Registrado)

```php
// app/Providers/LoggingServiceProvider.php
$this->app->bind(LoggingServiceInterface::class, function ($app) {
    $loggingDriver = config('logging.driver', 'laravel');

    // Create the primary logger based on configuration
    $primaryLogger = match ($loggingDriver) {
        'file' => new FileLoggingService(),
        'laravel' => new LoggingService(),
        default => new LoggingService(),
    };

    // Wrap with SafeLoggingService for fallback protection
    return new SafeLoggingService($primaryLogger);
});
```

### Injeção de Dependência

```php
class UserController extends Controller
{
    public function __construct(
        private LoggingServiceInterface $loggingService // Interface, não implementação
    ) {}
}
```

## 🗄️ Configuração do Activity Log para Banco de Dados

### Opção 1: Usar Spatie Activity Log (Recomendado)

#### Configuração no .env:

```env
# Activity Log Configuration
ACTIVITY_LOGGER_ENABLED=true
ACTIVITY_LOGGER_DB_CONNECTION=mysql
ACTIVITY_LOGGER_TABLE=activity_log
ACTIVITY_LOGGER_CLEAN_RECORDS_OLDER_THAN_DAYS=365
```

#### Passos para Ativar:

```bash
# 1. Publicar configurações
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"

# 2. Executar migrations
php artisan migrate

# 3. Verificar se a tabela foi criada
php artisan tinker
>>> Schema::hasTable('activity_log')
```

#### Onde os Logs Serão Salvos:

- **Tabela**: `activity_log` no banco de dados MySQL
- **Campos**: `log_name`, `description`, `subject_type`, `subject_id`, `causer_type`, `causer_id`, `properties`, `created_at`
- **Retenção**: 365 dias (configurável)

### Opção 2: Configurar Canal Database do Laravel

#### Adicionar no config/logging.php:

```php
'channels' => [
    // ... outros canais

    'database' => [
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => \Monolog\Handler\DoctrineHandler::class,
        'handler_with' => [
            'connection' => 'mysql',
            'table' => 'logs',
        ],
    ],
],
```

#### Configurar no .env:

```env
LOG_CHANNEL=database
```

### Opção 3: Usar Ambos (Arquivos + Banco)

```env
LOG_CHANNEL=stack
LOG_STACK=single,database
```

## 🚀 Como Usar

### 1. Logging de API

```php
use App\Contracts\LoggingServiceInterface;

class UserController extends Controller
{
    public function __construct(
        private LoggingServiceInterface $loggingService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // Log da requisição
            $this->loggingService->logApiRequest($request, [
                'operation' => 'user_creation'
            ]);

            // Lógica de negócio
            $user = User::create($request->validated());

            $duration = (microtime(true) - $startTime) * 1000;

            // Log da resposta
            $response = response()->json($user, 201);
            $this->loggingService->logApiResponse(
                $response->getStatusCode(),
                $user->toArray(),
                $duration
            );

            return $response;

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'user_creation'
            ]);
            throw $e;
        }
    }
}
```

### 2. Migração Gradual com Trait

Para serviços existentes que ainda usam `Log::`, use o trait `HasSafeLogging`:

```php
use App\Traits\HasSafeLogging;

class WhatsAppService
{
    use HasSafeLogging;

    public function sendMessage(string $to, string $message): array
    {
        try {
            // Lógica de envio...

            $this->logWhatsAppEvent('message_sent', [
                'phone' => $to,
                'message_type' => 'text'
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            $this->logException($e, ['phone' => $to]);
            return ['success' => false];
        }
    }
}
```

### 3. Logging de Operações de Negócio

```php
// Log de operação de negócio
$this->loggingService->logBusinessOperation(
    'order_processed',
    [
        'order_id' => $order->id,
        'total_amount' => $order->total,
        'payment_method' => $order->payment_method
    ],
    'success'
);

// Log de operação com falha
$this->loggingService->logBusinessOperation(
    'payment_processing',
    [
        'order_id' => $order->id,
        'error' => $exception->getMessage()
    ],
    'failed'
);
```

### 4. Logging de Segurança

```php
// Log de tentativa de login
$this->loggingService->logSecurityEvent(
    'login_attempt',
    [
        'email' => $request->email,
        'success' => $success
    ],
    $success ? 'info' : 'warning'
);

// Log de acesso a recurso sensível
$this->loggingService->logSecurityEvent(
    'sensitive_resource_accessed',
    [
        'resource' => 'admin_panel',
        'user_id' => $user->id,
        'permissions' => $user->getAllPermissions()->pluck('name')
    ],
    'info'
);
```

### 5. Logging de Performance

```php
$startTime = microtime(true);

// Operação que queremos medir
$result = $this->heavyOperation();

$duration = (microtime(true) - $startTime) * 1000;

$this->loggingService->logPerformance(
    'heavy_operation',
    $duration,
    [
        'result_size' => strlen(json_encode($result)),
        'memory_peak' => memory_get_peak_usage(true)
    ]
);
```

### 6. Logging de Auditoria

```php
// Log automático via trait
class User extends Model
{
    use LogsActivity;

    // Logs automáticos de created, updated, deleted
}

// Log manual
$this->loggingService->logAudit(
    'user_role_changed',
    'User',
    $user->id,
    [
        'old_role' => $oldRole,
        'new_role' => $newRole,
        'changed_by' => Auth::id()
    ]
);
```

### 7. Logging Específico de Integrações

```php
// Telegram
$this->loggingService->logTelegramEvent(
    'message_received',
    [
        'chat_id' => $chatId,
        'message_type' => 'text',
        'text' => $messageText
    ]
);

// WhatsApp
$this->loggingService->logWhatsAppEvent(
    'message_sent',
    [
        'phone' => $phone,
        'message_type' => 'template',
        'template_name' => 'welcome'
    ]
);
```

## 🔧 Migração Gradual

### Passo 1: Usar Trait em Serviços Existentes

```php
// Antes
class WhatsAppService
{
    public function sendMessage($to, $message)
    {
        try {
            // lógica...
            Log::info('Message sent', ['to' => $to]);
        } catch (\Exception $e) {
            Log::error('Error sending message', ['error' => $e->getMessage()]);
        }
    }
}

// Depois
class WhatsAppService
{
    use HasSafeLogging;

    public function sendMessage($to, $message)
    {
        try {
            // lógica...
            $this->logWhatsAppEvent('message_sent', ['phone' => $to]);
        } catch (\Exception $e) {
            $this->logException($e, ['phone' => $to]);
        }
    }
}
```

### Passo 2: Migrar Controllers

```php
// Antes
class UserController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Creating user', $request->all());
        // lógica...
    }
}

// Depois
class UserController extends Controller
{
    public function __construct(
        private LoggingServiceInterface $loggingService
    ) {}

    public function store(Request $request)
    {
        $this->loggingService->logApiRequest($request, ['operation' => 'user_creation']);
        // lógica...
    }
}
```

### Passo 3: Configurar Fallback

```php
// config/logging.php
return [
    'driver' => env('LOGGING_DRIVER', 'laravel'),
    'fallback_enabled' => env('LOGGING_FALLBACK_ENABLED', true),
];
```

## 🛠️ Comandos de Gerenciamento

### Estatísticas de Logs

```bash
# Ver estatísticas de todos os canais
php artisan logs:manage stats

# Ver estatísticas de canais específicos
php artisan logs:manage stats --channel=api --channel=security
```

### Limpeza de Logs

```bash
# Limpar logs mais antigos que 30 dias
php artisan logs:manage clean --days=30

# Limpar logs de canais específicos
php artisan logs:manage clean --channel=api --days=7

# Simular limpeza (dry-run)
php artisan logs:manage clean --days=30 --dry-run
```

### Rotação de Logs

```bash
# Rotacionar logs grandes (>10MB)
php artisan logs:manage rotate

# Rotacionar logs específicos
php artisan logs:manage rotate --channel=api --channel=business
```

### Análise de Logs

```bash
# Analisar todos os logs
php artisan logs:manage analyze

# Analisar logs específicos
php artisan logs:manage analyze --channel=security --channel=performance
```

## 📊 Monitoramento e Alertas

### Configuração de Alertas

```php
// Log de operação lenta
if ($duration > 5000) { // 5 segundos
    $this->loggingService->logPerformance(
        'slow_operation',
        $duration,
        ['threshold' => 5000],
        ['alert' => true]
    );
}

// Log de erro crítico
$this->loggingService->logException($exception, [
    'critical' => true,
    'alert_channels' => ['slack', 'email']
]);
```

### Integração com Ferramentas Externas

#### Slack

```env
LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
LOG_SLACK_USERNAME="Rei do Óleo Logs"
LOG_SLACK_EMOJI=":warning:"
```

#### Papertrail

```env
PAPERTRAIL_URL=logs.papertrailapp.com
PAPERTRAIL_PORT=12345
```

## 🔒 Segurança e Privacidade

### Sanitização Automática

O sistema automaticamente remove dados sensíveis dos logs:

- **Headers**: `authorization`, `cookie`, `x-csrf-token`, `x-api-key`
- **Body**: `password`, `password_confirmation`, `token`, `api_key`, `secret`
- **Telegram**: `token`, `webhook_secret`
- **WhatsApp**: `token`, `webhook_secret`

### Logs de Segurança

Todos os eventos de segurança são logados com:

- IP do usuário
- User Agent
- Timestamp
- Contexto da operação
- Dados sanitizados

## 📈 Performance e Otimização

### Configurações Recomendadas

```env
# Níveis de log por ambiente
LOG_LEVEL=debug          # Development
LOG_LEVEL=info           # Staging
LOG_LEVEL=warning        # Production

# Retenção de logs
LOG_DAILY_DAYS=30        # Logs diários
LOG_DAILY_DAYS=7         # Logs de API
LOG_DAILY_DAYS=365       # Logs de segurança

# Fallback
LOGGING_FALLBACK_ENABLED=true
```

### Monitoramento de Performance

```php
// Middleware automático de performance
// Adiciona headers: X-Response-Time, X-Memory-Usage
// Loga operações lentas automaticamente
```

## 🔧 Configuração Avançada

### Canais Customizados

```php
// config/logging.php
'custom_channel' => [
    'driver' => 'daily',
    'path' => storage_path('logs/custom.log'),
    'level' => 'info',
    'days' => 30,
    'formatter' => JsonFormatter::class,
],
```

### Formatters Customizados

```php
use Monolog\Formatter\JsonFormatter;

class CustomJsonFormatter extends JsonFormatter
{
    public function format(array $record): string
    {
        $record['extra']['environment'] = app()->environment();
        $record['extra']['version'] = config('app.version');

        return parent::format($record);
    }
}
```

### Criando Nova Implementação

```php
class DatabaseLoggingService implements LoggingServiceInterface
{
    public function logApiRequest(Request $request, array $context = []): void
    {
        // Implementação para salvar logs no banco de dados
        LogEntry::create([
            'type' => 'api_request',
            'data' => $context,
            'user_id' => $request->user()?->id,
        ]);
    }

    // Implementar outros métodos...
}
```

## 📋 Checklist de Implementação

- [ ] Instalar dependências: `composer require spatie/laravel-activitylog`
- [ ] Publicar configurações: `php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"`
- [ ] Executar migrations: `php artisan migrate`
- [ ] Configurar canais de log no `config/logging.php`
- [ ] **OPCIONAL**: Registrar `LoggingServiceProvider` no `config/app.php`
- [ ] Implementar `LoggingServiceInterface` nos controllers
- [ ] Adicionar trait `LogsActivity` nos models
- [ ] Usar trait `HasSafeLogging` em serviços existentes
- [ ] Configurar middleware de performance
- [ ] Testar comandos de gerenciamento
- [ ] Configurar alertas e integrações
- [ ] Documentar padrões de uso para a equipe

## 🚨 Troubleshooting

### Problemas Comuns

1. **Logs não aparecem**

   - Verificar permissões da pasta `storage/logs`
   - Verificar configuração do canal no `config/logging.php`

2. **Performance degradada**

   - Verificar se logs estão sendo rotacionados
   - Ajustar níveis de log para produção
   - Usar `--dry-run` para testar comandos

3. **Logs muito grandes**

   - Executar `php artisan logs:manage clean`
   - Configurar rotação automática
   - Ajustar retenção de logs

### Comandos Úteis

```bash
# Verificar tamanho dos logs
du -sh storage/logs/*

# Ver últimas linhas de um log
tail -f storage/logs/api.log

# Buscar por erro específico
grep "ERROR" storage/logs/api.log

# Analisar performance
php artisan logs:manage analyze --channel=performance
```

## 🎯 Benefícios do Desacoplamento

### 1. **Flexibilidade**

- Trocar implementação sem modificar código cliente
- Testar com implementações mock
- Adaptar para diferentes ambientes

### 2. **Testabilidade**

```php
// Test com mock
$mockLogger = Mockery::mock(LoggingServiceInterface::class);
$mockLogger->shouldReceive('logApiRequest')->once();

$controller = new UserController($mockLogger);
```

### 3. **Extensibilidade**

- Adicionar novas implementações facilmente
- Implementar logging para diferentes backends
- A/B testing de estratégias de logging

### 4. **Manutenibilidade**

- Mudanças isoladas em implementações
- Código mais limpo e organizado
- Facilita refatoração futura

## 🛡️ Sistema de Fallback

### Como Funciona

1. **Tentativa Primária**: Usa a implementação configurada
2. **Detecção de Erro**: Captura exceções do sistema de logging
3. **Fallback Automático**: Usa `Log` facade do Laravel
4. **Log de Erro**: Registra o problema para debug
5. **Continuidade**: Mantém funcionamento mesmo com falhas

### Benefícios

- **Resiliência**: Sistema nunca para de funcionar
- **Debugging**: Logs de erro do próprio sistema
- **Migração Segura**: Transição gradual sem riscos
- **Monitoramento**: Visibilidade de problemas

### Configuração

```env
# Habilitar/desabilitar fallback
LOGGING_FALLBACK_ENABLED=true

# Configurar driver principal (quando LoggingServiceProvider estiver registrado)
LOGGING_DRIVER=laravel
```

## 📊 Status Atual do Projeto

### ✅ **Implementado:**

- Sistema de logging estruturado com interfaces
- Canais de log especializados
- Sistema de fallback robusto
- Traits para migração gradual
- Comandos de gerenciamento

### ⚠️ **Pendente:**

- `LoggingServiceProvider` não está registrado no `config/app.php`
- Variável `LOGGING_DRIVER` não está sendo utilizada
- Configurações customizadas não estão ativas

### 🔧 **Para Ativar Configurações Customizadas:**

1. Registrar `LoggingServiceProvider` no `config/app.php`
2. Adicionar `LOGGING_DRIVER` no `.env`
3. Ou usar configurações padrão do Laravel com canais customizados
