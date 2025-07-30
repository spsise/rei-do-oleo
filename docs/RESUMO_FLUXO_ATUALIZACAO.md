# 📋 Resumo Executivo - Fluxo de Atualização de Serviço

## 🎯 Visão Geral Rápida

O processo de atualização de serviço foi **refatorado para usar uma única requisição** seguindo os princípios SOLID e melhores práticas:

1. **Atualização unificada** - Serviço e itens em uma única transação
2. **Flags de operação** - Controle granular sobre operações de itens
3. **Atomicidade garantida** - Transação única no banco de dados

---

## 🔄 Fluxo Simplificado

```
Usuário → Frontend → Backend → Database
   ↓         ↓         ↓         ↓
Edita    Calcula   Valida   Salva
serviço   totais    dados    dados
```

### **Diagrama do Fluxo Completo**

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   FRONTEND      │    │    BACKEND      │    │   DATABASE      │    │     CACHE       │
│                 │    │                 │    │                 │    │                 │
│ EditServiceModal│───▶│ ServiceController│───▶│ services table  │    │                 │
│ (Dados Serviço) │    │ @update         │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │                       │
         │                       │                       │                       │
         ▼                       ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ useUpdateService│    │ ServiceService  │    │ service_items   │    │ React Query     │
│ (Hook)          │    │ @update         │    │ table           │    │ Cache           │
└─────────────────┘    └─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │                       │
         │                       │                       │                       │
         ▼                       ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ service.service │    │ ServiceRepository│    │ products table  │    │ Invalidation    │
│ (API Client)    │    │ @update         │    │                 │    │ & Refresh       │
└─────────────────┘    └─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │                       │
         │                       │                       │                       │
         ▼                       ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ HTTP Request    │    │ DB Transaction  │    │ Foreign Keys    │    │ UI Update       │
│ PUT /api/services│    │ & Validation    │    │ & Constraints   │    │ & Re-render     │
└─────────────────┘    └─────────────────┘    └─────────────────┘    └─────────────────┘

                    ┌─────────────────────────────────────────────────────────────────┐
                    │                    SEGUNDA REQUISIÇÃO (200ms delay)            │
                    └─────────────────────────────────────────────────────────────────┘

┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ EditServiceModal│───▶│ ServiceController│───▶│ service_items   │    │                 │
│ (Dados Itens)   │    │ @bulkUpdateItems│    │ table           │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │                       │
         │                       │                       │                       │
         ▼                       ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ useUpdateService│    │ ServiceService  │    │ Bulk Update     │    │ Final Cache     │
│ (Hook)          │    │ @bulkUpdateItems│    │ Delete + Insert │    │ Invalidation    │
└─────────────────┘    └─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │                       │
         │                       │                       │                       │
         ▼                       ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ service.service │    │ ServiceRepository│    │ Recalculate     │    │ UI Refresh      │
│ (API Client)    │    │ @bulkUpdateItems│    │ Service Totals  │    │ & Success Msg   │
└─────────────────┘    └─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │                       │
         │                       │                       │                       │
         ▼                       ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ HTTP Request    │    │ DB Transaction  │    │ Update services │    │ Modal Close     │
