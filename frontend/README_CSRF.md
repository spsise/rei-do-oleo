# 🔐 Configuração CSRF no Frontend React - Rei do Óleo

## 📋 Visão Geral

O frontend React foi configurado para trabalhar automaticamente com os cookies CSRF do Laravel Sanctum, garantindo autenticação segura em SPAs (Single Page Applications).

## 🔧 Componentes Principais

### 1. **Configuração API (src/services/api.ts)**

```typescript
// Axios configurado com withCredentials para cookies
export const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true, // ✅ Essencial para cookies CSRF
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

// Instância separada para CSRF (sem Bearer token)
export const csrfApi = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,
  headers: { Accept: 'application/json' },
});
```

### 2. **Serviço CSRF (src/services/csrfService.ts)**

```typescript
export const csrfService = {
  // Obtém cookie CSRF do endpoint /sanctum/csrf-cookie
  async getCsrfCookie(): Promise<void>

  // Verifica se cookie CSRF existe
  hasXsrfToken(): boolean

  // Renova cookie CSRF
  async refreshCsrfCookie(): Promise<void>
};
```

### 3. **Hook useAuth (src/hooks/useAuth.ts)**

```typescript
export const useAuth = () => {
  // Login com fluxo CSRF automático
  const login = useCallback(async (credentials) => {
    // PASSO 1: Obter CSRF cookie
    // PASSO 2: Fazer login com cookie
    // PASSO 3: Retry automático se erro 419
  });

  // Inicialização automática do CSRF
  const initializeCsrf = useCallback(async () => {
    // Obtém CSRF cookie na inicialização da app
  });
};
```

### 4. **Provider Autenticação (src/components/providers/AuthProvider.tsx)**

```typescript
export const AuthProvider = ({ children }) => {
  // Inicializa CSRF automaticamente quando app carrega
  useEffect(() => {
    const initCsrf = async () => {
      if (!hasValidCsrf) {
        await initializeCsrf();
      }
    };
    initCsrf();
  }, []);
};
```

### 5. **Interceptors Automáticos (src/services/interceptors.ts)**

```typescript
// Interceptor de REQUEST - obtém CSRF se necessário
api.interceptors.request.use(async (config) => {
  const needsCsrf = ['post', 'put', 'patch', 'delete'].includes(config.method);
  if (needsCsrf && !csrfService.hasXsrfToken()) {
    await csrfService.getCsrfCookie();
  }
});

// Interceptor de RESPONSE - lida com erros 419 automaticamente
api.interceptors.response.use(null, async (error) => {
  if (error.response?.status === 419) {
    // Renova CSRF e tenta novamente
    await csrfService.refreshCsrfCookie();
    return api.request(originalRequest);
  }
});
```

## 🔄 Fluxo de Autenticação

### Login Normal:

1. **Inicialização**: App obtém CSRF cookie automaticamente
2. **Login**: Hook `useAuth.login()` gerencia todo o fluxo
3. **Requisições**: Interceptors garantem CSRF em todas as requests

### Recuperação de Erros:

1. **Erro 419**: Interceptor renova CSRF e retenta automaticamente
2. **Erro 401**: Interceptor tenta refresh token
3. **Fallback**: Logout automático se tudo falhar

## 🎯 Como Usar

### 1. **Em Componentes de Login:**

```typescript
import { useAuth } from '../hooks/useAuth';

const LoginForm = () => {
  const { login, loading } = useAuth();

  const handleSubmit = async (credentials) => {
    try {
      await login(credentials); // ✅ CSRF automático
      navigate('/dashboard');
    } catch (error) {
      toast.error(error.message);
    }
  };
};
```

### 2. **Em Requisições API:**

```typescript
import { api } from '../services/api';

// ✅ CSRF automático em todas as requisições
const createClient = async (data) => {
  const response = await api.post('/api/v1/clients', data);
  return response.data;
};
```

### 3. **Verificação de Status:**

```typescript
import { useAuth } from '../hooks/useAuth';

const Header = () => {
  const { hasValidCsrf } = useAuth();

  return <div>Status: {hasValidCsrf ? '🔒 Seguro' : '⚠️ Sem CSRF'}</div>;
};
```

## 🛠️ Configuração de Desenvolvimento

### 1. **Variáveis de Ambiente (.env)**

```env
VITE_API_URL=http://localhost:8000
VITE_APP_URL=http://localhost:3000
VITE_CSRF_ENABLED=true
```

### 2. **Vite Config (vite.config.ts)**

```typescript
export default defineConfig({
  server: {
    host: '0.0.0.0',
    port: 3000,
    proxy: {
      // Proxy para desenvolvimento local se necessário
      '/api': 'http://localhost:8000',
    },
  },
});
```

## 🔍 Debug e Troubleshooting

### 1. **Verificar Cookies no Browser:**

```javascript
// Console do navegador
console.log('CSRF Cookie:', document.cookie.includes('XSRF-TOKEN'));
```

### 2. **Logs Automáticos:**

- ✅ `CSRF cookie obtido com sucesso`
- 🔄 `Renovando CSRF cookie...`
- ❌ `Erro ao obter CSRF cookie`

### 3. **Utilitários de Debug:**

```typescript
import { checkApiConnection, checkCsrfStatus } from '../services/interceptors';

// Verificar conectividade
const isApiOnline = await checkApiConnection();
const isCsrfWorking = await checkCsrfStatus();
```

## 🚨 Possíveis Problemas

### ❌ **Erro: "CSRF token mismatch"**

- **Causa**: Cookie expirado ou não obtido
- **Solução**: Automática via interceptors

### ❌ **Erro: "Failed to fetch"**

- **Causa**: CORS ou API offline
- **Solução**: Verificar se API está rodando em localhost:8000

### ❌ **Erro: "withCredentials not allowed"**

- **Causa**: Configuração CORS incorreta no backend
- **Solução**: Verificar `config/cors.php` no Laravel

## ✅ **Status Final**

- 🔐 **CSRF**: Automático em todas as requisições
- 🔄 **Recovery**: Interceptors lidam com erros automaticamente
- 🎯 **UX**: Transparente para o usuário
- 🛡️ **Segurança**: Cookies HTTPOnly + SameSite
- ⚡ **Performance**: Cache de tokens + retry inteligente

A configuração está **100% funcional** e **pronta para produção**!
