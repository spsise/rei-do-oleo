# 📋 Documentação: Processo de Atualização de Serviço

## 🎯 Visão Geral

Este documento descreve o fluxo completo de atualização de serviços no sistema **Rei do Óleo**, explicando como o usuário interage com a interface, como os dados são processados no frontend e como são tratados no backend.

## 🔄 Fluxo Completo de Atualização

### 1. **Acesso à Tela de Edição**

#### Frontend - Páginas de Acesso

- **Página Principal de Serviços**: `frontend/src/pages/Services.tsx`
- **Página do Técnico**: `frontend/src/pages/Technician.tsx`
- **Componente de Formulário**: `frontend/src/components/Service/ServiceForm.tsx`

#### Como o Usuário Acessa

```typescript
// Na página de serviços, o usuário clica no botão "Editar"
const handleEditService = (service: Service) => {
  setSelectedServiceForEdit(service);
  setShowEditServiceModal(true);
};
```

### 2. **Carregamento dos Dados do Serviço**

#### Frontend - Hooks de Dados

```typescript
// Hook para buscar serviço específico
const { data: service } = useQuery({
  queryKey: ['service', serviceId],
  queryFn: () => serviceService.getService(serviceId),
});

// Hook para atualizar serviço
const updateServiceMutation = useUpdateService();
```

#### Backend - Endpoint de Busca

- **Rota**: `GET /api/v1/services/{id}`
- **Controller**: `ServiceController@show`
- **Service**: `ServiceService@findService`
- **Repository**: `ServiceRepository@find`

```php
// ServiceController.php
public function show(int $id): JsonResponse
{
    $service = $this->serviceService->findService($id);

    if (!$service) {
        return $this->errorResponse('Serviço não encontrado', 404);
    }

    return $this->successResponse(
        new ServiceResource($service),
        'Serviço encontrado'
    );
}
```

### 3. **Formulário de Edição**

#### Frontend - Componente ServiceForm

```typescript
// frontend/src/components/Service/ServiceForm.tsx
export const ServiceForm: React.FC<ServiceFormProps> = ({
  service,
  onSubmit,
  onCancel,
  loading = false,
}) => {
  const [formData, setFormData] = useState<CreateServiceData>({
    service_center_id: service?.service_center?.id ?? 0,
    client_id: service?.client?.id ?? 0,
    vehicle_id: service?.vehicle?.id ?? 0,
    description: service?.description ?? '',
    complaint: service?.complaint || '',
    diagnosis: service?.diagnosis || '',
    solution: service?.solution || '',
    // ... outros campos
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit(formData);
  };
};
```

#### Campos Editáveis

- **Informações Básicas**: Centro de serviço, cliente, veículo
- **Detalhes do Serviço**: Descrição, reclamação, diagnóstico, solução
- **Agendamento**: Data agendada, início, conclusão
- **Responsáveis**: Técnico, atendente
- **Financeiro**: Custo de mão de obra, desconto, valor total
- **Observações**: Observações gerais, notas internas
- **Itens**: Produtos utilizados no serviço

### 4. **Processamento do Submit**

#### Frontend - Validação e Envio

```typescript
// frontend/src/pages/Services.tsx
const handleUpdateService = async (data: UpdateServiceData) => {
  if (!modal.service) return;

  await updateServiceMutation.mutateAsync({
    id: modal.service.id,
    data,
  });
  closeModal();
};
```

#### Hook de Atualização

```typescript
// frontend/src/hooks/useServices.ts
export const useUpdateService = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      id,
      data,
    }: {
      id: number;
      data: UpdateServiceData;
    }): Promise<Service> => {
      const response = await serviceService.updateService(id, data);
      return response.data!;
    },
    onSuccess: (updatedService) => {
      // Atualizar cache do serviço específico
      queryClient.setQueryData(
        [QUERY_KEYS.SERVICE, updatedService.id],
        updatedService
      );

      // Invalidar queries relacionadas
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.SERVICES],
      });
    },
  });
};
```

