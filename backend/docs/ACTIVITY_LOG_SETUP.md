# Configuração do Activity Log - Banco de Dados

## 📋 Visão Geral

Este documento explica como configurar o Spatie Laravel Activity Log para salvar logs no banco de dados do projeto Rei do Óleo.

## 🗄️ Configuração do Activity Log

### Pré-requisitos

O pacote `spatie/laravel-activitylog` já está incluído no `composer.json`:

```json
{
  "require": {
    "spatie/laravel-activitylog": "^4.10"
  }
}
```

### Passo 1: Publicar Configurações

```bash
# Publicar arquivo de configuração
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
```

Isso criará o arquivo `config/activitylog.php` com as configurações padrão.

### Passo 2: Executar Migrations

```bash
# Executar migrations para criar a tabela activity_log
php artisan migrate
```

### Passo 3: Verificar Instalação

```bash
# Verificar se a tabela foi criada
php artisan tinker
>>> Schema::hasTable('activity_log')
# Deve retornar: true
```

## ⚙️ Configuração no .env

### Configuração Básica

Adicione as seguintes variáveis no seu arquivo `.env`:

```env
# Activity Log Configuration
ACTIVITY_LOGGER_ENABLED=true
ACTIVITY_LOGGER_DB_CONNECTION=mysql
ACTIVITY_LOGGER_TABLE=activity_log
ACTIVITY_LOGGER_CLEAN_RECORDS_OLDER_THAN_DAYS=365
```

### Configuração Completa

```env
# Database Configuration (já existente)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=DB_DATABASE_NAME
DB_USERNAME=DB_USERNAME_VALUE
DB_PASSWORD=DB_PASSWORD_VALUE

# Activity Log Configuration
ACTIVITY_LOGGER_ENABLED=true
ACTIVITY_LOGGER_DB_CONNECTION=mysql
ACTIVITY_LOGGER_TABLE=activity_log
ACTIVITY_LOGGER_CLEAN_RECORDS_OLDER_THAN_DAYS=365

# Logging Configuration (padrão atual)
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```

## 📊 Estrutura da Tabela

### Tabela: `activity_log`

| Campo          | Tipo         | Descrição                           |
| -------------- | ------------ | ----------------------------------- |
| `id`           | bigint       | ID único do log                     |
| `log_name`     | varchar(255) | Nome do log (ex: 'default')         |
| `description`  | text         | Descrição da atividade              |
| `subject_type` | varchar(255) | Classe do modelo afetado            |
| `subject_id`   | bigint       | ID do modelo afetado                |
| `causer_type`  | varchar(255) | Classe do usuário que causou a ação |
| `causer_id`    | bigint       | ID do usuário que causou a ação     |
| `properties`   | json         | Dados adicionais da atividade       |
| `created_at`   | timestamp    | Data/hora da criação                |
| `updated_at`   | timestamp    | Data/hora da última atualização     |

### Exemplo de Registro

```json
{
  "id": 1,
  "log_name": "default",
  "description": "User created",
  "subject_type": "App\\Models\\User",
  "subject_id": 123,
  "causer_type": "App\\Models\\User",
  "causer_id": 1,
  "properties": {
    "attributes": {
      "name": "João Silva",
      "email": "joao@example.com"
    }
  },
  "created_at": "2024-01-15 10:30:00"
}
```

## 🚀 Como Usar

### 1. Log Automático em Models

```php
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

### 2. Log Manual

```php
use Spatie\Activitylog\ActivityLogger;

// Log simples
activity()
    ->log('User logged in');

// Log com contexto
activity()
    ->causedBy($user)
    ->performedOn($order)
    ->withProperties(['ip' => $request->ip()])
    ->log('Order created');

// Log com dados customizados
activity()
    ->causedBy($user)
    ->performedOn($product)
    ->withProperties([
        'old_values' => $oldValues,
        'new_values' => $newValues
    ])
    ->log('Product updated');
```

### 3. Consultar Logs

```php
// Buscar logs de um usuário
$userLogs = Activity::causedBy($user)->get();

// Buscar logs de um modelo
$orderLogs = Activity::performedOn($order)->get();

// Buscar logs por tipo
$loginLogs = Activity::inLog('login')->get();

// Buscar logs recentes
$recentLogs = Activity::latest()->take(10)->get();
```

## 🔧 Configuração Avançada

### Configuração do activitylog.php

```php
<?php

