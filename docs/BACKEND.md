# 🔧 Backend - Sistema Rei do Óleo API

## 🚀 Tecnologias

- **Framework**: Laravel 12
- **Autenticação**: Laravel Sanctum (JWT)
- **Banco de Dados**: MySQL 8.0+
- **Cache**: Redis
- **PHP**: 8.2+

## 📁 Estrutura

```
backend/
├── app/
│   ├── Http/Controllers/Api/    # Controllers da API
│   ├── Models/                  # Models Eloquent
│   ├── Providers/              # Service Providers
│   ├── Traits/                 # Traits reutilizáveis
│   └── Middleware/             # Middlewares personalizados
├── config/
│   ├── api.php                 # Configurações da API
│   ├── cors.php                # CORS settings
│   └── sanctum.php             # JWT settings
├── routes/api.php              # Rotas da API
└── database/migrations/        # Migrações do banco
```

## 🔗 Endpoints Principais

### Base URL

```
http://localhost:8000/api/v1
```

### 🔐 Autenticação

- `POST /auth/register` - Cadastro
- `POST /auth/login` - Login
- `POST /auth/logout` - Logout
- `GET /auth/me` - Perfil do usuário
- `POST /auth/refresh` - Renovar token

### 👥 Usuários (Protegido)

- `GET /users` - Listar usuários
- `POST /users` - Criar usuário
- `GET /users/{id}` - Visualizar usuário
- `PUT /users/{id}` - Atualizar usuário
- `DELETE /users/{id}` - Deletar usuário

### 🔧 Monitoramento

- `GET /health` - Status da API

## ⚙️ Configuração

### Variáveis de Ambiente (.env)

```env
# API
API_VERSION=v1
JWT_TTL=1440

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=rei_do_oleo_dev
DB_USERNAME=rei_do_oleo_user
DB_PASSWORD=senha_segura

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# CORS
CORS_ALLOWED_ORIGINS="http://localhost:3000,http://localhost:5173"

# Rate Limiting
RATE_LIMIT_PER_MINUTE=60
RATE_LIMIT_LOGIN_PER_MINUTE=5
```

### Comandos de Setup

```bash
cd backend

# Instalar dependências
composer install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Executar migrações
php artisan migrate

# Iniciar servidor
php artisan serve
```

## 🔒 Segurança

### Rate Limiting

- **API geral**: 60 req/min por usuário
- **Login**: 5 tentativas/min por IP
- **Email verification**: 6 req/min

### Headers de Segurança

- CORS configurado para frontend
- Headers de resposta padronizados
- Validação rigorosa de entrada

### Autenticação JWT

- Tokens com expiração configurável
- Revogação de tokens no logout
- Suporte a múltiplos dispositivos

## 📊 Padrão de Resposta

### Sucesso

```json
{
    "status": "success",
    "message": "Operação realizada com sucesso",
    "data": { ... },
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
    "errors": { ... },
    "timestamp": "2024-01-15T10:30:00.000Z",
    "version": "v1"
}
```

## 🛠️ Desenvolvimento

### Testes

```bash
php artisan test
```

### Cache e Otimização

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Debugging

```bash
php artisan tinker
php artisan route:list
```

## 📈 Próximas Implementações

### Endpoints de Negócio

- `POST /products` - Cadastro de produtos
- `POST /orders` - Gestão de pedidos
- `POST /customers` - Gestão de clientes
- `GET /reports` - Relatórios

### Funcionalidades

- Upload de arquivos
- Notificações em tempo real
- Auditoria de ações
- Integração com APIs externas

### Performance

- Query optimization
- Cache strategies
- Database indexing
- API versioning

## 🐛 Troubleshooting

### Problemas Comuns

**Token inválido**

```bash
# Verificar se o header Authorization está correto
Authorization: Bearer {seu_token}
```

**CORS Error**

```bash
# Verificar configurações em config/cors.php
CORS_ALLOWED_ORIGINS="http://localhost:3000"
```

**Rate Limit**

```bash
# Aguardar o tempo especificado ou ajustar em .env
RATE_LIMIT_PER_MINUTE=100
```

### Logs

```bash
tail -f storage/logs/laravel.log
```

## 📚 Recursos

- [Laravel Documentation](https://laravel.com/docs)
- [Sanctum Documentation](https://laravel.com/docs/sanctum)
- [API Testing com Postman](postman_collection.json)

---

> **Nota**: Este backend segue as melhores práticas Laravel Senior e está pronto para integração com o frontend React.
