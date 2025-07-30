# 🔄 Fluxo de Atualização de Serviço - Documentação Completa

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Arquitetura do Sistema](#arquitetura-do-sistema)
3. [Fluxo Frontend](#fluxo-frontend)
4. [Fluxo Backend](#fluxo-backend)
5. [Campos e Dados](#campos-e-dados)
6. [Processo de Validação](#processo-de-validação)
7. [Cache e Sincronização](#cache-e-sincronização)
8. [Tratamento de Erros](#tratamento-de-erros)
9. [Exemplos Práticos](#exemplos-práticos)

---

## 🎯 Visão Geral

O processo de atualização de serviço é dividido em duas etapas principais:

1. **Atualização dos dados do serviço** (informações gerais)
2. **Atualização dos itens do serviço** (produtos/peças)

Este fluxo garante consistência dos dados e permite atualizações granulares.

---

## 🏗️ Arquitetura do Sistema

```
┌─────────────────┐    HTTP PUT     ┌─────────────────┐    Database    ┌─────────────────┐
│   Frontend      │ ──────────────► │   Backend       │ ──────────────► │   Database      │
│   (React)       │                 │   (Laravel)     │                 │   (MySQL)       │
└─────────────────┘                 └─────────────────┘                 └─────────────────┘
        │                                   │                                   │
        │                                   │                                   │
        ▼                                   ▼                                   ▼
┌─────────────────┐                 ┌─────────────────┐                 ┌─────────────────┐
│ EditServiceModal│                 │ ServiceController│                 │ services table  │
│ TechnicianPage  │                 │ ServiceRepository│                 │ service_items   │
│ useUpdateService│                 │ ServiceService   │                 │ products table  │
└─────────────────┘                 └─────────────────┘                 └─────────────────┘
```

---

## ⚛️ Fluxo Frontend

### 1. **Inicialização do Modal de Edição**

**Arquivo**: `frontend/src/components/Technician/EditServiceModal.tsx`

```typescript
// Dados iniciais carregados do serviço
const originalServiceData = useMemo(() => {
  if (!service) return {};

  const completeService = serviceDetails || service;

  return {
    client_id: 0,
    vehicle_id: vehicles.length > 0 ? vehicles[0].id : 0,
    service_center_id: isCompleteService(completeService)
      ? completeService.service_center?.id || 1
      : 1,
    technician_id: isCompleteService(completeService)
      ? completeService.technician?.id || 1
      : 1,
    attendant_id: isCompleteService(completeService)
      ? completeService.attendant?.id || 1
      : 1,
    service_number: service.service_number,
    description: service.description || '',
    estimated_duration: 60,
    scheduled_at: isCompleteService(completeService)
      ? completeService.scheduled_date
      : undefined,
    started_at: isCompleteService(completeService)
      ? completeService.started_at
      : undefined,
    completed_at: isCompleteService(completeService)
      ? completeService.finished_at
      : undefined,
    service_status_id: isCompleteService(completeService)
      ? completeService.status?.id || 1
      : 1,
    payment_method_id: isCompleteService(completeService)
      ? completeService.payment_method?.id || 1
      : 1,
    mileage_at_service: isCompleteService(completeService)
      ? completeService.vehicle?.mileage_at_service
      : service.mileage_at_service || 0,
    total_amount: isCompleteService(completeService)
      ? parseFloat(completeService.financial?.total_amount)
      : service.total_amount || 0,
    discount_amount: isCompleteService(completeService)
      ? completeService.financial?.discount || 0
      : 0,
    final_amount: isCompleteService(completeService)
      ? parseFloat(completeService.financial?.total_amount)
      : service.total_amount || 0,
    observations: isCompleteService(completeService)
      ? completeService.observations
      : service.observations || '',
    notes: isCompleteService(completeService)
      ? completeService.internal_notes
      : service.notes || '',
    active: true,
    items: service.items || [],
  };
}, [service, serviceDetails, vehicles]);
```

### 2. **Manipulação de Produtos**

#### **Adicionar Produto**

```typescript
const handleAddProduct = (
  product: TechnicianProduct,
  quantity: number = 1,
  notes?: string
) => {
  setEditData((prev) => {
    if (!prev) return null;

    // Verificar se o produto já existe
    const existingItem = prev.items?.find(
      (item) => item.product_id === product.id
    );

    if (existingItem) {
      // Atualizar quantidade se já existir
      return {
        ...prev,
        items:
          prev.items?.map((item) =>
            item.product_id === product.id
              ? {
                  ...item,
                  quantity: (item.quantity || 0) + quantity,
                  total_price:
                    (item.unit_price || 0) * ((item.quantity || 0) + quantity),
                }
              : item
          ) || [],
      };
    }

    // Adicionar novo produto
    const newItem: TechnicianServiceItem = {
      id: `item-${service?.id || 'new'}-${product.id}-${Date.now()}`,
      product_id: product.id,
      product: product,
      quantity,
      unit_price: product.price || 0,
      total_price: (product.price || 0) * quantity,
      notes: notes || '',
    };

    return {
      ...prev,
      items: [...(prev.items || []), newItem],
    };
  });
};
```

#### **Atualizar Quantidade**

```typescript
const handleUpdateProductQuantity = (itemId: string, quantity: number) => {
  const validQuantity = Math.max(1, Math.min(quantity, 999));

  setEditData((prev) => {
    if (!prev) return null;

    return {
      ...prev,
      items:
        prev.items?.map((item) =>
          item.id === itemId
            ? {
                ...item,
                quantity: validQuantity,
                total_price: (item.unit_price || 0) * validQuantity,
              }
            : item
        ) || [],
    };
  });
};
```

#### **Atualizar Preço**

```typescript
const handleUpdateProductPrice = (itemId: string, unitPrice: number) => {
  setEditData((prev) => {
    if (!prev) return null;

    return {
      ...prev,
      items:
        prev.items?.map((item) =>
          item.id === itemId
            ? {
                ...item,
                unit_price: unitPrice,
                total_price: (item.quantity || 0) * unitPrice,
              }
            : item
        ) || [],
    };
  });
};
```

### 3. **Cálculo de Totais**

```typescript
const handleCalculateItemsTotal = () => {
  const total =
    editData?.items?.reduce((total, item) => {
      const itemTotal = (item.unit_price || 0) * (item.quantity || 0);
      return total + itemTotal;
    }, 0) || 0;
  return isNaN(total) ? 0 : total;
};

const handleCalculateFinalTotal = () => {
  const itemsTotal = handleCalculateItemsTotal();
  const discount = editData?.discount_amount || 0;
  const finalTotal = Math.max(0, itemsTotal - discount);
  return isNaN(finalTotal) ? 0 : finalTotal;
};
```

### 4. **Submissão dos Dados**

**Arquivo**: `frontend/src/pages/Technician.tsx`

```typescript
const handleEditServiceSubmit = async (
  serviceId: number,
  data: EditServiceData
) => {
  try {
    // Separar dados do serviço dos itens
    const { items, ...serviceData } = data;

    // Atualizar o serviço
    await updateServiceMutation.mutateAsync({
      id: serviceId,
      data: serviceData,
    });

    // Aguardar para garantir que a primeira transação foi commitada
    await new Promise((resolve) => setTimeout(resolve, 200));

    // Atualizar os itens do serviço
    await updateServiceItemsMutation.mutateAsync({
      serviceId,
      items: items || [],
    });

    setShowEditServiceModal(false);
    setSelectedServiceForEdit(null);

    // Invalidar cache
    queryClient.invalidateQueries({
      queryKey: [QUERY_KEYS.SERVICE, serviceId],
    });

    queryClient.invalidateQueries({
      queryKey: ['technician', 'search'],
    });

    toast.success('Serviço atualizado com sucesso!');
  } catch (error) {
    console.error('Erro ao editar serviço:', error);
    toast.error('Erro ao salvar alterações do serviço');
  }
};
```

---

## 🐘 Fluxo Backend

### 1. **Rota da API**

**Arquivo**: `backend/routes/api.php`

```php
Route::put('/services/{id}', [ServiceController::class, 'update']);
Route::put('/service-items/{serviceId}/bulk-update', [ServiceItemController::class, 'bulkUpdate']);
```

### 2. **Controller de Serviço**

**Arquivo**: `backend/app/Http/Controllers/Api/ServiceController.php`

```php
public function update(UpdateServiceRequest $request, int $id): JsonResponse
{
    try {
        $validated = $request->validated();

        $service = $this->serviceService->updateService($id, $validated);

        if (!$service) {
            return $this->errorResponse('Service not found', 404);
        }

        return $this->successResponse(
            new ServiceResource($service),
            'Service updated successfully'
        );
    } catch (Exception $e) {
        return $this->errorResponse('Error updating service', 500);
    }
}
```

### 3. **Validação de Dados**

**Arquivo**: `backend/app/Http/Requests/Api/Service/UpdateServiceRequest.php`

```php
public function rules(): array
{
    $serviceId = $this->route('id');

    return [
        'service_center_id' => 'sometimes|exists:service_centers,id',
        'client_id' => 'sometimes|exists:clients,id',
        'vehicle_id' => 'sometimes|exists:vehicles,id',
        'service_number' => ['sometimes', 'string', 'max:20', Rule::unique('services')->ignore($serviceId)],
        'description' => 'sometimes|string|min:3|max:500',
        'complaint' => 'nullable|string|max:1000',
        'diagnosis' => 'nullable|string|max:1000',
        'solution' => 'nullable|string|max:1000',
        'scheduled_at' => 'nullable|date',
        'started_at' => 'nullable|date',
        'completed_at' => 'nullable|date',
        'technician_id' => 'nullable|exists:users,id',
        'attendant_id' => 'nullable|exists:users,id',
        'service_status_id' => 'sometimes|exists:service_statuses,id',
        'payment_method_id' => 'nullable|exists:payment_methods,id',
        'mileage_at_service' => 'nullable|integer|min:0',
        'total_amount' => 'nullable|numeric|min:0',
        'discount_amount' => 'nullable|numeric|min:0',
        'final_amount' => 'nullable|numeric|min:0',
        'observations' => 'nullable|string|max:2000',
        'notes' => 'nullable|string|max:1000',
        'active' => 'sometimes|boolean',
        'estimated_duration' => 'nullable|integer|min:15|max:480',
        'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
        'items' => 'nullable|array',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.discount' => 'nullable|numeric|min:0|max:100',
        'items.*.notes' => 'nullable|string|max:500',
    ];
}
```

### 4. **Service Layer**

**Arquivo**: `backend/app/Domain/Service/Services/ServiceService.php`

```php
public function updateService(int $id, array $data): ?Service
{
    $service = $this->serviceRepository->update($id, $data);

    if ($service) {
        $this->clearServiceCaches($service);
    }

    return $service;
}
```

### 5. **Repository Layer**

**Arquivo**: `backend/app/Domain/Service/Repositories/ServiceRepository.php`

```php
public function update(int $id, array $data): ?Service
{
    $service = Service::find($id);

    if (!$service) {
        return null;
    }

    return DB::transaction(function () use ($service, $data) {
        // Filtrar apenas campos que realmente mudaram
        $changedData = $this->filterChangedFields($service, $data);

        // Só atualizar se há mudanças
        if (!empty($changedData)) {
            $service->update($changedData);
        }

        // Update service items if provided
        if (isset($data['items']) && is_array($data['items'])) {
            // Remove existing items
            $service->serviceItems()->delete();

            // Add new items
            $this->addServiceItems($service, $data['items']);
        }

        return $service->fresh([
            'client',
            'vehicle',
            'serviceCenter',
            'serviceStatus',
            'serviceItems.product'
        ]);
    });
}

private function filterChangedFields(Service $service, array $data): array
{
    $changedFields = [];

    foreach ($data as $field => $value) {
        // Pular campos especiais como 'items'
        if ($field === 'items') {
            continue;
        }

        // Comparar valores
        if ($service->getAttribute($field) != $value) {
            $changedFields[$field] = $value;
        }
    }

    return $changedFields;
}

public function addServiceItems(Service $service, array $items): void
{
    foreach ($items as $item) {
        ServiceItem::create([
            'service_id' => $service->id,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'discount' => $item['discount'] ?? 0,
            'total_price' => $item['quantity'] * $item['unit_price'] * (1 - ($item['discount'] ?? 0) / 100),
            'notes' => $item['notes'] ?? null,
        ]);
    }

    // Recalculate service totals
    $service->calculateTotals();
}
```

### 6. **Model Layer**

**Arquivo**: `backend/app/Domain/Service/Models/Service.php`

```php
public function calculateTotals(): void
{
    $totalAmount = $this->serviceItems()->sum('total_price');
    $finalAmount = $totalAmount - ($this->discount_amount ?? 0);

    $this->update([
        'total_amount' => $totalAmount,
        'final_amount' => max(0, $finalAmount),
    ]);
}
```

---

## 📊 Campos e Dados

### **Dados Enviados do Frontend para o Backend**

#### **1. Dados do Serviço (Primeira Requisição)**

```typescript
interface EditServiceData {
  vehicle_id?: number;
  description?: string;
  estimated_duration?: number;
  scheduled_at?: string;
  mileage_at_service?: number;
  internal_notes?: string;
  observations?: string;
  discount?: number;
  total_amount?: number;
  final_amount?: number;
}
```

#### **2. Dados dos Itens (Segunda Requisição)**

```typescript
interface ServiceItemData {
  product_id: number;
  quantity: number;
  unit_price: number;
  discount?: number;
  notes?: string;
}
```

### **Mapeamento de Campos**

| Campo Frontend       | Campo Backend        | Tipo       | Descrição                  |
| -------------------- | -------------------- | ---------- | -------------------------- |
| `vehicle_id`         | `vehicle_id`         | `integer`  | ID do veículo              |
| `description`        | `description`        | `string`   | Descrição do serviço       |
| `estimated_duration` | `estimated_duration` | `integer`  | Duração estimada (minutos) |
| `scheduled_at`       | `scheduled_at`       | `datetime` | Data/hora agendada         |
| `mileage_at_service` | `mileage_at_service` | `integer`  | Quilometragem no serviço   |
| `internal_notes`     | `notes`              | `text`     | Notas internas             |
| `observations`       | `observations`       | `text`     | Observações                |
| `discount`           | `discount_amount`    | `decimal`  | Valor do desconto          |
| `total_amount`       | `total_amount`       | `decimal`  | Total dos itens            |
| `final_amount`       | `final_amount`       | `decimal`  | Total final (com desconto) |

### **Campos dos Itens**

| Campo Frontend | Campo Backend | Tipo      | Descrição           |
| -------------- | ------------- | --------- | ------------------- |
| `product_id`   | `product_id`  | `integer` | ID do produto       |
| `quantity`     | `quantity`    | `integer` | Quantidade          |
| `unit_price`   | `unit_price`  | `decimal` | Preço unitário      |
| `discount`     | `discount`    | `decimal` | Desconto percentual |
| `notes`        | `notes`       | `text`    | Notas do item       |

---

## ✅ Processo de Validação

### **1. Validação Frontend**

- Verificação de campos obrigatórios
- Validação de tipos de dados
- Cálculo automático de totais
- Verificação de mudanças antes do envio

### **2. Validação Backend**

- Validação de regras de negócio
- Verificação de existência de registros relacionados
- Validação de permissões
- Sanitização de dados

### **3. Validação de Banco**

- Constraints de integridade referencial
- Validação de tipos de dados
- Verificação de chaves únicas

---

## 🔄 Cache e Sincronização

### **1. React Query Cache**

```typescript
// Invalidar cache do serviço específico
queryClient.invalidateQueries({
  queryKey: [QUERY_KEYS.SERVICE, serviceId],
});

// Invalidar cache de busca
queryClient.invalidateQueries({
  queryKey: ['technician', 'search'],
});
```

### **2. Backend Cache**

```php
private function clearServiceCaches(Service $service): void
{
    Cache::forget("service_{$service->id}");
    Cache::forget("service_items_{$service->id}");
    Cache::tags(['services'])->flush();
}
```

---

## ⚠️ Tratamento de Erros

### **1. Frontend**

```typescript
try {
  await updateServiceMutation.mutateAsync({ id: serviceId, data: serviceData });
  await updateServiceItemsMutation.mutateAsync({ serviceId, items });
  toast.success('Serviço atualizado com sucesso!');
} catch (error) {
  console.error('Erro ao editar serviço:', error);

  const errorMessage = (error as any)?.response?.data?.message;
  if (errorMessage?.includes('Service not found')) {
    toast.error('Serviço não encontrado. Tente recarregar a página.');
  } else {
    toast.error('Erro ao salvar alterações do serviço');
  }
}
```

### **2. Backend**

```php
try {
    $service = $this->serviceService->updateService($id, $validated);

    if (!$service) {
        return $this->errorResponse('Service not found', 404);
    }

    return $this->successResponse(
        new ServiceResource($service),
        'Service updated successfully'
    );
} catch (ValidationException $e) {
    return $this->errorResponse('Validation failed', 422, $e->errors());
} catch (Exception $e) {
    Log::error('Error updating service', [
        'service_id' => $id,
        'error' => $e->getMessage()
    ]);
    return $this->errorResponse('Error updating service', 500);
}
```

---

## 📝 Exemplos Práticos

### **Exemplo 1: Atualização Simples**

**Dados Enviados:**

```json
{
  "description": "Troca de óleo e filtro - atualizado",
  "observations": "Cliente solicitou óleo sintético",
  "mileage_at_service": 50000
}
```

**Processo:**

1. Frontend valida dados
2. Envia requisição PUT para `/api/services/123`
3. Backend valida e atualiza apenas campos alterados
4. Retorna serviço atualizado
5. Frontend atualiza cache e exibe sucesso

### **Exemplo 2: Atualização com Itens**

**Dados do Serviço:**

```json
{
  "description": "Manutenção completa",
  "total_amount": 1500.0,
  "final_amount": 1350.0,
  "discount": 150.0
}
```

**Dados dos Itens:**

```json
[
  {
    "product_id": 5,
    "quantity": 2,
    "unit_price": 89.9,
    "discount": 10.0,
    "notes": "Óleo sintético premium"
  },
  {
    "product_id": 12,
    "quantity": 1,
    "unit_price": 45.0,
    "discount": 0,
    "notes": "Filtro de óleo"
  }
]
```

**Processo:**

1. Frontend calcula totais automaticamente
2. Envia dados do serviço (primeira requisição)
3. Aguarda confirmação (200ms)
4. Envia dados dos itens (segunda requisição)
5. Backend recalcula totais baseado nos itens
6. Frontend atualiza cache e exibe sucesso

### **Exemplo 3: Tratamento de Erro**

**Cenário:** Produto não encontrado

**Resposta do Backend:**

```json
{
  "success": false,
  "message": "Product not found",
  "errors": {
    "items.0.product_id": ["The selected product id is invalid."]
  }
}
```

**Tratamento Frontend:**

```typescript
if (errorMessage?.includes('Product not found')) {
  toast.error('Produto não encontrado. Verifique se ainda está disponível.');
}
```

---

## 🔧 Configurações Importantes

### **1. Timeouts**

```typescript
// Delay entre transações
await new Promise((resolve) => setTimeout(resolve, 200));
```

### **2. Validações**

```php
// Regras de validação
'quantity' => 'required|integer|min:1|max:999',
'unit_price' => 'required|numeric|min:0',
'discount' => 'nullable|numeric|min:0|max:100',
```

### **3. Transações**

```php
// Garantir consistência
return DB::transaction(function () use ($service, $data) {
    // Operações de banco
});
```

---

## 📋 Checklist de Implementação

### **Frontend**

- [ ] Validação de dados antes do envio
- [ ] Cálculo automático de totais
- [ ] Tratamento de erros adequado
- [ ] Feedback visual para o usuário
- [ ] Invalidação de cache
- [ ] Loading states

### **Backend**

- [ ] Validação de entrada
- [ ] Transações de banco
- [ ] Recalculação de totais
- [ ] Logs de erro
- [ ] Respostas padronizadas
- [ ] Cache invalidation

### **Testes**

- [ ] Testes unitários
- [ ] Testes de integração
- [ ] Testes de validação
- [ ] Testes de erro
- [ ] Testes de performance

---

## 🚀 Melhorias Futuras

1. **Otimização de Performance**

   - Implementar cache Redis
   - Otimizar queries de banco
   - Lazy loading de dados

2. **Funcionalidades**

   - Histórico de alterações
   - Notificações em tempo real
   - Backup automático

3. **Segurança**
   - Rate limiting
   - Validação mais rigorosa
   - Auditoria de mudanças

---

**📖 Esta documentação deve ser atualizada sempre que houver mudanças no fluxo de atualização de serviços.**
