# 🗄️ Seeders do Sistema Rei do Óleo

Este diretório contém os seeders para popular a base de dados com dados iniciais e fake para desenvolvimento.

## 📋 Seeders Disponíveis

### 🔧 Seeders Básicos (Dados de Referência)

- **`RolePermissionSeeder`** - Cria roles e permissões do sistema
- **`ServiceStatusSeeder`** - Cria status de serviços (agendado, em andamento, etc.)
- **`PaymentMethodSeeder`** - Cria métodos de pagamento
- **`CategorySeeder`** - Cria categorias de produtos
- **`ServiceCenterSeeder`** - Cria centros de serviço
- **`UserSeeder`** - Cria usuários do sistema

### 🎭 Seeders Fake (Dados para Desenvolvimento)

- **`ClientFakeSeeder`** - Cria 50 clientes fake
- **`VehicleFakeSeeder`** - Cria 80 veículos fake
- **`ProductFakeSeeder`** - Cria produtos fake (óleos, filtros, peças, etc.)
- **`ServiceFakeSeeder`** - Cria 100 serviços fake
- **`ServiceItemFakeSeeder`** - Cria itens de serviço fake

## 🚀 Como Usar

### 1. Seeder Básico (Produção)

Para popular apenas com dados essenciais:

```bash
php artisan db:seed
```

### 2. Seeder Completo com Dados Fake (Desenvolvimento)

Para popular com dados completos incluindo fake data:

```bash
# Usando o comando personalizado (recomendado)
php artisan seed:fake

# Ou usando o seeder diretamente
php artisan db:seed --class=DatabaseSeederFake
```

### 3. Comando Personalizado

O sistema inclui um comando personalizado para facilitar o uso:

```bash
# População completa
php artisan seed:fake

# Reset completo + população
php artisan seed:fake --fresh

# População segura (verifica dados existentes)
php artisan seed:fake --safe

# Limpar dados fake e recriar
php artisan seed:fake --clean

# Seeder final (resolve todos os problemas de duplicação)
php artisan seed:fake --final

# Apenas clientes fake
php artisan seed:fake --only=clients

# Apenas veículos fake
php artisan seed:fake --only=vehicles

# Apenas produtos fake
php artisan seed:fake --only=products

# Apenas serviços fake
php artisan seed:fake --only=services

# Apenas itens de serviço fake
php artisan seed:fake --only=items
```

### 4. Seeders Individuais

Para executar seeders específicos:

```bash
# Apenas clientes fake
php artisan db:seed --class=ClientFakeSeeder

# Apenas produtos fake
php artisan db:seed --class=ProductFakeSeeder

# Apenas serviços fake
php artisan db:seed --class=ServiceFakeSeeder
```

## 📊 Dados Gerados

### Clientes Fake (50 registros)

- 70% pessoa física, 30% pessoa jurídica
- Dados completos: nome, telefone, email, CPF/CNPJ, endereço
- 90% ativos

### Veículos Fake (80 registros)

- Marcas populares no Brasil (Fiat, VW, Chevrolet, etc.)
- Dados técnicos: ano, cor, placa, quilometragem, tipo de combustível
- Relacionamento com clientes
- Data do último serviço

### Produtos Fake (40+ registros)

- Óleos de motor, transmissão, freio
- Filtros (óleo, ar, combustível, cabine)
- Pastilhas e discos de freio
- Baterias automotivas
- Pneus diversos
- Amortecedores e molas
- Correias e velas
- Fluidos e aditivos
- Acessórios automotivos

### Serviços Fake (100 registros)

- Datas realistas (últimos 6 meses)
- Status variados (agendado, em andamento, concluído)
- Valores entre R$ 50 e R$ 800
- Descontos ocasionais
- Observações e notas

### Itens de Serviço Fake

- Produtos utilizados em cada serviço
- Quantidades e preços
- Mão de obra (70% dos serviços)
- Descontos em itens específicos

## 🔄 Reset e Recriar

Para limpar a base e recriar com dados fake:

```bash
# Limpar tudo
php artisan migrate:fresh

# Popular com dados fake
php artisan db:seed --class=DatabaseSeederFake
```

## ⚠️ Importante

- Os seeders fake devem ser usados **APENAS** em ambiente de desenvolvimento
- Nunca execute em produção
- Os dados são gerados usando Faker com localização pt_BR
- Alguns seeders dependem de outros (verificar ordem de execução)

## 🎯 Casos de Uso

### Desenvolvimento

```bash
# Ambiente de desenvolvimento completo
php artisan migrate:fresh
php artisan db:seed --class=DatabaseSeederFake
```

### Testes

```bash
# Para testes unitários
php artisan migrate:fresh --seed
```

### Demonstração

```bash
# Para demonstração com dados realistas
php artisan migrate:fresh
php artisan db:seed --class=DatabaseSeederFake
```

## 📝 Personalização

Para modificar a quantidade de dados gerados, edite os arquivos dos seeders:

- `ClientFakeSeeder.php` - Linha 22: altere o número de clientes
- `VehicleFakeSeeder.php` - Linha 47: altere o número de veículos
- `ProductFakeSeeder.php` - Linha 89: altere produtos adicionais
- `ServiceFakeSeeder.php` - Linha 89: altere o número de serviços

## 🔧 Troubleshooting

### Erro: "Column not found: chassis"

O campo `chassis` foi removido do modelo Vehicle. Use o seeder corrigido:

```bash
php artisan seed:fake --safe
```

### Erro: "Duplicate entry"

Dados já existem. Use o seeder seguro:

```bash
php artisan seed:fake --safe
```

### Erro: "Nenhum cliente encontrado"

Execute os seeders na ordem correta:

```bash
php artisan db:seed --class=ClientFakeSeeder
php artisan db:seed --class=VehicleFakeSeeder
```

### Erro: "Nenhuma categoria encontrada"

Execute o CategorySeeder primeiro:

```bash
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=ProductFakeSeeder
```

### Erro de chave estrangeira

Certifique-se de executar os seeders na ordem:

1. Dados de referência (roles, status, etc.)
2. Clientes
3. Veículos
4. Produtos
5. Serviços
6. Itens de serviço

### Solução Rápida para Problemas

Use o seeder seguro que verifica dados existentes:

```bash
php artisan seed:fake --safe
```

### Para Problemas de Dados Duplicados

Limpe os dados fake existentes e recrie:

```bash
php artisan seed:fake --clean
```

### Para Problemas de Chaves Únicas

Use o seeder final que resolve todos os problemas:

```bash
php artisan seed:fake --final
```

### Para Testar Itens de Serviço Especificamente

```bash
php artisan test:service-items
```
