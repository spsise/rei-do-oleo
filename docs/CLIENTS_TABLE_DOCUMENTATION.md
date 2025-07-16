# 📋 Documentação da Tabela Clients

## 📖 Visão Geral

A tabela `clients` é uma das tabelas principais do sistema **Rei do Óleo**, responsável por armazenar todas as informações dos clientes da empresa. Esta tabela possui relacionamentos diretos com veículos e serviços, formando o núcleo do sistema de gestão de clientes.

## 🏗️ Estrutura da Tabela

### 📊 Campos Principais

| Campo        | Tipo        | Tamanho | Nullable | Default             | Descrição                      |
| ------------ | ----------- | ------- | -------- | ------------------- | ------------------------------ |
| `id`         | `bigint`    | -       | ❌       | `AUTO_INCREMENT`    | Chave primária única           |
| `name`       | `varchar`   | 255     | ❌       | -                   | Nome completo do cliente       |
| `phone01`    | `varchar`   | 20      | ❌       | -                   | Telefone principal             |
| `phone02`    | `varchar`   | 20      | ✅       | `NULL`              | Telefone secundário            |
| `email`      | `varchar`   | 255     | ✅       | `NULL`              | Email do cliente               |
| `cpf`        | `varchar`   | 14      | ✅       | `NULL`              | CPF do cliente                 |
| `cnpj`       | `varchar`   | 18      | ✅       | `NULL`              | CNPJ do cliente                |
| `address`    | `varchar`   | 255     | ✅       | `NULL`              | Endereço completo              |
| `city`       | `varchar`   | 100     | ✅       | `NULL`              | Cidade                         |
| `state`      | `varchar`   | 2       | ✅       | `NULL`              | Estado (UF)                    |
| `zip_code`   | `varchar`   | 10      | ✅       | `NULL`              | CEP                            |
| `notes`      | `text`      | -       | ✅       | `NULL`              | Observações sobre o cliente    |
| `active`     | `boolean`   | -       | ❌       | `true`              | Status de ativação             |
| `created_at` | `timestamp` | -       | ❌       | `CURRENT_TIMESTAMP` | Data de criação                |
| `updated_at` | `timestamp` | -       | ❌       | `CURRENT_TIMESTAMP` | Data de atualização            |
| `deleted_at` | `timestamp` | -       | ✅       | `NULL`              | Data de exclusão (Soft Delete) |

## 🔗 Relacionamentos

### Relacionamentos Diretos

```php
// Relacionamento com Veículos (1:N)
public function vehicles()
{
    return $this->hasMany(Vehicle::class);
}

// Relacionamento com Serviços (1:N)
public function services()
{
    return $this->hasMany(Service::class);
}

// Relacionamento com Service Items através de Services
public function serviceItems()
{
    return $this->hasManyThrough(ServiceItem::class, Service::class);
}
```

### Tabelas Relacionadas

| Tabela          | Tipo de Relacionamento | Descrição                          |
| --------------- | ---------------------- | ---------------------------------- |
| `vehicles`      | `hasMany`              | Veículos pertencentes ao cliente   |
| `services`      | `hasMany`              | Serviços realizados para o cliente |
| `service_items` | `hasManyThrough`       | Itens dos serviços do cliente      |

## 🗂️ Índices e Performance

### Índices Criados

```sql
-- Índices para otimização de consultas
CREATE INDEX clients_name_index ON clients(name);
CREATE INDEX clients_phone01_index ON clients(phone01);
CREATE INDEX clients_email_index ON clients(email);
CREATE INDEX clients_cpf_index ON clients(cpf);
CREATE INDEX clients_cnpj_index ON clients(cnpj);
CREATE INDEX clients_active_index ON clients(active);
CREATE INDEX clients_created_at_index ON clients(created_at);
CREATE INDEX clients_state_city_index ON clients(state, city);

-- Índice Full-Text para busca avançada
CREATE FULLTEXT INDEX clients_search_fulltext ON clients(name, phone01, cpf, cnpj);
```

### Otimizações de Performance

- **Índice composto** em `(state, city)` para consultas por região
- **Índices simples** em campos de busca frequente
- **Full-text search** para busca avançada
- **Índice temporal** em `created_at` para relatórios

## 🔍 Sistema de Busca

### Busca Full-Text

A tabela implementa busca full-text nos campos:

- **`name`**: Nome do cliente
- **`phone01`**: Telefone principal
- **`cpf`**: CPF do cliente
- **`cnpj`**: CNPJ do cliente

### Exemplo de Uso

```php
// Busca full-text
$clients = Client::whereRaw('MATCH(name, phone01, cpf, cnpj) AGAINST(? IN BOOLEAN MODE)', ['joão silva'])
    ->get();

// Busca por região
$clients = Client::where('state', 'SP')
    ->where('city', 'São Paulo')
    ->where('active', true)
    ->get();

// Busca por documento
$client = Client::where('cpf', '123.456.789-00')
    ->orWhere('cnpj', '12.345.678/0001-90')
    ->first();
```

## 🔄 Soft Deletes

A tabela implementa **Soft Deletes** através do campo `deleted_at`:

```php
// No modelo Client
use SoftDeletes;

// Exemplo de uso
$client->delete(); // Marca como deletado
Client::withTrashed()->find($id); // Inclui registros deletados
Client::onlyTrashed()->get(); // Apenas registros deletados
```

## 📊 Migrações Relacionadas

### Ordem de Execução

