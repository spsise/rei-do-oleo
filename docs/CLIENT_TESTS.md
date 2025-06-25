# 📋 Documentação dos Testes - ClientTest.php

Este documento explica detalhadamente cada teste implementado no arquivo `ClientTest.php`, sua finalidade e importância para garantir a qualidade do modelo `Client`.

## 📖 Visão Geral

O arquivo `ClientTest.php` contém **36 testes unitários** que verificam todas as funcionalidades do modelo `Client`, incluindo:

- Estrutura e configuração do modelo
- Relacionamentos com outras entidades
- Scopes de busca e filtros
- Atributos calculados
- Validações de dados
- Funcionalidades de cache
- Soft deletes
- Factories

---

## 🧪 Lista Completa dos Testes

### 1. **Estrutura e Configuração do Modelo**

#### `it_has_correct_fillable_attributes()`

**Finalidade:** Verifica se os atributos que podem ser preenchidos em massa estão corretos.

**O que testa:**

- Confirma que apenas os campos permitidos podem ser preenchidos via `mass assignment`
- Garante segurança contra vulnerabilidades de `mass assignment`
- Campos testados: `name`, `phone01`, `phone02`, `email`, `cpf`, `cnpj`, `address`, `city`, `state`, `zip_code`, `notes`, `active`

#### `it_has_correct_casts()`

**Finalidade:** Verifica se os tipos de dados estão sendo convertidos corretamente.

**O que testa:**

- `id` → `int`
- `active` → `boolean`
- `created_at` → `datetime`
- `updated_at` → `datetime`

#### `it_has_proper_table_name()`

**Finalidade:** Confirma que o modelo está apontando para a tabela correta.

**O que testa:** Tabela `clients`

#### `it_has_proper_primary_key()`

**Finalidade:** Verifica a configuração da chave primária.

**O que testa:**

- Chave primária é `id`
- É um campo auto-incrementável

---

### 2. **Relacionamentos do Modelo**

#### `it_has_many_vehicles()`

**Finalidade:** Testa o relacionamento com veículos.

**O que testa:**

- Relacionamento é do tipo `HasMany`
- Um cliente pode ter múltiplos veículos
- Veículos criados aparecem na coleção do cliente

#### `it_has_many_services()`

**Finalidade:** Testa o relacionamento com serviços.

**O que testa:**

- Relacionamento é do tipo `HasMany`
- Um cliente pode ter múltiplos serviços
- Serviços criados aparecem na coleção do cliente

#### `it_has_last_service_relationship()`

**Finalidade:** Testa o relacionamento com o último serviço.

**O que testa:**

- Relacionamento é do tipo `HasOne`
- Retorna sempre o serviço mais recente (baseado em `created_at`)
- Funciona corretamente quando há múltiplos serviços

#### `client_has_services_relationship()`

**Finalidade:** Teste adicional para confirmar o relacionamento com serviços.

**O que testa:** Verificação simples de que serviços são carregados corretamente

---

### 3. **Scopes de Busca e Filtros**

#### `active_scope_returns_only_active_clients()`

**Finalidade:** Testa o scope `active()`.

**O que testa:**

- Retorna apenas clientes com `active = true`
- Ignora clientes inativos
- Conta correta de registros

#### `search_by_name_scope_filters_by_name()`

**Finalidade:** Testa busca por nome usando `searchByName()`.

**O que testa:**

- Busca parcial no campo `name`
- Case-insensitive
- Retorna apenas registros que contêm o termo

#### `search_by_phone_scope_filters_by_phone()`

**Finalidade:** Testa busca por telefone usando `searchByPhone()`.

**O que testa:**

- Busca nos campos `phone01` e `phone02`
- Correspondência exata de números

#### `search_by_document_scope_filters_by_document()`

**Finalidade:** Testa busca por documentos (CPF/CNPJ).

**O que testa:**

- Busca por CPF válido
- Busca por CNPJ válido
- Retorna registros corretos

#### `search_scope_combines_multiple_search_criteria()`

**Finalidade:** Testa o scope geral `search()`.

