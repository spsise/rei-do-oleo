# 🚀 Frontend - Rei do Óleo

Frontend React + TypeScript + Tailwind CSS integrado com API Laravel para o sistema Rei do Óleo.

## ✨ Características

- **React 19** com TypeScript
- **Tailwind CSS 3.x** para estilização
- **React Query** para gerenciamento de estado da API
- **React Router** para navegação
- **React Hot Toast** para notificações
- **Heroicons** para ícones
- **Design responsivo** baseado no TailAdmin
- **Autenticação** com Laravel Sanctum
- **Layout moderno** com sidebar e header

## 🛠️ Tecnologias

- React 19.1.0
- TypeScript 5.8.3
- Tailwind CSS 3.4.0
- Vite 7.0.1
- React Query 5.81.5
- React Router 6.30.1
- Axios 1.10.0

## 🚀 Instalação

1. **Instalar dependências:**

   ```bash
   npm install
   ```

2. **Configurar API:**
   - Edite `src/config/api.ts` para apontar para sua API Laravel
   - URL padrão: `http://localhost:8000/api/v1`

3. **Iniciar desenvolvimento:**

   ```bash
   npm run dev
   ```

4. **Acessar aplicação:**
   - URL: http://localhost:5173

## 📁 Estrutura do Projeto

```
src/
├── components/
│   └── layout/
│       ├── DashboardLayout.tsx  # Layout principal
│       ├── Header.tsx           # Header com navegação
│       └── Sidebar.tsx          # Sidebar com menu
├── contexts/
│   └── AuthContext.tsx          # Contexto de autenticação
├── pages/
│   ├── Login.tsx                # Página de login
│   └── Dashboard.tsx            # Dashboard principal
├── services/
│   └── api.ts                   # Serviços da API
├── config/
│   └── api.ts                   # Configuração da API
└── App.tsx                      # Componente principal
```

## 🔐 Autenticação

O sistema usa **Laravel Sanctum** para autenticação:

- **Login:** `/api/v1/auth/login`
- **Registro:** `/api/v1/auth/register`
- **Logout:** `/api/v1/auth/logout`
- **Perfil:** `/api/v1/auth/me`

### Fluxo de Autenticação

1. Usuário faz login com email/senha
2. API retorna token Bearer
3. Token é armazenado no localStorage
4. Token é enviado em todas as requisições
5. Se token expirar, usuário é redirecionado para login

## 🎨 Design System

### Cores Principais

- **Brand:** `#465fff` (azul principal)
- **Primary:** `#3b82f6` (azul secundário)
- **Gray:** Tons de cinza para textos e bordas
- **Success:** `#10B981` (verde)
- **Error:** `#EF4444` (vermelho)
- **Warning:** `#F59E0B` (amarelo)

### Componentes

- **Cards:** Para estatísticas e informações
- **Buttons:** Primário, secundário e outline
- **Forms:** Inputs, selects e validação
- **Tables:** Para listagens de dados
- **Modals:** Para ações importantes

## 📊 Dashboard

O dashboard inclui:

- **Cards de estatísticas** (clientes, veículos, serviços, receita)
- **Gráficos** de performance
- **Serviços recentes** com status
- **Ações rápidas** para tarefas comuns
- **Notificações** em tempo real

## 🔧 Configuração da API

### Endpoints Principais

- **Dashboard Stats:** `GET /api/v1/services/dashboard/stats`
- **Clientes:** `GET /api/v1/clients`
- **Veículos:** `GET /api/v1/vehicles`
- **Serviços:** `GET /api/v1/services`
- **Produtos:** `GET /api/v1/products`

### Configuração

Edite `src/config/api.ts`:

```typescript
export const API_CONFIG = {
  BASE_URL: 'http://localhost:8000/api/v1',
  TIMEOUT: 10000,
  HEADERS: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
};
```

## 🚀 Deploy

### Build de Produção

```bash
npm run build
```

### Preview

```bash
npm run preview
```

## 📱 Responsividade

O sistema é totalmente responsivo:

- **Mobile:** Menu hambúrguer, cards empilhados
- **Tablet:** Layout adaptativo
- **Desktop:** Sidebar fixa, layout completo

## 🔄 Integração com TailAdmin

Este projeto foi baseado no design do **TailAdmin (free)** com:

- ✅ Layout responsivo
- ✅ Componentes modernos
- ✅ Cores e tipografia consistentes
- ✅ Ícones Heroicons
- ✅ Animações suaves

## 🐛 Troubleshooting

### Erro de CORS

Se houver erro de CORS, configure o Laravel:

```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### Token Expirado

O sistema automaticamente:

- Detecta token expirado (401)
- Limpa dados locais
- Redireciona para login

## 📝 Licença

Este projeto é parte do sistema Rei do Óleo.

---

**Desenvolvido com ❤️ usando React + Laravel**