1. `2025_06_25_012422_create_clients_table.php` - Criação da tabela clients
2. `2025_06_25_012425_create_vehicles_table.php` - Tabela de veículos (relacionamento)
3. `2025_06_25_012438_create_services_table.php` - Tabela de serviços (relacionamento)

### Dependências

- **`vehicles`**: Depende de `clients` (foreign key)
- **`services`**: Depende de `clients` e `vehicles` (foreign keys)
- **`service_items`**: Depende de `services` (foreign key)

## 🎯 Casos de Uso

### Consultas Comuns

```php
// Clientes ativos com veículos
$clients = Client::where('active', true)
    ->with(['vehicles', 'services'])
    ->get();

// Clientes por região
$clients = Client::where('state', $state)
    ->where('city', $city)
    ->where('active', true)
    ->get();

// Clientes com mais serviços
$clients = Client::withCount('services')
    ->orderBy('services_count', 'desc')
    ->get();

// Clientes com veículos específicos
$clients = Client::whereHas('vehicles', function ($query) {
    $query->where('brand', 'Toyota');
})->get();

// Histórico de serviços por cliente
$client = Client::with(['services' => function ($query) {
    $query->orderBy('created_at', 'desc');
}])->find($clientId);
```

### Validações

```php
// Request de criação/atualização
class ClientRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone01' => 'required|string|max:20',
            'phone02' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'cpf' => 'nullable|string|max:14|unique:clients,cpf,' . $this->client?->id,
            'cnpj' => 'nullable|string|max:18|unique:clients,cnpj,' . $this->client?->id,
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|size:2',
            'zip_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'cnpj.unique' => 'Este CNPJ já está cadastrado.',
            'state.size' => 'O estado deve ter exatamente 2 caracteres.',
        ];
    }
}
```

## 🔧 Manutenção e Monitoramento

### Limpeza de Dados

```sql
-- Clientes inativos há mais de 1 ano
SELECT * FROM clients
WHERE active = false
AND updated_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Clientes sem veículos
SELECT c.* FROM clients c
LEFT JOIN vehicles v ON c.id = v.client_id
WHERE v.id IS NULL AND c.active = true;

-- Clientes sem serviços
SELECT c.* FROM clients c
LEFT JOIN services s ON c.id = s.client_id
WHERE s.id IS NULL AND c.active = true;
```

### Backup e Recuperação

```bash
# Backup específico da tabela clients
mysqldump -u username -p database_name clients > clients_backup.sql

# Restauração
mysql -u username -p database_name < clients_backup.sql
```

## 📈 Métricas e Relatórios

### KPIs Importantes

- **Total de clientes ativos**: `Client::where('active', true)->count()`
- **Clientes por região**: `Client::groupBy('state', 'city')->count()`
- **Clientes com veículos**: `Client::whereHas('vehicles')->count()`
- **Clientes com serviços**: `Client::whereHas('services')->count()`

### Relatórios Sugeridos

1. **Relatório de Clientes**: Lista completa com dados de contato
2. **Análise por Região**: Distribuição geográfica de clientes
3. **Histórico de Serviços**: Clientes com mais serviços realizados
4. **Análise de Veículos**: Clientes com mais veículos cadastrados

## 🔍 Consultas Avançadas

### Relatórios Complexos

```sql
-- Clientes com estatísticas completas
SELECT
    c.name,
    c.city,
    c.state,
    COUNT(DISTINCT v.id) as total_vehicles,
    COUNT(s.id) as total_services,
    SUM(s.final_amount) as total_spent,
    MAX(s.created_at) as last_service
FROM clients c
LEFT JOIN vehicles v ON c.id = v.client_id
LEFT JOIN services s ON c.id = s.client_id
WHERE c.active = true
GROUP BY c.id, c.name, c.city, c.state
ORDER BY total_spent DESC;

-- Clientes por período de cadastro
SELECT
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as new_clients
FROM clients
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY month;

-- Top clientes por valor gasto
SELECT
    c.name,
    c.phone01,
    SUM(s.final_amount) as total_spent,
    COUNT(s.id) as services_count
FROM clients c
JOIN services s ON c.id = s.client_id
WHERE s.final_amount IS NOT NULL
GROUP BY c.id, c.name, c.phone01
ORDER BY total_spent DESC
LIMIT 10;
```

## ⚠️ Considerações Importantes

### Segurança

- Validação de CPF/CNPJ únicos
- Controle de acesso via sistema de permissões
- Soft deletes para preservar histórico
- Validação de dados no nível da aplicação

### Performance

- Índices otimizados para consultas frequentes
- Full-text search para buscas avançadas
- Relacionamentos eager loading quando necessário
- Cache para consultas complexas

### Integridade

- Foreign key constraints para `vehicles` e `services`
- Validações de dados no nível da aplicação
- Controle de versão através de timestamps
- Backup regular dos dados

## 🚀 Otimizações Futuras

### Sugestões de Melhorias

1. **Campos Adicionais**:

   - `birth_date` - Data de nascimento
   - `gender` - Gênero
   - `preferred_contact` - Contato preferido
   - `marketing_consent` - Consentimento para marketing

2. **Funcionalidades**:

   - Sistema de fidelidade
   - Notificações automáticas
   - Integração com WhatsApp
   - Histórico de comunicações

3. **Performance**:
   - Particionamento por região
   - Cache Redis para consultas frequentes
   - Índices compostos adicionais

---

**📝 Última Atualização**: 25/06/2025  
**🔧 Versão**: 1.0  
**👨‍💻 Responsável**: Equipe de Desenvolvimento Rei do Óleo
