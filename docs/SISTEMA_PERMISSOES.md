# Sistema: Permissões - Rei do Óleo

## Resumo

Este sistema de permissões consiste nas tabelas responsáveis por determinar as permissões que um usuário terá no sistema, utilizando o pacote **Spatie Permission** do Laravel. O sistema implementa um controle granular de acesso baseado em **roles** (papéis) e **permissions** (permissões) específicas.

## Diagrama

[![Diagrama do Sistema de Permissões](https://dbdiagram.io/d/rei-do-oleo-permissions)](https://dbdiagram.io/d/rei-do-oleo-permissions)

## SQL Script

```sql
-- Tabela de Permissões
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Papéis/Roles
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Usuários (tabela principal)
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `service_center_id` bigint(20) unsigned NULL DEFAULT NULL,
  `phone` varchar(20) NULL DEFAULT NULL,
  `whatsapp` varchar(20) NULL DEFAULT NULL,
  `document` varchar(20) NULL DEFAULT NULL,
  `birth_date` date NULL DEFAULT NULL,
  `hire_date` date NULL DEFAULT NULL,
  `salary` decimal(10,2) NULL DEFAULT NULL,
  `commission_rate` decimal(5,2) NULL DEFAULT NULL,
  `specialties` json NULL DEFAULT NULL,
  `remember_token` varchar(100) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_phone_index` (`phone`),
  KEY `users_document_index` (`document`),
  KEY `users_active_index` (`active`),
  KEY `users_service_center_id_index` (`service_center_id`),
  KEY `users_created_at_index` (`created_at`),
  KEY `users_active_service_center_id_index` (`active`,`service_center_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Relacionamento Usuário-Permissão
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Relacionamento Usuário-Papel
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Relacionamento Papel-Permissão
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Tokens de Acesso Pessoal
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Centros de Serviço (relacionamento)
CREATE TABLE `service_centers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `cnpj` varchar(18) NULL DEFAULT NULL,
  `state_registration` varchar(50) NULL DEFAULT NULL,
  `legal_name` varchar(200) NULL DEFAULT NULL,
  `trade_name` varchar(150) NULL DEFAULT NULL,
  `address_line` varchar(255) NULL DEFAULT NULL,
  `number` varchar(10) NULL DEFAULT NULL,
  `complement` varchar(100) NULL DEFAULT NULL,
  `neighborhood` varchar(100) NULL DEFAULT NULL,
  `city` varchar(100) NULL DEFAULT NULL,
  `state` varchar(2) NULL DEFAULT NULL,
  `zip_code` varchar(10) NULL DEFAULT NULL,
  `latitude` decimal(10,8) NULL DEFAULT NULL,
  `longitude` decimal(11,8) NULL DEFAULT NULL,
  `phone` varchar(20) NULL DEFAULT NULL,
  `whatsapp` varchar(20) NULL DEFAULT NULL,
  `email` varchar(255) NULL DEFAULT NULL,
  `website` varchar(255) NULL DEFAULT NULL,
  `facebook_url` varchar(255) NULL DEFAULT NULL,
  `instagram_url` varchar(255) NULL DEFAULT NULL,
  `google_maps_url` varchar(255) NULL DEFAULT NULL,
  `manager_id` bigint(20) unsigned NULL DEFAULT NULL,
  `technical_responsible` varchar(255) NULL DEFAULT NULL,
  `opening_date` date NULL DEFAULT NULL,
  `operating_hours` text NULL DEFAULT NULL,
  `is_main_branch` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `observations` text NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_centers_code_unique` (`code`),
  UNIQUE KEY `service_centers_slug_unique` (`slug`),
  UNIQUE KEY `service_centers_cnpj_unique` (`cnpj`),
  KEY `service_centers_manager_id_index` (`manager_id`),
  KEY `service_centers_active_index` (`active`),
  KEY `service_centers_state_city_index` (`state`,`city`),
  KEY `service_centers_latitude_longitude_active_index` (`latitude`,`longitude`,`active`),
  KEY `service_centers_is_main_branch_index` (`is_main_branch`),
  FULLTEXT KEY `service_centers_search_fulltext` (`name`,`legal_name`,`trade_name`,`city`,`neighborhood`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Foreign Keys
ALTER TABLE `users` ADD CONSTRAINT `users_service_center_id_foreign`
  FOREIGN KEY (`service_center_id`) REFERENCES `service_centers` (`id`) ON DELETE SET NULL;

ALTER TABLE `service_centers` ADD CONSTRAINT `service_centers_manager_id_foreign`
  FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
```

## Dbdiagram Script

```sql
// Sistema de Permissões - Rei do Óleo
// Baseado no Spatie Permission Package

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

## Características do Sistema

### 🔐 **Controle Granular**

- **Permissões específicas** para cada ação
- **Papéis hierárquicos** (roles) para agrupamento
- **Polymorphic relationships** para flexibilidade
- **Guard-based** para múltiplos contextos

### 👥 **Gestão de Usuários**

- **Dados pessoais** completos
- **Informações profissionais** (salário, comissão)
- **Vinculação com centros** de serviço
- **Controle de atividade** e login

### 🏢 **Centros de Serviço**

- **Dados empresariais** completos
- **Geolocalização** para mapas
- **Redes sociais** integradas
- **Gestão de responsáveis** técnicos

### 🔑 **Tokens de API**

- **Autenticação via API** com Sanctum
- **Controle de abilities** por token
- **Expiração configurável**
- **Rastreamento de uso**

## Exemplos de Uso

### 📝 **Criação de Papéis e Permissões**

```php
// Criar permissões
Permission::create(['name' => 'view-users', 'guard_name' => 'web']);
Permission::create(['name' => 'create-users', 'guard_name' => 'web']);
Permission::create(['name' => 'edit-users', 'guard_name' => 'web']);
Permission::create(['name' => 'delete-users', 'guard_name' => 'web']);

// Criar papéis
$adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
$managerRole = Role::create(['name' => 'manager', 'guard_name' => 'web']);
$employeeRole = Role::create(['name' => 'employee', 'guard_name' => 'web']);

// Atribuir permissões aos papéis
$adminRole->givePermissionTo(['view-users', 'create-users', 'edit-users', 'delete-users']);
$managerRole->givePermissionTo(['view-users', 'create-users', 'edit-users']);
$employeeRole->givePermissionTo(['view-users']);
```

### 👤 **Gestão de Usuários**

```php
// Criar usuário
$user = User::create([
    'name' => 'João Silva',
    'email' => 'joao@reidooleo.com',
    'password' => Hash::make('password'),
    'service_center_id' => 1,
    'phone' => '(11) 99999-9999',
    'document' => '123.456.789-00',
    'hire_date' => '2024-01-15',
    'salary' => 2500.00,
    'commission_rate' => 5.00,
    'specialties' => json_encode(['Troca de Óleo', 'Alinhamento'])
]);

// Atribuir papel
$user->assignRole('manager');

// Verificar permissões
if ($user->hasPermissionTo('edit-users')) {
    // Usuário pode editar outros usuários
}

if ($user->hasRole('admin')) {
    // Usuário é administrador
}
```

### 🔍 **Consultas Avançadas**

```sql
-- Usuários com permissão específica
SELECT u.name, u.email, p.name as permission
FROM users u
JOIN model_has_permissions mhp ON u.id = mhp.model_id
JOIN permissions p ON mhp.permission_id = p.id
WHERE mhp.model_type = 'App\\Models\\User'
AND p.name = 'edit-users';

-- Usuários por papel e centro
SELECT u.name, r.name as role, sc.name as service_center
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
LEFT JOIN service_centers sc ON u.service_center_id = sc.id
WHERE mhr.model_type = 'App\\Models\\User'
AND u.active = true;

-- Permissões por papel
SELECT r.name as role, p.name as permission
FROM roles r
JOIN role_has_permissions rhp ON r.id = rhp.role_id
JOIN permissions p ON rhp.permission_id = p.id
ORDER BY r.name, p.name;
```

## Segurança e Boas Práticas

### 🔒 **Recomendações**

- **Sempre validar** permissões no backend
- **Usar guards** apropriados para diferentes contextos
- **Implementar cache** para permissões frequentemente consultadas
- **Auditar** alterações de permissões
- **Backup regular** das tabelas de permissões

### 🚨 **Considerações**

- **Soft deletes** preservam histórico
- **Foreign keys** garantem integridade
- **Índices otimizados** para performance
- **Polymorphic** permite extensibilidade

---

**📝 Documentação Baseada em**: [dbdiagram.io](https://docs.dbdiagram.io/)  
**🔧 Framework**: Laravel 12 + Spatie Permission  
**📊 Última Atualização**: 25/06/2025