### 5. **Requisição HTTP**

#### Frontend - Service Layer

```typescript
// frontend/src/services/service.service.ts
class ServiceService {
  async updateService(
    id: number,
    data: UpdateServiceData
  ): Promise<ApiResponse<Service>> {
    return apiCall(() =>
      httpClient.instance.put<ApiResponse<Service>>(`/services/${id}`, data)
    );
  }
}
```

#### Requisição HTTP Enviada

```http
PUT /api/v1/services/123
Content-Type: application/json
Authorization: Bearer {token}

{
  "description": "Troca de óleo e filtro - atualizado",
  "complaint": "Motor fazendo ruído estranho",
  "diagnosis": "Óleo vencido e filtro entupido",
  "solution": "Substituição completa do óleo e filtro",
  "technician_id": 2,
  "status_id": 2,
  "labor_cost": 150.00,
  "discount": 10.00,
  "observations": "Cliente solicitou óleo sintético",
  "items": [
    {
      "product_id": 5,
      "quantity": 2,
      "unit_price": 89.90,
      "discount": 10.0,
      "notes": "Óleo sintético premium"
    }
  ]
}
```

### 6. **Backend - Processamento da Requisição**

#### Rota da API

```php
// backend/routes/api.php
Route::put('/{id}', [ServiceController::class, 'update']); // PUT /api/v1/services/{id}
```

#### Controller - Validação e Processamento

```php
// backend/app/Http/Controllers/Api/ServiceController.php
public function update(UpdateServiceRequest $request, int $id): JsonResponse
{
    $service = $this->updateServiceAction->execute($id, $request->validated());

    if (!$service) {
        return $this->errorResponse('Serviço não encontrado', 404);
    }

    return $this->successResponse(
        new ServiceResource($service),
        'Serviço atualizado com sucesso'
    );
}
```

#### Request Validation

```php
// backend/app/Http/Requests/Api/Service/UpdateServiceRequest.php
public function rules(): array
{
    $serviceId = $this->route('id');

    return [
        'service_center_id' => 'sometimes|exists:service_centers,id',
        'client_id' => 'sometimes|exists:clients,id',
        'vehicle_id' => 'sometimes|exists:vehicles,id',
        'description' => 'sometimes|string|min:3|max:500',
        'complaint' => 'nullable|string|max:1000',
        'diagnosis' => 'nullable|string|max:1000',
        'solution' => 'nullable|string|max:1000',
        'technician_id' => 'nullable|exists:users,id',
        'status_id' => 'sometimes|exists:service_statuses,id',
        'labor_cost' => 'nullable|numeric|min:0',
        'discount' => 'nullable|numeric|min:0',
        'observations' => 'nullable|string|max:2000',
        'items' => 'nullable|array',
        'items.*.product_id' => 'required_with:items|integer|exists:products,id',
        'items.*.quantity' => 'required_with:items|integer|min:1',
        'items.*.unit_price' => 'required_with:items|numeric|min:0',
        'items.*.discount' => 'nullable|numeric|min:0|max:100',
        'items.*.notes' => 'nullable|string|max:500',
    ];
}
```

#### Action - Lógica de Negócio

```php
// backend/app/Domain/Service/Actions/UpdateServiceAction.php
class UpdateServiceAction
{
    public function __construct(
        private ServiceService $serviceService,
        private DataMappingService $dataMappingService
    ) {}

    public function execute(int $id, array $data): ?Service
    {
        // Map frontend field names to backend field names
        $mappedData = $this->dataMappingService->mapServiceFields($data);

        return $this->serviceService->updateService($id, $mappedData);
    }
}
```

#### Service - Camada de Serviço