**O que testa:**

- Busca combinada em múltiplos campos
- Nome, CPF, CNPJ, email, telefone
- Retorna resultados de qualquer campo que corresponda

#### `by_city_scope_filters_by_city()`

**Finalidade:** Testa filtro por cidade usando `byCity()`.

**O que testa:**

- Filtra clientes por cidade específica
- Retorna apenas registros da cidade solicitada

#### `by_state_scope_filters_by_state()`

**Finalidade:** Testa filtro por estado usando `byState()`.

**O que testa:**

- Filtra clientes por estado específico
- Retorna apenas registros do estado solicitado

#### `by_document_type_scope_filters_by_document_type()`

**Finalidade:** Testa filtro por tipo de documento.

**O que testa:**

- Diferencia entre pessoas físicas (CPF) e jurídicas (CNPJ)
- Conta correta de cada tipo

---

### 4. **Atributos Calculados**

#### `full_address_attribute_combines_address_fields()`

**Finalidade:** Testa o atributo calculado `full_address`.

**O que testa:**

- Combina `address`, `city`, `state`, `zip_code`
- Formato: "Rua, Cidade, Estado, CEP"
- Remove campos vazios automaticamente

#### `total_services_attribute_counts_client_services()`

**Finalidade:** Testa o atributo `totalServices`.

**O que testa:**

- Conta apenas serviços do cliente específico
- Não inclui serviços de outros clientes
- Performance otimizada com eager loading

#### `total_spent_attribute_sums_service_totals()`

**Finalidade:** Testa o atributo `totalSpent`.

**O que testa:**

- Soma todos os valores de serviços do cliente
- Ignora serviços sem valor (`null`)
- Cálculo correto de totais

#### `last_service_date_attribute_returns_correct_date()`

**Finalidade:** Testa o atributo `lastServiceDate`.

**O que testa:**

- Retorna a data do último serviço
- Formato de data correto
- Null quando não há serviços

#### `next_service_reminder_calculates_based_on_last_service()`

**Finalidade:** Testa o atributo `nextServiceReminder`.

**O que testa:**

- Calcula próximo lembrete (último serviço + 6 meses)
- Retorna objeto Carbon válido
- Cálculo correto de datas

---

### 5. **Funcionalidades de Cache**

#### `it_caches_client_by_license_plate()`

**Finalidade:** Testa o sistema de cache por placa.

**O que testa:**

- Primeira busca salva no cache
- Segunda busca usa o cache
- Chave de cache correta

#### `it_invalidates_cache_when_client_is_updated()`

**Finalidade:** Testa invalidação automática do cache.

**O que testa:**

- Cache é limpo quando cliente é atualizado
- Sistema de invalidação automática funciona
- Evento `updated` dispara limpeza

---

### 6. **Validações de Dados Brasileiros**

#### `it_validates_cpf_format()`

**Finalidade:** Testa validação de CPF.

**O que testa:**

- CPF com 11 dígitos numéricos
- Formato correto armazenado
- Validação regex

#### `it_validates_cnpj_format()`

**Finalidade:** Testa validação de CNPJ.

**O que testa:**

- CNPJ com 14 dígitos numéricos
- Formato correto armazenado
- Validação regex

#### `it_validates_brazilian_phone_format()`

**Finalidade:** Testa validação de telefone brasileiro.

**O que testa:**

- Formato de telefone brasileiro válido
- Armazenamento correto

#### `it_validates_brazilian_zipcode_format()`

**Finalidade:** Testa validação de CEP brasileiro.

**O que testa:**

- Formato de CEP válido
- Armazenamento correto

#### `it_validates_email_format_when_provided()`

**Finalidade:** Testa validação de email.

**O que testa:**

- Email válido quando fornecido
- Armazenamento correto

#### `it_validates_phone_format()`

**Finalidade:** Teste adicional de validação de telefone.

**O que testa:** Formato de telefone válido

---

### 7. **Separação de Dados Pessoa Física/Jurídica**

#### `it_stores_cpf_and_cnpj_separately()`

