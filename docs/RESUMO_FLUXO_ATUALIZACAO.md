# 📋 Resumo Executivo - Fluxo de Atualização de Serviço

## 🎯 Visão Geral Rápida

O processo de atualização de serviço é **dividido em duas etapas** para garantir consistência:

1. **Atualização dos dados do serviço** (informações gerais)
2. **Atualização dos itens do serviço** (produtos/peças)

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
4. **Submissão 1**: Envia dados do serviço
5. **Submissão 2**: Envia dados dos itens (após 200ms)
6. **Cache**: Invalida cache e atualiza interface

---

## 📊 Campos Principais

### **Dados do Serviço (Primeira Requisição)**

```typescript
{
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
}
```

**Fluxo Completo:**

```
Dados do Serviço → EditServiceModal.tsx → useUpdateService.ts → service.service.ts →
PUT /api/services/{id} → ServiceController@update → ServiceService@update →
ServiceRepository@update → Database (services table)
```

### **Dados dos Itens (Segunda Requisição)**

```typescript
[
  {
    product_id: number,
    quantity: number,
    unit_price: number,
    discount: number,
    notes: string,
  },
];
```

**Fluxo Completo:**

```
Dados dos Itens → EditServiceModal.tsx → useUpdateService.ts → service.service.ts →
PUT /api/service-items/{id}/bulk-update → ServiceController@bulkUpdateItems →
ServiceService@bulkUpdateItems → ServiceRepository@bulkUpdateItems →
Database (service_items table)
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
- `useUpdateService.ts` - Hook de atualização
- `service.service.ts` - Serviço de API

### **Backend**

- `ServiceController.php` - Controller principal
- `ServiceRepository.php` - Repository
- `ServiceService.php` - Service layer
- `Service.php` - Model
- `UpdateServiceRequest.php` - Validação

---

## ⚡ Pontos Críticos

### **1. Cálculo de Totais**

- **Frontend**: Calcula em tempo real
- **Backend**: Recalcula baseado nos itens salvos
- **Sincronização**: Garantir consistência

### **2. Race Conditions**

- **Delay**: 200ms entre requisições
- **Transações**: Uso de DB::transaction
- **Cache**: Invalidação adequada

### **3. Validação**

- **Frontend**: Validação de tipos e campos
- **Backend**: Validação de regras de negócio
- **Database**: Constraints de integridade

---

## 🔧 Configurações Importantes

### **Timeouts**

```typescript
// Delay entre transações
await new Promise((resolve) => setTimeout(resolve, 200));
```

### **Validações**

```php
'quantity' => 'required|integer|min:1|max:999',
'unit_price' => 'required|numeric|min:0',
'discount' => 'nullable|numeric|min:0|max:100',
```

### **Cache**

```typescript
// Invalidar cache
queryClient.invalidateQueries({
  queryKey: [QUERY_KEYS.SERVICE, serviceId],
});
```

---

## ⚠️ Problemas Comuns

### **1. Inconsistência de Totais**

- **Causa**: Frontend e backend calculam diferentemente
- **Solução**: Backend sempre recalcula baseado nos itens

### **2. Race Conditions**

- **Causa**: Requisições simultâneas
- **Solução**: Delay entre requisições + transações

### **3. Cache Desatualizado**

- **Causa**: Cache não invalidado
- **Solução**: Invalidação adequada do React Query

---

## 📝 Checklist de Debug

### **Frontend**

- [ ] Dados carregados corretamente?
- [ ] Totais calculados em tempo real?
- [ ] Validação antes do envio?
- [ ] Cache invalidado?

### **Backend**

- [ ] Validação passou?
- [ ] Transação commitada?
- [ ] Totais recalculados?
- [ ] Logs de erro?

### **Database**

- [ ] Dados salvos corretamente?
- [ ] Constraints respeitadas?
- [ ] Relacionamentos intactos?

---

## 🚀 Melhorias Recomendadas

### **Performance**

- Implementar cache Redis
- Otimizar queries de banco
- Lazy loading de dados

### **Funcionalidades**

- Histórico de alterações
- Notificações em tempo real
- Backup automático

### **Segurança**

- Rate limiting
- Validação mais rigorosa
- Auditoria de mudanças

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

- `PUT /api/services/{id}` - Atualizar serviço
- `PUT /api/service-items/{id}/bulk-update` - Atualizar itens

### **Documentação Completa**

- `docs/FLUXO_ATUALIZACAO_SERVICO.md` - Documentação técnica
- `docs/DIAGRAMA_FLUXO_ATUALIZACAO.md` - Diagramas visuais

---

**📖 Este resumo deve ser consultado para entendimento rápido do fluxo. Para detalhes técnicos, consulte a documentação completa.**
