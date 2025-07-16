# 📝 Exemplo de Uso dos Seeders

Este arquivo demonstra como usar os seeders em diferentes cenários.

## 🎯 Cenário 1: Primeira Configuração do Projeto

```bash
# 1. Configurar o banco de dados
php artisan migrate:fresh

# 2. Popular com dados essenciais + fake data
php artisan seed:fake --fresh

# Resultado: Base completa com dados realistas para desenvolvimento
```

## 🎯 Cenário 2: Desenvolvimento Diário

```bash
# Manter dados existentes e adicionar apenas clientes fake
php artisan seed:fake --only=clients

# Adicionar veículos para os clientes existentes
php artisan seed:fake --only=vehicles

# Adicionar produtos para teste
php artisan seed:fake --only=products
```

## 🎯 Cenário 3: Testes e Demonstração

```bash
# Reset completo para demonstração
php artisan seed:fake --fresh

# Verificar dados criados
php artisan tinker
>>> App\Domain\Client\Models\Client::count(); // 50
>>> App\Domain\Client\Models\Vehicle::count(); // 80
>>> App\Domain\Product\Models\Product::count(); // 40+
>>> App\Domain\Service\Models\Service::count(); // 100
```

## 🎯 Cenário 4: Debug e Troubleshooting

```bash
# Se houver erro no seeder completo, executar individualmente
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=ServiceStatusSeeder
php artisan db:seed --class=PaymentMethodSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=ServiceCenterSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ClientFakeSeeder
php artisan db:seed --class=VehicleFakeSeeder
php artisan db:seed --class=ProductFakeSeeder
php artisan db:seed --class=ServiceFakeSeeder
php artisan db:seed --class=ServiceItemFakeSeeder
```

## 📊 Verificação dos Dados

### Verificar Clientes Criados

```php
// No tinker ou controller
$clients = App\Domain\Client\Models\Client::with('vehicles')->get();
$clients->each(function($client) {
    echo "Cliente: {$client->name} - Veículos: {$client->vehicles->count()}\n";
});
```

### Verificar Serviços com Itens

```php
$services = App\Domain\Service\Models\Service::with(['client', 'vehicle', 'serviceItems'])->get();
$services->each(function($service) {
    echo "Serviço: {$service->service_number} - Cliente: {$service->client->name} - Itens: {$service->serviceItems->count()}\n";
});
```

### Verificar Produtos por Categoria

```php
$categories = App\Domain\Product\Models\Category::with('products')->get();
$categories->each(function($category) {
    echo "Categoria: {$category->name} - Produtos: {$category->products->count()}\n";
});
```

## 🔧 Personalização

### Alterar Quantidade de Dados

**ClientFakeSeeder.php** - Linha 22:

```php
// Alterar de 50 para 100 clientes
for ($i = 0; $i < 100; $i++) {
```

**VehicleFakeSeeder.php** - Linha 47:

```php
// Alterar de 80 para 150 veículos
for ($i = 0; $i < 150; $i++) {
```

**ServiceFakeSeeder.php** - Linha 89:

```php
// Alterar de 100 para 200 serviços
for ($i = 0; $i < 200; $i++) {
```

### Adicionar Novos Produtos

**ProductFakeSeeder.php** - Adicionar na array `$products`:

```php
['name' => 'Novo Produto', 'price' => 99.90, 'unit' => 'Unidade'],
```

### Alterar Probabilidades

**ClientFakeSeeder.php** - Linha 20:

```php
// Alterar de 70% para 80% pessoa física
$isPerson = $faker->boolean(80);
```

## 🚨 Problemas Comuns

### Erro: "Class 'Faker\Factory' not found"

```bash
composer require fakerphp/faker
```

### Erro: "Table doesn't exist"

```bash
php artisan migrate
```

### Erro: "Foreign key constraint fails"

```bash
# Executar na ordem correta
php artisan seed:fake --fresh
```

### Erro: "Duplicate entry"

```bash
# Limpar dados existentes
php artisan migrate:fresh
php artisan seed:fake
```

## 📈 Performance

### Para Grandes Volumes de Dados

```php
// Usar chunk para melhor performance
DB::beginTransaction();
try {
    // Criar dados em lotes
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

### Otimizar Consultas

```php
// Carregar relacionamentos necessários
$clients = Client::with(['vehicles', 'services'])->get();
```

## 🎨 Dados Realistas

Os seeders geram dados realistas para o contexto brasileiro:

- **CPF/CNPJ**: Formatos válidos brasileiros
- **Telefones**: Formatos brasileiros
- **Endereços**: Cidades e estados brasileiros
- **Veículos**: Marcas populares no Brasil
- **Produtos**: Itens comuns em oficinas
- **Valores**: Preços realistas em Reais

## 🔄 Integração com Frontend

Após executar os seeders, o frontend terá dados para:

- Listar clientes com veículos
- Mostrar produtos por categoria
- Exibir serviços com status
- Calcular totais e relatórios
- Testar funcionalidades completas
