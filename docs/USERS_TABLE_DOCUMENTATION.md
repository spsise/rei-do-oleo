# 📋 Documentação da Tabela Users

## 📖 Visão Geral

A tabela `users` é a tabela principal do sistema **Rei do Óleo** responsável por armazenar todas as informações dos usuários do sistema, incluindo funcionários, administradores e outros colaboradores. Esta tabela integra-se com o sistema de permissões do Laravel (Spatie Permission) e possui relacionamentos com outras entidades do sistema.

## 🏗️ Estrutura da Tabela

### 📊 Campos Principais

| Campo               | Tipo        | Tamanho | Nullable | Default             | Descrição                         |
| ------------------- | ----------- | ------- | -------- | ------------------- | --------------------------------- |
| `id`                | `bigint`    | -       | ❌       | `AUTO_INCREMENT`    | Chave primária única              |
| `name`              | `varchar`   | 255     | ❌       | -                   | Nome completo do usuário          |
| `email`             | `varchar`   | 255     | ❌       | -                   | Email único do usuário            |
| `email_verified_at` | `timestamp` | -       | ✅       | `NULL`              | Data de verificação do email      |
| `password`          | `varchar`   | 255     | ❌       | -                   | Senha criptografada               |
| `active`            | `boolean`   | -       | ❌       | `true`              | Status de ativação do usuário     |
| `last_login_at`     | `timestamp` | -       | ✅       | `NULL`              | Último login do usuário           |
| `service_center_id` | `bigint`    | -       | ✅       | `NULL`              | ID do centro de serviço vinculado |
| `phone`             | `varchar`   | 20      | ✅       | `NULL`              | Número de telefone                |
| `whatsapp`          | `varchar`   | 20      | ✅       | `NULL`              | Número do WhatsApp                |
| `document`          | `varchar`   | 20      | ✅       | `NULL`              | CPF/CNPJ do usuário               |
| `birth_date`        | `date`      | -       | ✅       | `NULL`              | Data de nascimento                |
| `hire_date`         | `date`      | -       | ✅       | `NULL`              | Data de contratação               |
| `salary`            | `decimal`   | 10,2    | ✅       | `NULL`              | Salário do funcionário            |
| `commission_rate`   | `decimal`   | 5,2     | ✅       | `NULL`              | Taxa de comissão (%)              |
| `specialties`       | `json`      | -       | ✅       | `NULL`              | Especialidades do usuário         |
| `remember_token`    | `varchar`   | 100     | ✅       | `NULL`              | Token para "lembrar-me"           |
| `created_at`        | `timestamp` | -       | ❌       | `CURRENT_TIMESTAMP` | Data de criação                   |
| `updated_at`        | `timestamp` | -       | ❌       | `CURRENT_TIMESTAMP` | Data de atualização               |
| `deleted_at`        | `timestamp` | -       | ✅       | `NULL`              | Data de exclusão (Soft Delete)    |

## 🔗 Relacionamentos

### Relacionamentos Diretos

```php
// Relacionamento com Service Center
public function serviceCenter()
{
    return $this->belongsTo(ServiceCenter::class);
}

// Relacionamentos de Permissões (Spatie Permission)
public function roles()
{
    return $this->morphToMany(Role::class, 'model', 'model_has_roles');
}

public function permissions()
{
    return $this->morphToMany(Permission::class, 'model', 'model_has_permissions');
}
```

### Tabelas Relacionadas

| Tabela                  | Tipo de Relacionamento | Descrição                                 |
| ----------------------- | ---------------------- | ----------------------------------------- |
| `service_centers`       | `belongsTo`            | Centro de serviço onde o usuário trabalha |
| `roles`                 | `morphToMany`          | Papéis/perfis do usuário                  |
| `permissions`           | `morphToMany`          | Permissões específicas do usuário         |
| `sessions`              | `hasMany`              | Sessões ativas do usuário                 |
| `password_reset_tokens` | `hasMany`              | Tokens de reset de senha                  |

## 🗂️ Índices e Performance

### Índices Criados

```sql
-- Índices para otimização de consultas
CREATE INDEX users_phone_index ON users(phone);
CREATE INDEX users_document_index ON users(document);
CREATE INDEX users_active_index ON users(active);
CREATE INDEX users_service_center_id_index ON users(service_center_id);
CREATE INDEX users_created_at_index ON users(created_at);
CREATE INDEX users_active_service_center_id_index ON users(active, service_center_id);

-- Índices únicos
CREATE UNIQUE INDEX users_email_unique ON users(email);
```

### Otimizações de Performance

- **Índice composto** em `(active, service_center_id)` para consultas de usuários ativos por centro
- **Índice simples** em `phone` e `document` para buscas rápidas
- **Índice temporal** em `created_at` para relatórios e análises

## 🔐 Sistema de Autenticação

### Campos de Autenticação

- **`email`**: Identificador único para login
- **`password`**: Senha criptografada com bcrypt
- **`email_verified_at`**: Controle de verificação de email
- **`remember_token`**: Token para sessões persistentes
- **`last_login_at`**: Rastreamento de atividade

### Segurança

```php
// Exemplo de validação de senha
'password' => [
    'required',
    'min:8',
    'confirmed',
    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
]
```

## 👥 Sistema de Permissões

### Integração com Spatie Permission

A tabela `users` integra-se com o sistema de permissões através das tabelas:

