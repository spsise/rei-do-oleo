# 📚 Documentação Swagger - Rei do Óleo API

## 🚀 Visão Geral

### Objetivo

Documentação completa da API Rei do Óleo, fornecendo uma referência abrangente para desenvolvedores frontend, integradores e equipe técnica.

### Características Principais

- **Versão da API**: 1.0.0
- **Tecnologias**: Laravel 12, Laravel Sanctum, OpenAPI 3.0
- **Cobertura**: 100% dos endpoints documentados

## 🔐 Autenticação

### Sistema de Autenticação

- **Método**: Laravel Sanctum (Bearer Token)
- **Endpoints de Autenticação**:
  - Registro de usuário
  - Login
  - Logout
  - Renovação de token
  - Recuperação de senha

### Como Autenticar

1. Faça login em `/api/v1/auth/login`
2. Copie o token retornado
3. No Swagger UI, clique em "Authorize"
4. Insira: `Bearer {seu_token}`

## 📊 Estatísticas da Documentação

### Cobertura de Endpoints

| Controlador             | Métodos Documentados | Status      | Cobertura |
| ----------------------- | -------------------- | ----------- | --------- |
| AuthController          | 8/8                  | ✅ Completo | 100%      |
| CategoryController      | 6/6                  | ✅ Completo | 100%      |
| ClientController        | 7/7                  | ✅ Completo | 100%      |
| ProductController       | 8/8                  | ✅ Completo | 100%      |
| VehicleController       | 6/6                  | ✅ Completo | 100%      |
| ServiceController       | 10/10                | ✅ Completo | 100%      |
| ServiceCenterController | 8/8                  | ✅ Completo | 100%      |
| ServiceItemController   | 7/7                  | ✅ Completo | 100%      |
| UserController          | 6/6                  | ✅ Completo | 100%      |

### Total de Endpoints

- **Endpoints Documentados**: 66
- **Cobertura Total**: 100%

## 🔍 Endpoints Detalhados

### 1. Autenticação (`/api/v1/auth`)

#### Registro de Usuário

- **Endpoint**: `POST /api/v1/auth/register`
- **Descrição**: Registra um novo usuário no sistema
- **Parâmetros**:
  - `name`: Nome completo (obrigatório)
  - `email`: E-mail válido (único)
  - `password`: Senha (mínimo 8 caracteres)
  - `role`: Função do usuário

#### Login

- **Endpoint**: `POST /api/v1/auth/login`
- **Descrição**: Autentica usuário e gera token de acesso
- **Parâmetros**:
  - `email`: E-mail do usuário
  - `password`: Senha

#### Logout

- **Endpoint**: `POST /api/v1/auth/logout`
- **Descrição**: Invalida o token de acesso atual

### 2. Clientes (`/api/v1/clients`)

#### Listar Clientes

- **Endpoint**: `GET /api/v1/clients`
- **Descrição**: Recupera lista de clientes com paginação
- **Filtros**:
  - `page`: Número da página
  - `per_page`: Itens por página
  - `search`: Termo de busca

#### Criar Cliente

- **Endpoint**: `POST /api/v1/clients`
- **Descrição**: Cadastra um novo cliente
- **Parâmetros**:
  - `name`: Nome completo
  - `document`: CPF/CNPJ
  - `email`: E-mail
  - `phone`: Telefone

### 3. Veículos (`/api/v1/vehicles`)

#### Buscar Veículo por Placa

- **Endpoint**: `POST /api/v1/vehicles/search/license-plate`
- **Descrição**: Encontra veículo específico pela placa
- **Parâmetros**:
  - `license_plate`: Placa do veículo

#### Listar Veículos de Cliente

- **Endpoint**: `GET /api/v1/vehicles/client/{clientId}`
- **Descrição**: Recupera todos os veículos de um cliente

### 4. Serviços (`/api/v1/services`)

#### Busca de Serviços

- **Endpoint**: `GET /api/v1/services`
- **Descrição**: Lista serviços com filtros avançados
- **Filtros**:
  - `status`: Status do serviço
  - `service_center_id`: Centro de serviço
  - `client_id`: Cliente
  - `date_start`: Data inicial
  - `date_end`: Data final

#### Serviços por Cliente

- **Endpoint**: `GET /api/v1/services/client/{clientId}`
- **Descrição**: Recupera todos os serviços de um cliente

### 5. Usuários (`/api/v1/users`)

#### Alterar Senha

- **Endpoint**: `PUT /api/v1/users/{id}/change-password`
- **Descrição**: Permite usuário alterar sua própria senha
- **Parâmetros**:
  - `current_password`: Senha atual
  - `new_password`: Nova senha
  - `new_password_confirmation`: Confirmação da nova senha

## 🛠️ Configurações Técnicas

### Swagger UI

- **URL**: `http://localhost:8000/api/documentation`
- **Versão**: 1.0.0
- **Autenticação**: Laravel Sanctum

### Comandos Úteis

```bash
# Regenerar documentação
php artisan l5-swagger:generate

# Validar documentação
php artisan l5-swagger:validate
```

## 🔒 Segurança

### Práticas de Segurança

- Autenticação via tokens
- Validação rigorosa de entrada
- Controle de acesso por função
- Proteção contra injeção de dados

### Níveis de Acesso

- **Admin**: Acesso completo
- **Manager**: Acesso parcial
- **Technician**: Acesso limitado
- **Attendant**: Acesso restrito

## 🚀 Próximos Passos

1. Manter documentação atualizada
2. Adicionar exemplos de erros mais detalhados
3. Implementar documentação de webhooks
4. Criar guia de integração
5. Configurar rate limiting

## 🎉 Conclusão

A documentação Swagger da API Rei do Óleo está **100% completa**, oferecendo:

- Interface intuitiva
- Exemplos realistas
- Documentação profissional
- Fácil integração

**Status**: ✅ Pronto para Produção
**Última Atualização**: $(date +%Y-%m-%d)

---

**Desenvolvido com ❤️ pela equipe Rei do Óleo**