```php
// backend/app/Domain/Service/Services/ServiceService.php
public function updateService(int $id, array $data): ?Service
{
    $service = $this->serviceRepository->update($id, $data);

    if ($service) {
        $this->clearServiceCaches($service);
    }

    return $service;
}
```

#### Repository - Persistência

```php
// backend/app/Domain/Service/Repositories/ServiceRepository.php
public function update(int $id, array $data): ?Service
{
    $service = Service::find($id);

    if (!$service) {
        return null;
    }

    return DB::transaction(function () use ($service, $data) {
        $service->update($data);

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
```

### 7. **Atualização de Itens do Serviço**

#### Processo Separado para Itens

```typescript
// frontend/src/pages/Technician.tsx
const handleEditServiceSubmit = async (
  serviceId: number,
  data: EditServiceData
) => {
  try {
    // Separar dados do serviço dos itens
    const { items, ...serviceData } = data;

    // Atualizar o serviço
    const updatedService = await updateServiceMutation.mutateAsync({
      id: serviceId,
      data: serviceData,
    });

    // Atualizar os itens do serviço
    const updatedItems = await updateServiceItemsMutation.mutateAsync({
      serviceId,
      items: items || [],
    });

    setShowEditServiceModal(false);
    setSelectedServiceForEdit(null);
  } catch (error) {
    console.error('Erro ao editar serviço:', error);
    toast.error('Erro ao salvar alterações do serviço');
  }
};
```

#### Hook para Itens

```typescript
// frontend/src/hooks/useServiceItems.ts
export const useUpdateServiceItems = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      serviceId,
      items,
    }: {
      serviceId: number;
      items: Array<{
        product_id: number;
        quantity: number;
        unit_price: number;
        notes?: string;
      }>;
    }): Promise<ServiceItemResponse[]> => {
      const response = await serviceItemService.updateServiceItems(
        serviceId,
        items
      );
      return response.data || [];
    },
    onSuccess: (_, { serviceId }) => {
      // Atualizar cache do serviço específico
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.SERVICE, serviceId],
      });

      // Invalidar queries relacionadas
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.SERVICES],
      });
    },
  });
};
```

#### Backend - Atualização de Itens

```php
// backend/app/Http/Controllers/Api/ServiceItemController.php
public function bulkUpdate(BulkUpdateServiceItemsRequest $request, int $serviceId): JsonResponse
{
    try {
        $validated = $request->validated();
        $items = $this->serviceItemService->bulkUpdateServiceItems($serviceId, $validated['items']);

        return $this->successResponse(
            ServiceItemResource::collection($items),
            'Itens do serviço atualizados com sucesso'
        );
    } catch (\InvalidArgumentException $e) {
        return $this->errorResponse($e->getMessage(), 404);
    } catch (\Exception $e) {
        return $this->errorResponse('Erro ao atualizar itens do serviço', 500);
    }
}
```

### 8. **Resposta da API**

#### Resposta de Sucesso

```json
{
  "status": "success",
  "message": "Serviço atualizado com sucesso",
  "data": {
    "id": 123,
    "service_number": "SER-2024-001",
    "description": "Troca de óleo e filtro - atualizado",
    "complaint": "Motor fazendo ruído estranho",
    "diagnosis": "Óleo vencido e filtro entupido",
    "solution": "Substituição completa do óleo e filtro",
    "status": {
      "id": 2,
      "name": "Em Andamento"
    },
    "technician": {
      "id": 2,
      "name": "João Silva"
    },
    "financial": {
      "labor_cost": 150.0,
      "discount": 10.0,
      "total_amount": 240.0
    },
    "items": [
      {
        "id": 1,
        "product": {
          "id": 5,
          "name": "Óleo Shell Helix Ultra",
          "sku": "OLEO-001"
        },
        "quantity": 2,
        "unit_price": 89.9,
        "discount": 10.0,
        "total_price": 161.82,
        "notes": "Óleo sintético premium"
      }
    ],
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T14:45:00Z"
  }
}
```