return [
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,
    'table_name' => 'activity_log',
    'database_connection' => env('ACTIVITY_LOGGER_DB_CONNECTION'),
    'default_log_name' => 'default',
    'default_auth_driver' => null,
    'subject_types' => [
        // Adicionar classes de modelos aqui
        \App\Models\User::class,
        \App\Models\Order::class,
        \App\Models\Product::class,
    ],
    'causer_types' => [
        // Adicionar classes de usuários aqui
        \App\Models\User::class,
    ],
    'enable_logging_models_events' => true,
    'activity_model_appends' => [
        'properties',
        'causer',
        'subject',
    ],
    'recording_model_events' => [
        'created',
        'updated',
        'deleted',
    ],
    'clean_records_older_than_days' => 365,
];
```

### Configurar Models Específicos

```php
class Order extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_amount', 'payment_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('orders');
    }
}
```

## 🛠️ Comandos Úteis

### Limpeza de Logs Antigos

```bash
# Limpar logs mais antigos que 365 dias
php artisan activitylog:clean

# Limpar logs mais antigos que 30 dias
php artisan activitylog:clean --days=30

# Simular limpeza (dry-run)
php artisan activitylog:clean --dry-run
```

### Estatísticas

```bash
# Ver estatísticas dos logs
php artisan tinker
>>> \Spatie\Activitylog\Models\Activity::count()
>>> \Spatie\Activitylog\Models\Activity::groupBy('log_name')->count()
```

## 📈 Performance e Otimização

### Índices Recomendados

```sql
-- Índices para melhorar performance
CREATE INDEX idx_activity_log_subject ON activity_log(subject_type, subject_id);
CREATE INDEX idx_activity_log_causer ON activity_log(causer_type, causer_id);
CREATE INDEX idx_activity_log_created_at ON activity_log(created_at);
CREATE INDEX idx_activity_log_name ON activity_log(log_name);
```

### Configurações de Performance

```php
// config/activitylog.php
'clean_records_older_than_days' => 90, // Reduzir retenção
'enable_logging_models_events' => false, // Desabilitar logs automáticos se não necessário
```

## 🔒 Segurança

### Sanitização de Dados

```php
// Configurar campos sensíveis que não devem ser logados
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'email'])
        ->dontLogIfAttributesChangedOnly(['updated_at'])
        ->logOnlyDirty();
}
```

### Controle de Acesso

```php
// Verificar se usuário tem permissão para ver logs
if (auth()->user()->can('view-activity-logs')) {
    $logs = Activity::latest()->paginate(20);
}
```

## 🚨 Troubleshooting

### Problemas Comuns

1. **Tabela não criada**

   ```bash
   php artisan migrate:status
   php artisan migrate
   ```

2. **Logs não aparecem**

   ```bash
   # Verificar se o trait está sendo usado
   php artisan tinker
   >>> $user = new \App\Models\User();
   >>> method_exists($user, 'getActivitylogOptions')
   ```

3. **Performance lenta**
   ```bash
   # Verificar índices
   php artisan tinker
   >>> \DB::select('SHOW INDEX FROM activity_log');
   ```

### Comandos de Debug

```bash
# Verificar configuração
php artisan config:show activitylog

# Verificar se o provider está carregado
php artisan config:show app.providers | grep Activitylog

# Testar logging
php artisan tinker
>>> activity()->log('Test log');
>>> \Spatie\Activitylog\Models\Activity::latest()->first();
```

## 📋 Checklist de Implementação

- [ ] Instalar dependências: `composer require spatie/laravel-activitylog`
- [ ] Publicar configurações: `php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"`
- [ ] Executar migrations: `php artisan migrate`
- [ ] Configurar variáveis no `.env`
- [ ] Adicionar trait `LogsActivity` nos models desejados
- [ ] Configurar `getActivitylogOptions()` nos models
- [ ] Testar logging automático
- [ ] Implementar logging manual onde necessário
- [ ] Configurar índices no banco de dados
- [ ] Configurar limpeza automática
- [ ] Testar performance
- [ ] Documentar padrões de uso

## 🎯 Benefícios

### 1. **Auditoria Completa**

- Rastreamento de todas as mudanças
- Histórico completo de ações
- Conformidade com regulamentações

### 2. **Debugging Avançado**

- Identificação de problemas
- Análise de comportamento do usuário
- Investigação de incidentes

### 3. **Análise de Dados**

- Relatórios de atividade
- Métricas de uso
- Insights de negócio

### 4. **Segurança**

- Detecção de atividades suspeitas
- Monitoramento de acesso
- Investigação de violações
