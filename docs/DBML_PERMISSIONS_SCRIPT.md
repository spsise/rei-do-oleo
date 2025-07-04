# 🔐 Script DBML - Sistema de Permissões Rei do Óleo

## 📋 Como Usar

1. Acesse [dbdiagram.io](https://dbdiagram.io/)
2. Clique em "Create New Diagram"
3. Cole o script abaixo na área de código
4. O diagrama será gerado automaticamente

## 🗄️ Script DBML Completo

```sql
// Sistema de Permissões - Rei do Óleo
// Baseado no Spatie Permission Package
// Framework: Laravel 12 + Spatie Permission

Table users {
  id bigint [pk, increment]
  name varchar(255) [not null]
  email varchar(255) [not null, unique]
  email_verified_at timestamp [null]
  password varchar(255) [not null]
  active boolean [default: true]
  last_login_at timestamp [null]
  service_center_id bigint [null]
  phone varchar(20) [null]
  whatsapp varchar(20) [null]
  document varchar(20) [null]
  birth_date date [null]
  hire_date date [null]
  salary decimal(10,2) [null]
  commission_rate decimal(5,2) [null]
  specialties json [null]
  remember_token varchar(100) [null]
  created_at timestamp [null]
  updated_at timestamp [null]
  deleted_at timestamp [null]

  indexes {
    (phone)
    (document)
    (active)
    (service_center_id)
    (created_at)
    (active, service_center_id)
  }
}

Table service_centers {
  id bigint [pk, increment]
  code varchar(20) [unique, not null]
  name varchar(150) [not null]
  slug varchar(255) [unique, not null]
  cnpj varchar(18) [unique, null]
  state_registration varchar(50) [null]
  legal_name varchar(200) [null]
  trade_name varchar(150) [null]
  address_line varchar(255) [null]
  number varchar(10) [null]
  complement varchar(100) [null]
  neighborhood varchar(100) [null]
  city varchar(100) [null]
  state varchar(2) [null]
  zip_code varchar(10) [null]
  latitude decimal(10,8) [null]
  longitude decimal(11,8) [null]
  phone varchar(20) [null]
  whatsapp varchar(20) [null]
  email varchar(255) [null]
  website varchar(255) [null]
  facebook_url varchar(255) [null]
  instagram_url varchar(255) [null]
  google_maps_url varchar(255) [null]
  manager_id bigint [null]
  technical_responsible varchar(255) [null]
  opening_date date [null]
  operating_hours text [null]
  is_main_branch boolean [default: false]
  active boolean [default: true]
  observations text [null]
  created_at timestamp [null]
  updated_at timestamp [null]
  deleted_at timestamp [null]

  indexes {
    (manager_id)
    (active)
    (state, city)
    (latitude, longitude, active)
    (is_main_branch)
    (name, legal_name, trade_name, city, neighborhood) [type: fulltext]
  }
}

Table permissions {
  id bigint [pk, increment]
  name varchar(255) [not null]
  guard_name varchar(255) [not null]
  created_at timestamp [null]
  updated_at timestamp [null]

  indexes {
    (name, guard_name) [unique]
  }
}

Table roles {
  id bigint [pk, increment]
  name varchar(255) [not null]
  guard_name varchar(255) [not null]
  created_at timestamp [null]
  updated_at timestamp [null]

  indexes {
    (name, guard_name) [unique]
  }
}

Table model_has_permissions {
  permission_id bigint [not null]
  model_type varchar(255) [not null]
  model_id bigint [not null]

  indexes {
    (permission_id, model_id, model_type) [pk]
    (model_id, model_type)
  }
}

Table model_has_roles {
  role_id bigint [not null]
  model_type varchar(255) [not null]
  model_id bigint [not null]

  indexes {
    (role_id, model_id, model_type) [pk]
    (model_id, model_type)
  }
}

Table role_has_permissions {
  permission_id bigint [not null]
  role_id bigint [not null]

  indexes {
    (permission_id, role_id) [pk]
  }
}

Table personal_access_tokens {
  id bigint [pk, increment]
  tokenable_type varchar(255) [not null]
  tokenable_id bigint [not null]
  name varchar(255) [not null]
  token varchar(64) [unique, not null]
  abilities text [null]
  last_used_at timestamp [null]
  expires_at timestamp [null]
  created_at timestamp [null]
  updated_at timestamp [null]

  indexes {
    (tokenable_type, tokenable_id)
  }
}

// Relacionamentos
Ref: users.service_center_id > service_centers.id [delete: set null]
Ref: service_centers.manager_id > users.id [delete: set null]

Ref: model_has_permissions.permission_id > permissions.id [delete: cascade]
Ref: model_has_permissions.model_id > users.id [delete: cascade]

Ref: model_has_roles.role_id > roles.id [delete: cascade]
Ref: model_has_roles.model_id > users.id [delete: cascade]

Ref: role_has_permissions.permission_id > permissions.id [delete: cascade]
Ref: role_has_permissions.role_id > roles.id [delete: cascade]

Ref: personal_access_tokens.tokenable_id > users.id [delete: cascade]
```

## 🎨 Personalização do Diagrama

### Cores e Estilos

Após gerar o diagrama, você pode personalizar:

1. **Cores das tabelas**: Clique na tabela → Settings → Color
2. **Estilo das linhas**: Clique no relacionamento → Settings → Style
3. **Layout**: Arraste as tabelas para reorganizar
4. **Zoom**: Use scroll ou botões de zoom

### Exportação

- **PNG**: Para documentação
- **PDF**: Para impressão
- **SQL**: Para implementação
- **Share**: Para colaboração

## 📊 Características do Script

### 🔐 **Sistema de Permissões**

- **Polymorphic relationships** para flexibilidade
- **Guard-based** para múltiplos contextos
- **Cascade deletes** para integridade
- **Índices otimizados** para performance

### 👥 **Gestão de Usuários**

- **21 campos** completos
- **Soft deletes** implementado
- **Relacionamento** com centros de serviço
- **Campos de RH** (salário, comissão)

### 🏢 **Centros de Serviço**

- **25 campos** empresariais
- **Geolocalização** para mapas
- **Full-text search** implementado
- **Redes sociais** integradas

## 🔧 Comandos Úteis

### Laravel + Spatie Permission

```php
// Criar permissões
php artisan permission:create-permission view-users
php artisan permission:create-permission create-users
php artisan permission:create-permission edit-users
php artisan permission:create-permission delete-users

// Criar papéis
php artisan permission:create-role admin
php artisan permission:create-role manager
php artisan permission:create-role employee

// Atribuir permissões
php artisan permission:give-permission-to admin "view-users,create-users,edit-users,delete-users"
php artisan permission:give-permission-to manager "view-users,create-users,edit-users"
php artisan permission:give-permission-to employee "view-users"
```

### Verificações no Código

```php
// Verificar permissões
if ($user->hasPermissionTo('edit-users')) {
    // Usuário pode editar outros usuários
}

// Verificar papéis
if ($user->hasRole('admin')) {
    // Usuário é administrador
}

// Verificar múltiplas permissões
if ($user->hasAnyPermission(['edit-users', 'delete-users'])) {
    // Usuário tem pelo menos uma das permissões
}
```

---

**🔗 Link para dbdiagram.io**: [https://dbdiagram.io/](https://dbdiagram.io/)  
**📝 Documentação Completa**: [SISTEMA_PERMISSOES.md](./SISTEMA_PERMISSOES.md)  
**📊 Última Atualização**: 25/06/2025