### 9. **Atualização do Cache e UI**

#### Frontend - Atualização do Estado

```typescript
// React Query invalida automaticamente o cache
onSuccess: (updatedService) => {
  // Atualizar cache do serviço específico
  queryClient.setQueryData(
    [QUERY_KEYS.SERVICE, updatedService.id],
    updatedService
  );

  // Invalidar queries relacionadas
  queryClient.invalidateQueries({
    queryKey: [QUERY_KEYS.SERVICES],
  });
};
```

#### Feedback ao Usuário

```typescript
// Toast de sucesso
toast.success('Serviço atualizado com sucesso');

// Fechar modal
setShowEditServiceModal(false);
setSelectedServiceForEdit(null);

// Recarregar dados se necessário
if (searchResult) {
  handleSearch();
}
```

## 🔧 Componentes Técnicos

### Estrutura de Arquivos

#### Frontend

```
frontend/src/
├── pages/
│   ├── Services.tsx              # Página principal de serviços
│   └── Technician.tsx            # Página do técnico
├── components/
│   ├── Service/
│   │   ├── ServiceForm.tsx       # Formulário de edição
│   │   └── ServiceTable.tsx      # Tabela de serviços
│   └── Technician/
│       └── EditServiceModal.tsx  # Modal de edição
├── hooks/
│   ├── useServices.ts            # Hook para serviços
│   └── useServiceItems.ts        # Hook para itens
├── services/
│   ├── service.service.ts        # Service de serviços
│   └── serviceItem.service.ts    # Service de itens
└── types/
    └── service.ts                # Tipos TypeScript
```

#### Backend

```
backend/app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── ServiceController.php     # Controller principal
│   │   └── ServiceItemController.php # Controller de itens
│   └── Requests/Api/Service/
│       └── UpdateServiceRequest.php  # Validação
├── Domain/Service/
│   ├── Actions/
│   │   └── UpdateServiceAction.php   # Lógica de negócio
│   ├── Services/
│   │   ├── ServiceService.php        # Service principal
│   │   └── ServiceItemService.php    # Service de itens
│   ├── Repositories/
│   │   ├── ServiceRepository.php     # Repository principal
│   │   └── ServiceItemRepository.php # Repository de itens
│   └── Models/
│       ├── Service.php               # Model principal
│       └── ServiceItem.php           # Model de itens
└── Services/
    └── DataMappingService.php        # Mapeamento de campos
```

### Rotas da API

#### Serviços

```php
// backend/routes/api.php
Route::prefix('services')->group(function () {
    Route::get('/{id}', [ServiceController::class, 'show']);           // GET /api/v1/services/{id}
    Route::put('/{id}', [ServiceController::class, 'update']);         // PUT /api/v1/services/{id}
    Route::put('/{id}/status', [ServiceController::class, 'updateStatus']); // PUT /api/v1/services/{id}/status
});
```

#### Itens de Serviço

```php
Route::prefix('services/{serviceId}/items')->group(function () {
    Route::get('/', [ServiceItemController::class, 'index']);          // GET /api/v1/services/{serviceId}/items
    Route::put('/{itemId}', [ServiceItemController::class, 'update']); // PUT /api/v1/services/{serviceId}/items/{itemId}
    Route::put('/bulk', [ServiceItemController::class, 'bulkUpdate']); // PUT /api/v1/services/{serviceId}/items/bulk
});
```

## 🚨 Tratamento de Erros

### Frontend - Tratamento de Erros

```typescript
// Hook com tratamento de erro
export const useUpdateService = () => {
  return useMutation({
    mutationFn: async ({ id, data }) => {
      const response = await serviceService.updateService(id, data);
      return response.data!;
    },
    onError: (error: ApiError) => {
      console.error('Erro ao atualizar serviço:', error);
      toast.error('Erro ao atualizar serviço. Tente novamente.');
      throw error;
    },
  });
};
```

