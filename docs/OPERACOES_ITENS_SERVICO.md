# 🔧 Operações de Itens de Serviço

## 🎯 Visão Geral

Este documento explica o comportamento detalhado das três operações disponíveis para manipular itens de serviço: `replace`, `update` e `merge`.

---

## 📊 **Comportamento das Operações**

### **1. Operação `replace`** 🔄

#### **Comportamento:**

- ✅ **Remove TODOS** os itens existentes
- ✅ **Adiciona** os novos itens enviados
- ✅ **Operação atômica** (tudo ou nada)

#### **Cenário de Exemplo:**

```json
// Itens existentes: [1, 2, 3]
// Request:
{
  "items": {
    "operation": "replace",
    "data": [
      { "product_id": 4, "quantity": 2, "unit_price": 25.0 },
      { "product_id": 5, "quantity": 1, "unit_price": 50.0 }
    ]
  }
}
// Resultado: [4, 5] (itens 1, 2, 3 foram removidos)
```

#### **Quando Usar:**

- Edição completa do serviço
- Substituição total dos itens
- Sincronização com sistemas externos
- Correção de erros nos itens

---

### **2. Operação `update`** 🔧

#### **Comportamento Padrão (remove_unsent = false):**

- ✅ **Atualiza** itens existentes (com ID)
- ✅ **Atualiza** itens existentes (por product_id se não tiver ID)
- ✅ **Adiciona** novos itens (sem ID e sem product_id existente)
- ❌ **Mantém** itens existentes não enviados

#### **Comportamento com remove_unsent = true:**

- ✅ **Atualiza** itens existentes (com ID)
- ✅ **Adiciona** novos itens (sem ID)
- ✅ **Remove** itens existentes não enviados

#### **Cenários de Exemplo:**

##### **Cenário 1: Atualização + Adição (remove_unsent = false)**

```json
// Itens existentes: [1, 2, 3]
// Request:
{
  "items": {
    "operation": "update",
    "remove_unsent": false,
    "data": [
      { "id": 1, "quantity": 5 }, // Atualiza item 1
      { "product_id": 4, "quantity": 2 } // Adiciona item novo
    ]
  }
}
// Resultado: [1(atualizado), 2(mantido), 3(mantido), 4(novo)]
```

##### **Cenário 3: Atualização por product_id (remove_unsent = false)**

```json
// Itens existentes: [1(product_id=24), 2(product_id=32), 3(product_id=33)]
// Request:
{
  "items": {
    "operation": "update",
    "remove_unsent": false,
    "data": [
      { "product_id": 24, "quantity": 12, "unit_price": 150 }, // Atualiza item 1
      { "product_id": 32, "quantity": 3, "unit_price": 35 }, // Atualiza item 2
      { "product_id": 46, "quantity": 28, "unit_price": 167.85 } // Adiciona item novo
    ]
  }
}
// Resultado: [1(atualizado), 2(atualizado), 3(mantido), 4(novo)]
```

##### **Cenário 2: Atualização + Remoção (remove_unsent = true)**

```json
// Itens existentes: [1, 2, 3]
// Request:
{
  "items": {
    "operation": "update",
    "remove_unsent": true,
    "data": [
      { "id": 1, "quantity": 5 }, // Atualiza item 1
      { "product_id": 4, "quantity": 2 } // Adiciona item novo
    ]
  }
}
// Resultado: [1(atualizado), 4(novo)] (itens 2 e 3 foram removidos)
```

#### **Quando Usar:**

- **remove_unsent = false**: Edições parciais seguras
- **remove_unsent = true**: Edições parciais com limpeza

---

### **3. Operação `merge`** ➕

#### **Comportamento:**

- ✅ **Adiciona** novos itens
- ✅ **Mantém** todos os itens existentes
- ❌ **Nunca remove** itens existentes

#### **Cenário de Exemplo:**

```json
// Itens existentes: [1, 2, 3]
// Request:
{
  "items": {
    "operation": "merge",
    "data": [
      { "product_id": 4, "quantity": 2, "unit_price": 25.0 },
      { "product_id": 5, "quantity": 1, "unit_price": 50.0 }
    ]
  }
}
// Resultado: [1, 2, 3, 4, 5] (todos mantidos + novos adicionados)
```

#### **Quando Usar:**

- Adição de produtos extras
- Inclusão de promoções
- Complementação do serviço
- Adição de produtos cortesia

---

## 🎯 **Matriz de Decisão**

| Cenário                | Operação  | remove_unsent | Resultado                              |
| ---------------------- | --------- | ------------- | -------------------------------------- |
| **Substituição total** | `replace` | -             | Remove todos, adiciona novos           |
| **Edição segura**      | `update`  | `false`       | Atualiza/adiciona, mantém não enviados |
| **Edição com limpeza** | `update`  | `true`        | Atualiza/adiciona, remove não enviados |
| **Apenas adicionar**   | `merge`   | -             | Mantém todos, adiciona novos           |

---

## 💡 **Exemplos Práticos**

### **1. Edição Completa de Serviço**

```typescript
const editCompleteService = {
  service: {
    description: 'Troca de óleo e filtros atualizada',
    total_amount: 150.0,
  },
  items: {
    operation: 'replace',
    data: [
      { product_id: 1, quantity: 2, unit_price: 45.0 },
      { product_id: 2, quantity: 1, unit_price: 60.0 },
    ],
  },
};
```

### **2. Correção de Preços**