│ PUT /api/service-│    │ & Validation    │    │ table totals    │    │ & Navigation    │
│ items/bulk-update│    │                 │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘    └─────────────────┘
```

### **Etapas Detalhadas:**

1. **Inicialização**: Carrega dados do serviço no modal
2. **Manipulação**: Usuário altera quantidades/preços
3. **Cálculo**: Frontend recalcula totais automaticamente
4. **Preparação**: Estrutura dados com flags de operação
5. **Submissão**: Envia dados unificados em uma requisição
6. **Cache**: Invalida cache e atualiza interface

---

## 📊 Campos Principais

### **Estrutura Unificada (Nova Implementação)**

```typescript
{
  service: {
  vehicle_id: number,
  description: string,
  estimated_duration: number,
  scheduled_at: string,
  mileage_at_service: number,
  observations: string,
  internal_notes: string,
  discount: number,
  total_amount: number,
  final_amount: number
  },
  items: {
    operation: 'replace' | 'update' | 'merge',
    data: [
  {
    product_id: number,
    quantity: number,
    unit_price: number,
    discount: number,
    notes: string,
      }
    ]
  }
}
```

**Fluxo Completo:**

```
Dados Unificados → EditServiceModal.tsx → useUpdateServiceWithItems.ts → service.service.ts →
PUT /api/services/{id} → ServiceController@update → UpdateServiceWithItemsAction@execute →
ServiceItemsOperationService@executeOperation → Database (services + service_items tables)
```

---

## 🔄 Detalhamento do Fluxo por Etapa

### **Primeira Requisição - Dados do Serviço**

1. **Frontend - EditServiceModal.tsx**

   - Usuário edita campos do serviço
   - Validação de formulário em tempo real
   - Cálculo automático de totais

2. **Frontend - useUpdateService.ts**

   - Hook customizado gerencia estado
   - Prepara dados para envio
   - Controla loading states

3. **Frontend - service.service.ts**

   - Serviço de API faz requisição HTTP
   - Headers de autenticação
   - Tratamento de erros

4. **Backend - PUT /api/services/{id}**

   - Rota da API recebe requisição
   - Middleware de autenticação
   - Validação de entrada

5. **Backend - ServiceController@update**

   - Controller processa requisição
   - Valida dados com UpdateServiceRequest
   - Chama ServiceService

6. **Backend - ServiceService@update**

   - Lógica de negócio
   - Transação de banco de dados
   - Eventos e notificações

7. **Backend - ServiceRepository@update**

   - Acesso ao banco de dados
   - Query builder
   - Relacionamentos

8. **Database - services table**
   - Tabela principal do serviço
   - Constraints e índices
   - Dados persistidos

### **Segunda Requisição - Dados dos Itens**

1. **Frontend - EditServiceModal.tsx**

   - Dados dos itens preparados
   - Delay de 200ms após primeira requisição
   - Validação de produtos e quantidades

2. **Frontend - useUpdateService.ts**

   - Hook gerencia segunda requisição
   - Dependência da primeira requisição
   - Cache invalidation

3. **Frontend - service.service.ts**

   - Requisição para bulk update
   - Dados dos itens em array
   - Headers e autenticação

4. **Backend - PUT /api/service-items/{id}/bulk-update**

   - Rota específica para itens
   - Validação de array de itens
   - Middleware de autorização

5. **Backend - ServiceController@bulkUpdateItems**

   - Controller processa itens
   - Validação com UpdateServiceItemsRequest
   - Chama ServiceService

6. **Backend - ServiceService@bulkUpdateItems**

   - Lógica de negócio para itens
   - Transação para múltiplos itens
   - Recalcula totais do serviço

7. **Backend - ServiceRepository@bulkUpdateItems**

   - Bulk update no banco
   - Delete e insert de itens
   - Relacionamentos com produtos

8. **Database - service_items table**
   - Tabela de itens do serviço
   - Foreign keys para service_id e product_id
   - Dados de quantidade e preço

---

## 🛠️ Arquivos Principais

### **Frontend**

- `EditServiceModal.tsx` - Modal de edição
- `Technician.tsx` - Página principal
- `useUpdateServiceWithItems.ts` - Hook de atualização unificada
- `service.service.ts` - Serviço de API

### **Backend**

- `ServiceController.php` - Controller principal
- `UpdateServiceWithItemsAction.php` - Action para atualização unificada
- `ServiceItemsOperationService.php` - Service para operações de itens
- `UpdateServiceWithItemsRequest.php` - Validação unificada
- `ServiceRepository.php` - Repository
- `ServiceService.php` - Service layer
- `Service.php` - Model

---

## ⚡ Pontos Críticos

### **1. Cálculo de Totais**

- **Frontend**: Calcula em tempo real
- **Backend**: Recalcula baseado nos itens salvos
- **Sincronização**: Garantida pela transação única

### **2. Atomicidade**

- **Transação Única**: DB::transaction para serviço e itens
- **Rollback Automático**: Em caso de erro, tudo é revertido
- **Consistência**: Dados sempre consistentes

### **3. Validação**

- **Frontend**: Validação de tipos e campos
- **Backend**: Validação unificada com flags de operação
- **Database**: Constraints de integridade

---

## 🔧 Configurações Importantes

### **Flags de Operação**

```typescript
// Operações disponíveis para itens
type ItemOperation = 'replace' | 'update' | 'merge';