- `permissions`: Permissões disponíveis no sistema
- `roles`: Papéis/perfis de usuário
- `model_has_permissions`: Relacionamento many-to-many entre usuários e permissões
- `model_has_roles`: Relacionamento many-to-many entre usuários e papéis
- `role_has_permissions`: Relacionamento entre papéis e permissões

### Exemplo de Uso

```php
// Verificar permissões
$user->hasPermissionTo('edit-users');
$user->hasRole('admin');

// Atribuir papéis
$user->assignRole('manager');

// Atribuir permissões diretas
$user->givePermissionTo('delete-users');
```

## 💼 Campos de Recursos Humanos

### Informações Pessoais

- **`name`**: Nome completo
- **`document`**: CPF/CNPJ
- **`birth_date`**: Data de nascimento
- **`phone`**: Telefone principal
- **`whatsapp`**: WhatsApp para comunicação

### Informações Profissionais

- **`hire_date`**: Data de contratação
- **`salary`**: Salário base (decimal 10,2)
- **`commission_rate`**: Taxa de comissão (decimal 5,2)
- **`specialties`**: Especialidades em formato JSON
- **`service_center_id`**: Centro de serviço vinculado

### Exemplo de Especialidades (JSON)

```json
{
  "specialties": [
    "Troca de Óleo",
    "Alinhamento",
    "Balanceamento",
    "Suspensão",
    "Freios"
  ]
}
```

## 🔄 Soft Deletes

A tabela implementa **Soft Deletes** através do campo `deleted_at`:

```php
// No modelo User
use SoftDeletes;

// Exemplo de uso
$user->delete(); // Marca como deletado
User::withTrashed()->find($id); // Inclui registros deletados
User::onlyTrashed()->get(); // Apenas registros deletados
```

## 📊 Migrações Relacionadas

### Ordem de Execução

1. `0001_01_01_000000_create_users_table.php` - Criação inicial
2. `2025_06_25_012401_create_permission_tables.php` - Sistema de permissões
3. `2025_06_25_012511_add_fields_to_users_table.php` - Campos de controle
4. `2025_06_25_add_extra_fields_to_users_table.php` - Campos de RH

### Tabelas Auxiliares

- **`password_reset_tokens`**: Tokens para reset de senha
- **`sessions`**: Sessões ativas dos usuários
- **`permissions`**: Permissões do sistema
- **`roles`**: Papéis/perfis
- **`model_has_permissions`**: Relacionamento usuário-permissão
- **`model_has_roles`**: Relacionamento usuário-papel
- **`role_has_permissions`**: Relacionamento papel-permissão

## 🎯 Casos de Uso

### Consultas Comuns

```php
// Usuários ativos por centro de serviço
User::where('active', true)
    ->where('service_center_id', $centerId)
    ->get();

// Funcionários com comissão
User::whereNotNull('commission_rate')
    ->where('active', true)
    ->get();

// Usuários por especialidade
User::whereJsonContains('specialties', 'Troca de Óleo')
    ->get();

// Relatório de atividade
User::whereNotNull('last_login_at')
    ->where('last_login_at', '>=', now()->subDays(30))
    ->get();
```

### Validações

```php
// Request de criação/atualização
class UserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user?->id,
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'document' => 'nullable|string|max:20|unique:users,document,' . $this->user?->id,
            'birth_date' => 'nullable|date|before:today',
            'hire_date' => 'nullable|date|before_or_equal:today',
            'salary' => 'nullable|numeric|min:0|max:99999999.99',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'specialties' => 'nullable|array',
            'service_center_id' => 'nullable|exists:service_centers,id',
        ];
    }
}
```

## 🔧 Manutenção e Monitoramento

### Limpeza de Dados

```sql
-- Usuários inativos há mais de 1 ano
SELECT * FROM users
WHERE active = false
AND updated_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Sessões expiradas
DELETE FROM sessions
WHERE last_activity < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR));
```

### Backup e Recuperação

```bash
# Backup específico da tabela users
mysqldump -u username -p database_name users > users_backup.sql

# Restauração
mysql -u username -p database_name < users_backup.sql
```

## 📈 Métricas e Relatórios

### KPIs Importantes

- **Total de usuários ativos**: `User::where('active', true)->count()`
- **Usuários por centro**: `User::groupBy('service_center_id')->count()`
- **Taxa de atividade**: Usuários com login nos últimos 30 dias
- **Distribuição de especialidades**: Análise do campo `specialties`

### Relatórios Sugeridos

1. **Relatório de Funcionários**: Lista completa com dados de RH
2. **Análise de Atividade**: Usuários ativos vs inativos
3. **Distribuição por Centro**: Usuários por centro de serviço
4. **Análise de Comissões**: Funcionários com comissão vs salário fixo

## ⚠️ Considerações Importantes

### Segurança

- Senhas sempre criptografadas com bcrypt
- Validação de email única
- Controle de acesso via sistema de permissões
- Soft deletes para preservar histórico

### Performance

- Índices otimizados para consultas frequentes
- Campos JSON para flexibilidade (especialidades)
- Relacionamentos lazy loading quando necessário

### Integridade

- Foreign key constraints para `service_center_id`
- Validações de dados no nível da aplicação
- Controle de versão através de timestamps

---

**📝 Última Atualização**: 25/06/2025  
**🔧 Versão**: 1.0  
**👨‍💻 Responsável**: Equipe de Desenvolvimento Rei do Óleo
