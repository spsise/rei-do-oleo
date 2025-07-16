# 👤 Script DBML - Tabela Clients e Relacionamentos

## 📋 Como Usar

1. Acesse [dbdiagram.io](https://dbdiagram.io/)
2. Clique em "Create New Diagram"
3. Cole o script abaixo na área de código
4. O diagrama será gerado automaticamente

## 🗄️ Script DBML Completo

```sql
// Tabela Clients e Relacionamentos - Rei do Óleo
// Sistema de Gestão de Clientes

Table clients {
  id bigint [pk, increment]
  name varchar(255) [not null]
  phone01 varchar(20) [not null]
  phone02 varchar(20) [null]
  email varchar(255) [null]
  cpf varchar(14) [null]
  cnpj varchar(18) [null]
  address varchar(255) [null]
  city varchar(100) [null]
  state varchar(2) [null]
  zip_code varchar(10) [null]
  notes text [null]
  active boolean [default: true]
  created_at timestamp [null]
  updated_at timestamp [null]
  deleted_at timestamp [null]

  indexes {
    (name)
    (phone01)
    (email)
    (cpf)
    (cnpj)
    (active)
    (created_at)
    (state, city)
    (name, phone01, cpf, cnpj) [type: fulltext]
  }
}

Table vehicles {
  id bigint [pk, increment]
  client_id bigint [not null]
  license_plate varchar(8) [unique, not null]
  brand varchar(50) [null]
  model varchar(100) [null]
  year year [null]
  color varchar(30) [null]
  fuel_type varchar(20) [null]
  mileage integer [null]
  last_service date [null]
  created_at timestamp [null]
  updated_at timestamp [null]

  indexes {
    (license_plate)
    (client_id)
    (brand, model)
    (last_service)
    (created_at)
  }
}

Table services {
  id bigint [pk, increment]
  client_id bigint [not null]
  vehicle_id bigint [not null]
  user_id bigint [not null]
  service_center_id bigint [not null]
  service_number varchar(20) [unique, not null]
  scheduled_at timestamp [null]
  started_at timestamp [null]
  completed_at timestamp [null]
  service_status_id bigint [not null]
  payment_method_id bigint [null]
  mileage_at_service integer [null]
  total_amount decimal(10,2) [null]
  discount_amount decimal(10,2) [null]
  final_amount decimal(10,2) [null]
  observations text [null]
  notes text [null]
  active boolean [default: true]
  created_at timestamp [null]
  updated_at timestamp [null]
  deleted_at timestamp [null]

  indexes {
    (service_number)
    (client_id)
    (vehicle_id)
    (service_center_id)
    (service_status_id)
    (scheduled_at)
    (service_status_id, scheduled_at)
    (service_center_id, scheduled_at)
    (active)
    (created_at)
  }
}

Table service_items {
  id bigint [pk, increment]
  service_id bigint [not null]
  product_id bigint [not null]
  quantity integer [not null]
  unit_price decimal(10,2) [not null]
  total_price decimal(10,2) [not null]
  notes text [null]
  created_at timestamp [null]
  updated_at timestamp [null]

  indexes {
    (service_id, product_id) [unique]
    (service_id)
    (product_id)
  }
}

Table users {
  id bigint [pk, increment]
  name varchar(255) [not null]
  email varchar(255) [unique, not null]
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
}

Table service_statuses {
  id bigint [pk, increment]
  name varchar(50) [unique, not null]
  description varchar(255) [null]
  color varchar(7) [default: '#6B7280']
  sort_order integer [default: 0]
  created_at timestamp [null]
  updated_at timestamp [null]
}

Table payment_methods {
  id bigint [pk, increment]
  name varchar(50) [unique, not null]
  description varchar(255) [null]
  active boolean [default: true]
  sort_order integer [default: 0]
  created_at timestamp [null]
  updated_at timestamp [null]
}

Table categories {
  id bigint [pk, increment]
  name varchar(100) [not null]
  slug varchar(255) [unique, not null]
  description text [null]
  parent_id bigint [null]
  sort_order integer [default: 0]
  active boolean [default: true]
  created_at timestamp [null]
  updated_at timestamp [null]
  deleted_at timestamp [null]

  indexes {
    (name)
    (slug)
    (parent_id)
    (active)
    (sort_order)
  }
}

Table products {
  id bigint [pk, increment]
  category_id bigint [not null]
  name varchar(255) [not null]
  slug varchar(255) [unique, not null]
  description text [null]
  sku varchar(50) [unique, not null]
  price decimal(10,2) [not null]
  stock_quantity integer [null]
  min_stock integer [null]
  unit varchar(10) [null]
  active boolean [default: true]
  created_at timestamp [null]
  updated_at timestamp [null]

  indexes {
    (category_id)
    (name)
    (slug)
    (sku)
    (active)
    (price)
  }
}

// Relacionamentos
Ref: vehicles.client_id > clients.id [delete: cascade]

Ref: services.client_id > clients.id [delete: cascade]
Ref: services.vehicle_id > vehicles.id [delete: cascade]
Ref: services.user_id > users.id [delete: cascade]
Ref: services.service_center_id > service_centers.id [delete: cascade]
Ref: services.service_status_id > service_statuses.id
Ref: services.payment_method_id > payment_methods.id [delete: set null]

Ref: service_items.service_id > services.id [delete: cascade]
Ref: service_items.product_id > products.id [delete: cascade]

Ref: users.service_center_id > service_centers.id [delete: set null]
Ref: service_centers.manager_id > users.id [delete: set null]

Ref: categories.parent_id > categories.id [delete: set null]
Ref: products.category_id > categories.id [delete: cascade]
```

## 🎨 Personalização do Diagrama

### Cores Sugeridas

- **clients**: Azul (#3B82F6) - Tabela principal
- **vehicles**: Verde (#10B981) - Entidade relacionada
- **services**: Laranja (#F59E0B) - Entidade de negócio
- **service_items**: Roxo (#8B5CF6) - Entidade de detalhe
- **users**: Cinza (#6B7280) - Entidade de sistema
- **service_centers**: Vermelho (#EF4444) - Entidade organizacional
- **categories**: Amarelo (#F59E0B) - Entidade de catálogo
- **products**: Verde-azulado (#06B6D4) - Entidade de produto

### Estilos de Relacionamento

- **Cascade Delete**: Linha sólida vermelha
- **Set Null**: Linha tracejada azul
- **Restrict**: Linha sólida preta

## 📊 Características do Script

### 🔗 **Relacionamentos Principais**

- **clients → vehicles**: 1:N (um cliente pode ter vários veículos)
- **clients → services**: 1:N (um cliente pode ter vários serviços)
- **vehicles → services**: 1:N (um veículo pode ter vários serviços)
- **services → service_items**: 1:N (um serviço pode ter vários itens)
- **categories → categories**: 1:N (categorias pai/filho)
- **categories → products**: 1:N (uma categoria pode ter vários produtos)
- **service_centers → users**: 1:N (um centro pode ter vários usuários)
- **users → services**: 1:N (um usuário pode realizar vários serviços)

### 🎯 **Índices Otimizados**

- **Full-text search** na tabela clients
- **Índices compostos** para consultas complexas
- **Índices únicos** para integridade
- **Índices temporais** para relatórios

### 🔍 **Busca Avançada**

- **Full-text** em clients (nome, telefone, documentos)
- **Índices geográficos** (estado, cidade)
- **Índices de performance** (ativo, criado em)

## 🔧 Comandos Úteis

### Consultas Frequentes

```sql
-- Clientes com veículos e serviços
SELECT
    c.name,
    c.phone01,
    COUNT(DISTINCT v.id) as total_vehicles,
    COUNT(s.id) as total_services
FROM clients c
LEFT JOIN vehicles v ON c.id = v.client_id
LEFT JOIN services s ON c.id = s.client_id
WHERE c.active = true
GROUP BY c.id, c.name, c.phone01;

-- Top clientes por valor gasto
SELECT
    c.name,
    SUM(s.final_amount) as total_spent
FROM clients c
JOIN services s ON c.id = s.client_id
WHERE s.final_amount IS NOT NULL
GROUP BY c.id, c.name
ORDER BY total_spent DESC
LIMIT 10;
```

### Laravel Eloquent

```php
// Cliente com relacionamentos
$client = Client::with(['vehicles', 'services.serviceItems'])
    ->find($clientId);

// Clientes por região
$clients = Client::where('state', 'SP')
    ->where('city', 'São Paulo')
    ->where('active', true)
    ->get();

// Busca full-text
$clients = Client::whereRaw('MATCH(name, phone01, cpf, cnpj) AGAINST(? IN BOOLEAN MODE)', ['joão'])
    ->get();
```

## 📈 Métricas Importantes

### KPIs de Clientes

- **Total de clientes ativos**
- **Clientes por região**
- **Clientes com veículos**
- **Clientes com serviços**
- **Valor médio por cliente**

### Relatórios Sugeridos

1. **Distribuição geográfica** de clientes
2. **Histórico de serviços** por cliente
3. **Análise de veículos** por cliente
4. **Performance financeira** por cliente

---

**🔗 Link para dbdiagram.io**: [https://dbdiagram.io/](https://dbdiagram.io/)  
**📝 Documentação Completa**: [CLIENTS_TABLE_DOCUMENTATION.md](./CLIENTS_TABLE_DOCUMENTATION.md)  
**📊 Última Atualização**: 25/06/2025