### Backend - Validação e Erros

```php
// ServiceController.php
public function update(UpdateServiceRequest $request, int $id): JsonResponse
{
    try {
        $service = $this->updateServiceAction->execute($id, $request->validated());

        if (!$service) {
            return $this->errorResponse('Serviço não encontrado', 404);
        }

        return $this->successResponse(
            new ServiceResource($service),
            'Serviço atualizado com sucesso'
        );
    } catch (ValidationException $e) {
        return $this->errorResponse('Dados inválidos', 422, $e->errors());
    } catch (\Exception $e) {
        return $this->errorResponse('Erro interno do servidor', 500);
    }
}
```

## 📊 Monitoramento e Logs

### Frontend - Logs de Debug

```typescript
console.log('Dados recebidos para edição:', { serviceId, data });
console.log('Dados do serviço:', serviceData);
console.log('Itens do serviço:', items);
console.log('Serviço atualizado:', updatedService);
console.log('Itens atualizados:', updatedItems);
```

### Backend - Logs de Sistema

```php
// ServiceService.php
public function updateService(int $id, array $data): ?Service
{
    Log::info('Atualizando serviço', [
        'service_id' => $id,
        'user_id' => Auth::id(),
        'changes' => $data
    ]);

    $service = $this->serviceRepository->update($id, $data);

    if ($service) {
        $this->clearServiceCaches($service);
        Log::info('Serviço atualizado com sucesso', ['service_id' => $id]);
    }

    return $service;
}
```

## 🔄 Fluxo de Cache

### Invalidação de Cache

```typescript
// Frontend - React Query
onSuccess: (updatedService) => {
  // Atualizar cache específico
  queryClient.setQueryData(
    [QUERY_KEYS.SERVICE, updatedService.id],
    updatedService
  );

  // Invalidar listagens
  queryClient.invalidateQueries({
    queryKey: [QUERY_KEYS.SERVICES],
  });

  // Invalidar busca do técnico
  queryClient.invalidateQueries({
    queryKey: ['technician', 'search'],
  });
};
```

### Backend - Cache Clearing

```php
// ServiceService.php
private function clearServiceCaches(Service $service): void
{
    Cache::forget("service_{$service->id}");
    Cache::forget("services_center_{$service->service_center_id}");
    Cache::forget("services_client_{$service->client_id}");
    Cache::forget("services_technician_{$service->technician_id}");
}
```

## 🎯 Pontos de Atenção

### Validações Importantes

1. **Permissões**: Verificar se o usuário pode editar o serviço
2. **Status**: Validar se o serviço pode ser editado no status atual
3. **Itens**: Garantir que produtos existem e têm estoque
4. **Valores**: Validar cálculos financeiros
5. **Datas**: Verificar consistência de datas (início, fim, agendamento)

### Performance

1. **Eager Loading**: Carregar relacionamentos necessários
2. **Cache**: Invalidar apenas caches relevantes
3. **Transações**: Usar transações para operações complexas
4. **Validação**: Validar dados antes de processar

### Segurança

1. **Sanitização**: Limpar dados de entrada
2. **Autorização**: Verificar permissões do usuário
3. **Validação**: Validar todos os campos obrigatórios
4. **Logs**: Registrar operações sensíveis

## 📝 Conclusão

O processo de atualização de serviço é robusto e bem estruturado, seguindo padrões de arquitetura limpa e boas práticas de desenvolvimento. O sistema oferece:

- ✅ **Interface intuitiva** para edição
- ✅ **Validação completa** de dados
- ✅ **Tratamento de erros** adequado
- ✅ **Cache inteligente** para performance
- ✅ **Logs detalhados** para monitoramento
- ✅ **Segurança** implementada em todas as camadas

O fluxo garante que as atualizações sejam processadas de forma segura e eficiente, mantendo a integridade dos dados e oferecendo uma boa experiência ao usuário.
