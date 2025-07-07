# 🚀 Comando DevReset - Reset da Base de Desenvolvimento

O comando `dev:reset` foi criado para facilitar o reset da base de dados durante o desenvolvimento, executando migrations frescas e populando com dados fake quando necessário.

## 📋 Uso Básico

```bash
# Reset completo com confirmação
php artisan dev:reset

# Reset forçado sem confirmação
php artisan dev:reset --force

# Reset apenas com seeders (sem migrate:fresh)
php artisan dev:reset --seed-only
```

## 🎭 Opções para Dados Fake

### Reset com dados fake básicos

```bash
php artisan dev:reset --fake
```

### Reset com seeder seguro (verifica dados existentes)

```bash
php artisan dev:reset --fake --safe
```

### Reset com limpeza de dados fake existentes

```bash
php artisan dev:reset --fake --clean
```

### Reset com seeder final (resolve problemas de duplicação)

```bash
php artisan dev:reset --fake --final
```

### Reset apenas um seeder específico

```bash
# Apenas clientes
php artisan dev:reset --fake --only=clients

# Apenas veículos
php artisan dev:reset --fake --only=vehicles

# Apenas produtos
php artisan dev:reset --fake --only=products

# Apenas serviços
php artisan dev:reset --fake --only=services

# Apenas itens de serviço
php artisan dev:reset --fake --only=items
```

## 🔄 O que o Comando Faz

### 1. **Verificação de Ambiente**

- ✅ Verifica se está em ambiente de desenvolvimento (`local` ou `development`)
- ❌ Bloqueia execução em produção

### 2. **Confirmação de Segurança**

- 🔒 Solicita confirmação antes de resetar (exceto com `--force`)
- ⚠️ Avisa sobre perda de dados

### 3. **Execução de Migrations**

- 📊 Executa `migrate:fresh` (exceto com `--seed-only`)
- 🔄 Recria todas as tabelas do zero

### 4. **Seeders Básicos**

- 🌱 Executa seeders essenciais:
  - `RolePermissionSeeder`
  - `ServiceStatusSeeder`
  - `PaymentMethodSeeder`
  - `CategorySeeder`
  - `ServiceCenterSeeder`
  - `UserSeeder`

### 5. **Dados Fake (opcional)**

- 🎭 Executa seeders de dados fake baseado nas opções:
  - **Padrão**: `DatabaseSeederFake`
  - **Seguro**: `DatabaseSeederFakeSafe`
  - **Limpo**: `DatabaseSeederFakeClean`
  - **Final**: `DatabaseSeederFakeFinal`

### 6. **Limpeza de Cache**

- 🧹 Limpa todos os caches da aplicação:
  - Config cache
  - Route cache
  - View cache
  - Application cache

### 7. **Resumo Final**

- 📊 Mostra quantidade de registros em cada tabela
- ✅ Confirma sucesso da operação

## 🎯 Exemplos Práticos

### Desenvolvimento Inicial

```bash
# Primeira configuração do projeto
php artisan dev:reset --fake --final
```

### Teste de Funcionalidades

```bash
# Reset rápido para testar
php artisan dev:reset --fake --safe
```

### Debug de Problemas

```bash
# Reset limpo para investigar
php artisan dev:reset --fake --clean
```

### Desenvolvimento de Features Específicas

```bash
# Trabalhar apenas com clientes
php artisan dev:reset --fake --only=clients

# Trabalhar apenas com serviços
php artisan dev:reset --fake --only=services
```

### Apenas Recarregar Dados

```bash
# Manter estrutura, apenas recarregar dados
php artisan dev:reset --seed-only --fake
```

## ⚠️ Avisos Importantes

### 🔒 Segurança

- **NUNCA** execute em produção
- **SEMPRE** confirme antes de resetar
- **SEMPRE** faça backup se necessário

### 🗄️ Dados

- **TODOS** os dados serão perdidos
- **NÃO** há rollback automático
- **FAÇA** backup antes se necessário

### 🔧 Ambiente

- **APENAS** para desenvolvimento
- **VERIFIQUE** as variáveis de ambiente
- **TESTE** em ambiente isolado primeiro

## 🛠️ Troubleshooting

### Erro: "Command can only be run in development environment"

```bash
# Verifique o ambiente no .env
APP_ENV=local
```

### Erro: "Seeder not found"

```bash
# Verifique se os seeders existem
ls database/seeders/
```

### Erro: "Database connection failed"

```bash
# Verifique a conexão com o banco
php artisan migrate:status
```

### Erro: "Permission denied"

```bash
# Verifique permissões de escrita
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

## 📊 Seeders Disponíveis

### Seeders Básicos

- `RolePermissionSeeder` - Roles e permissões
- `ServiceStatusSeeder` - Status de serviços
- `PaymentMethodSeeder` - Métodos de pagamento
- `CategorySeeder` - Categorias de produtos
- `ServiceCenterSeeder` - Centros de serviço
- `UserSeeder` - Usuários básicos

### Seeders de Dados Fake

- `ClientFakeSeeder` - Clientes fake
- `VehicleFakeSeeder` - Veículos fake
- `ProductFakeSeeder` - Produtos fake
- `ServiceFakeSeeder` - Serviços fake
- `ServiceItemFakeSeeder` - Itens de serviço fake

### Seeders Compostos

- `DatabaseSeederFake` - Todos os dados fake
- `DatabaseSeederFakeSafe` - Versão segura
- `DatabaseSeederFakeClean` - Versão com limpeza
- `DatabaseSeederFakeFinal` - Versão final otimizada

## 🎉 Benefícios

- ⚡ **Rápido**: Reset completo em um comando
- 🔒 **Seguro**: Verificações de ambiente
- 🎭 **Flexível**: Múltiplas opções de dados fake
- 📊 **Informativo**: Resumo detalhado
- 🧹 **Limpo**: Limpeza automática de cache
- 🔧 **Robusto**: Tratamento de erros