**Finalidade:** Testa armazenamento separado de CPF e CNPJ.

**O que testa:**

- CPF e CNPJ são mutuamente exclusivos
- Um não interfere no outro
- Armazenamento correto de cada tipo

---

### 8. **Funcionalidades de Factory**

#### `factory_creates_individual_client_by_default()`

**Finalidade:** Testa criação padrão da factory.

**O que testa:**

- Factory cria pessoa física por padrão
- Tem CPF válido
- Não tem CNPJ
- Campos obrigatórios preenchidos

#### `factory_can_create_company_client()`

**Finalidade:** Testa criação de empresa pela factory.

**O que testa:**

- Factory pode criar pessoa jurídica
- Tem CNPJ válido
- Não tem CPF
- Nome contém sufixos empresariais (Ltda, S/A, ME, EPP)

#### `factory_can_create_individual_client()`

**Finalidade:** Testa criação explícita de pessoa física.

**O que testa:**

- Método `individual()` funciona
- Tem CPF válido
- Não tem CNPJ

---

### 9. **Soft Deletes**

#### `it_uses_soft_deletes()`

**Finalidade:** Testa funcionalidade de exclusão suave.

**O que testa:**

- Registro não é removido fisicamente
- Campo `deleted_at` é preenchido
- Soft delete funciona corretamente

#### `it_can_restore_soft_deleted_client()`

**Finalidade:** Testa restauração de registros excluídos.

**O que testa:**

- Registro pode ser restaurado
- Campo `deleted_at` volta a ser null
- Funcionalidade de restore funciona

---

### 10. **Tratamento de Valores Nulos**

#### `it_handles_null_values_properly()`

**Finalidade:** Testa tratamento de campos opcionais.

**O que testa:**

- Campos opcionais podem ser null
- Sistema não quebra com valores nulos
- Campos testados: `phone02`, `email`, `address`, `notes`

---

## 🎯 Importância dos Testes

### **Cobertura de Funcionalidades**

- ✅ **100% das funcionalidades do modelo testadas**
- ✅ **Relacionamentos validados**
- ✅ **Scopes de busca verificados**
- ✅ **Atributos calculados testados**

### **Qualidade e Confiabilidade**

- ✅ **Detecta regressões automaticamente**
- ✅ **Garante funcionamento correto após mudanças**
- ✅ **Valida regras de negócio específicas**

### **Padrões Brasileiros**

- ✅ **Validações de CPF/CNPJ**
- ✅ **Formatos de telefone brasileiro**
- ✅ **CEP brasileiro**
- ✅ **Diferenciação PF/PJ**

### **Performance**

- ✅ **Testes otimizados para evitar N+1 queries**
- ✅ **Cache testado adequadamente**
- ✅ **Eager loading validado**

---

## 🔧 Execução dos Testes

### **Todos os testes:**

```bash
./vendor/bin/phpunit tests/Unit/Models/ClientTest.php
```

### **Teste específico:**

```bash
./vendor/bin/phpunit tests/Unit/Models/ClientTest.php --filter="nome_do_teste"
```

### **Com detalhes:**

```bash
./vendor/bin/phpunit tests/Unit/Models/ClientTest.php --testdox
```

---

## 📊 Estatísticas

- **Total de Testes:** 36
- **Categorias:** 10
- **Asserções:** ~79
- **Cobertura:** Funcionalidades críticas 100%
- **Performance:** Otimizado (sem warnings de queries excessivas)

---

## 🚀 Benefícios

1. **Confiabilidade:** Garante que o modelo funciona corretamente
2. **Manutenibilidade:** Facilita mudanças futuras
3. **Documentação:** Serve como documentação viva do código
4. **Regression Testing:** Detecta problemas em mudanças
5. **Qualidade:** Mantém alta qualidade do código

Este conjunto de testes fornece uma base sólida para o desenvolvimento e manutenção do modelo `Client`, garantindo que todas as funcionalidades essenciais estejam funcionando corretamente e de acordo com os padrões brasileiros de dados.
