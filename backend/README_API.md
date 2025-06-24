# Rei do Óleo - Backend API

## 🚀 Tecnologias Utilizadas

- **Laravel 12** - Framework PHP
- **Laravel Sanctum** - Autenticação JWT
- **MySQL** - Banco de dados principal
- **Redis** - Cache e sessões
- **PHP 8.2+** - Linguagem de programação

## 📁 Estrutura do Projeto

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php      # Autenticação
│   │   │       └── UserController.php      # Usuários
│   │   └── Middleware/
│   │       └── ApiResponse.php            # Middleware para responses
│   ├── Models/
│   │   └── User.php                       # Model User com Sanctum
│   ├── Providers/
│   │   └── ApiServiceProvider.php         # Configurações da API
│   └── Traits/
│       └── ApiResponseTrait.php           # Trait para respostas padronizadas
├── config/
│   ├── api.php                           # Configurações específicas da API
│   ├── cors.php                          # Configurações CORS
│   └── sanctum.php                       # Configurações Sanctum
├── routes/
│   └── api.php                           # Rotas da API
├── .env.example                          # Variáveis de ambiente
└── README_API.md                         # Esta documentação
```

## ⚙️ Configuração Inicial

### 1. Variáveis de Ambiente (.env)

```env
# API Configuration
API_VERSION=v1
API_PREFIX=api

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=rei_do_oleo_dev
DB_USERNAME=rei_do_oleo_user
DB_PASSWORD=rei_do_oleo_password

# Redis
REDIS_CLIENT=predis
REDIS_HOST=redis
REDIS_PORT=6379

# CORS
CORS_ALLOWED_ORIGINS="http://localhost:3000,http://localhost:5173"

# JWT & Sanctum
JWT_SECRET=rei_do_oleo_jwt_secret_key_2024
JWT_TTL=1440
JWT_REFRESH_TTL=20160

# Rate Limiting
RATE_LIMIT_PER_MINUTE=60
RATE_LIMIT_LOGIN_PER_MINUTE=5
```

### 2. Comandos de Instalação

```bash
# Instalar dependências
composer install

# Configurar environment
cp .env.example .env
php artisan key:generate

# Executar migrations
php artisan migrate

# Iniciar servidor
php artisan serve
```

## 📋 Endpoints da API

### Base URL
```
http://localhost:8000/api/v1
```

### Health Check
```http
GET /health
```

### 🔐 Autenticação

#### Registro
```http
POST /auth/register
Content-Type: application/json

{
    "name": "Nome do Usuário",
    "email": "usuario@email.com",
    "password": "senha123",
    "password_confirmation": "senha123"
}
```

#### Login
```http
POST /auth/login
Content-Type: application/json

{
    "email": "usuario@email.com",
    "password": "senha123"
}
```

#### Logout
```http
POST /auth/logout
Authorization: Bearer {token}
```

#### Obter Perfil
```http
GET /auth/me
Authorization: Bearer {token}
```

#### Refresh Token
```http
POST /auth/refresh
Authorization: Bearer {token}
```

## 🔒 Segurança

### Rate Limiting
- **API Geral**: 60 requests/minuto por usuário/IP
- **Login**: 5 tentativas/minuto por IP
- **Verificação de email**: 6 requests/minuto

### CORS
- Configurado para aceitar requests de:
  - `http://localhost:3000` (React)
  - `http://localhost:5173` (Vite)

## 📊 Padrão de Respostas

### Sucesso
```json
{
    "status": "success",
    "message": "Operação realizada com sucesso",
    "data": {
        // Dados retornados
    },
    "timestamp": "2024-01-15T10:30:00.000Z",
    "version": "v1"
}
```

### Erro
```json
{
    "status": "error",
    "message": "Descrição do erro",
    "code": 400,
    "errors": {
        // Detalhes dos erros (se houver)
    },
    "timestamp": "2024-01-15T10:30:00.000Z",
    "version": "v1"
}
```

## 🚀 Próximos Passos

1. **Implementar endpoints de negócio**:
   - Produtos (`/products`)
   - Pedidos (`/orders`)
   - Clientes (`/customers`)
   - Relatórios (`/reports`)

2. **Funcionalidades adicionais**:
   - Upload de arquivos
   - Notificações
   - Auditoria
   - Webhooks