// Exemplo de uso
const itemsData = {
  operation: 'replace' as const,
  data: [{ product_id: 1, quantity: 2, unit_price: 50.0 }],
};
```

### **Validações**

```php
// Validação unificada
'service' => 'required|array',
'items.operation' => 'required|string|in:replace,update,merge',
'items.data.*.product_id' => 'required|integer|exists:products,id',
'items.data.*.quantity' => 'required|integer|min:1|max:999',
```

### **Cache**

```typescript
// Invalidar cache unificado
queryClient.invalidateQueries({
  queryKey: [QUERY_KEYS.SERVICE, serviceId],
});
queryClient.invalidateQueries({
  queryKey: ['technician', 'search'],
});
```

---

## ⚠️ Problemas Comuns

### **1. Inconsistência de Totais**

- **Causa**: Frontend e backend calculam diferentemente
- **Solução**: Backend sempre recalcula baseado nos itens

### **2. Operações de Itens**

- **Causa**: Operação inválida ou dados malformados
- **Solução**: Validação rigorosa das flags de operação

### **3. Cache Desatualizado**

- **Causa**: Cache não invalidado
- **Solução**: Invalidação adequada do React Query

---

## 📝 Checklist de Debug

### **Frontend**

- [ ] Dados carregados corretamente?
- [ ] Totais calculados em tempo real?
- [ ] Estrutura unificada preparada?
- [ ] Cache invalidado?

### **Backend**

- [ ] Validação unificada passou?
- [ ] Transação única commitada?
- [ ] Operação de itens executada?
- [ ] Totais recalculados?
- [ ] Logs de erro?

### **Database**

- [ ] Dados salvos corretamente?
- [ ] Constraints respeitadas?
- [ ] Relacionamentos intactos?
- [ ] Atomicidade garantida?

---

## 🚀 Melhorias Recomendadas

### **Performance**

- ✅ **Redução de 50%** no número de requisições HTTP
- ✅ **Transação única** elimina overhead de múltiplas operações
- ✅ **Cache otimizado** com invalidação unificada

### **Funcionalidades**

- ✅ **Flags de operação** para controle granular
- ✅ **Atomicidade garantida** com rollback automático
- ✅ **Validação unificada** mais robusta

### **Segurança**

- ✅ **Rate limiting** reduzido (menos requisições)
- ✅ **Validação rigorosa** com flags de operação
- ✅ **Auditoria de mudanças** em transação única

---

## 📞 Suporte

### **Logs Importantes**

```bash
# Frontend
console.log('Dados enviados:', submitData);

# Backend
Log::info('Service updated', ['id' => $id, 'data' => $data]);
```

### **Endpoints**

- `PUT /api/services/{id}` - Atualizar serviço com itens (nova implementação)

### **Documentação Completa**

- `docs/FLUXO_ATUALIZACAO_SERVICO.md` - Documentação técnica
- `docs/DIAGRAMA_FLUXO_ATUALIZACAO.md` - Diagramas visuais

### **Princípios SOLID Aplicados**

- **S**: Single Responsibility - Cada classe tem uma responsabilidade específica
- **O**: Open/Closed - Extensível para novas operações sem modificar código existente
- **L**: Liskov Substitution - Interfaces bem definidas
- **I**: Interface Segregation - Interfaces específicas para cada operação
- **D**: Dependency Inversion - Dependências injetadas via construtor

---

**📖 Este resumo deve ser consultado para entendimento rápido do fluxo. Para detalhes técnicos, consulte a documentação completa.**