```typescript
const fixPrices = {
  items: {
    operation: 'update',
    remove_unsent: false,
    data: [
      { id: 1, unit_price: 50.0 }, // Corrige preço do item 1
      { id: 2, unit_price: 75.0 }, // Corrige preço do item 2
    ],
  },
};
```

### **3. Adição de Produtos Extras**

```typescript
const addExtras = {
  items: {
    operation: 'merge',
    data: [
      { product_id: 6, quantity: 1, unit_price: 15.0, notes: 'Aditivo' },
      { product_id: 7, quantity: 1, unit_price: 0.0, notes: 'Cortesia' },
    ],
  },
};
```

### **4. Limpeza de Itens Desnecessários**

```typescript
const cleanupItems = {
  items: {
    operation: 'update',
    remove_unsent: true,
    data: [
      { id: 1, quantity: 3 }, // Mantém apenas o item 1
      { product_id: 8, quantity: 1, unit_price: 20.0 }, // Adiciona novo
    ],
  },
};
```

---

## 🚨 **Problema de Constraint Única - SOLUCIONADO**

### **Problema Original:**

```
Duplicate entry '98-24' for key 'service_items.service_items_service_id_product_id_unique'
```

### **Causa:**

- Constraint única na tabela `service_items` para `service_id` + `product_id`
- Operação `update` tentava criar novos itens em vez de atualizar existentes
- Violação de integridade ao tentar inserir produto duplicado

### **Solução Implementada:**

- **Verificação inteligente** de itens existentes por `product_id`
- **Atualização automática** de itens existentes quando `product_id` já existe
- **Criação apenas** de itens realmente novos

### **Comportamento Atual:**

```php
// Para cada item sem ID:
if ($existingItemsByProductId->has($productId)) {
    // Atualiza item existente
    $existingItem = $existingItemsByProductId->get($productId);
    $this->serviceItemRepository->update($existingItem, $itemData);
} else {
    // Cria novo item
    $newItems[] = $itemData;
}
```

## 🚨 **Considerações Importantes**

### **Segurança:**

- **`replace`**: ⚠️ **Perda de dados** - remove todos os itens existentes
- **`update` (remove_unsent = false)**: ✅ **Seguro** - não remove dados
- **`update` (remove_unsent = true)**: ⚠️ **Cuidado** - pode remover dados não intencionalmente
- **`merge`**: ✅ **Mais seguro** - nunca remove dados

### **Performance:**

- **`replace`**: Mais rápido (uma operação de delete + bulk insert)
- **`update`**: Médio (operações individuais por item)
- **`merge`**: Mais lento (mantém todos + adiciona novos)

### **Consistência:**

- **Todas as operações** são atômicas (transação única)
- **Rollback automático** em caso de erro
- **Logging detalhado** de todas as operações

---

## 🔧 **Implementação Técnica**

### **Backend (Laravel):**

```php
// ServiceItemsOperationService.php
public function executeOperation(int $serviceId, array $itemsData): Collection
{
    $operation = $itemsData['operation'] ?? 'replace';
    $items = $itemsData['data'] ?? [];
    $removeUnsent = $itemsData['remove_unsent'] ?? false;

    return match ($operation) {
        'replace' => $this->replaceItems($serviceId, $items),
        'update' => $this->updateItems($serviceId, $items, $removeUnsent),
        'merge' => $this->mergeItems($serviceId, $items),
        default => throw new \InvalidArgumentException("Invalid operation: {$operation}")
    };
}
```

### **Frontend (React):**

```typescript
const useUpdateServiceWithItems = () => {
  return useMutation({
    mutationFn: ({
      serviceId,
      data,
    }: {
      serviceId: number;
      data: UpdateServiceWithItemsData;
    }) => serviceService.updateServiceWithItems(serviceId, data),
    onSuccess: (data, variables) => {
      queryClient.invalidateQueries(['services']);
      toast.success('Serviço atualizado com sucesso');
    },
  });
};
```

---

## 📋 **Checklist de Uso**

### **Antes de Escolher a Operação:**

- [ ] **Dados existentes** serão perdidos?
- [ ] **Performance** é crítica?
- [ ] **Segurança** é prioridade?
- [ ] **Flexibilidade** é necessária?

### **Para `replace`:**

- [ ] Confirmação do usuário para perda de dados
- [ ] Backup dos dados existentes (se necessário)
- [ ] Validação de todos os novos itens

### **Para `update`:**

- [ ] Decidir se `remove_unsent` será usado
- [ ] Validar IDs dos itens existentes
- [ ] Considerar impacto em itens não enviados

### **Para `merge`:**

- [ ] Verificar duplicação de produtos
- [ ] Validar preços e quantidades
- [ ] Considerar impacto no total do serviço

---

## 🎯 **Recomendações**

### **Padrão Recomendado:**

1. **Use `update` (remove_unsent = false)** como padrão
2. **Use `merge`** para adições simples
3. **Use `replace`** apenas quando necessário
4. **Use `update` (remove_unsent = true)** com cuidado

### **Para Desenvolvedores:**

- Sempre documente qual operação está sendo usada
- Implemente confirmações para operações destrutivas
- Adicione logging para auditoria
- Teste todos os cenários possíveis

### **Para Usuários:**

- Entenda o impacto de cada operação
- Use confirmações antes de operações destrutivas
- Mantenha backup de dados importantes
- Teste em ambiente de desenvolvimento primeiro

---

**📖 Para mais detalhes técnicos, consulte:**

- `docs/RESUMO_FLUXO_ATUALIZACAO.md` - Resumo do fluxo
- `docs/REFATORACAO_SERVICO_UNIFICADO.md` - Documentação da refatoração
