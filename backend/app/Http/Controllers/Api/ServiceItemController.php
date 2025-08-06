<?php

namespace App\Http\Controllers\Api;

use App\Domain\Service\Models\ServiceItem;
use App\Domain\Service\Services\ServiceItemService;
use App\Http\Controllers\Controller;
use App\Http\Requests\BulkStoreServiceItemsRequest;
use App\Http\Requests\BulkUpdateServiceItemsRequest;
use App\Http\Requests\StoreServiceItemRequest;
use App\Http\Requests\UpdateServiceItemRequest;
use App\Http\Resources\ServiceItemResource;
use App\Http\Resources\ServiceResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ServiceItemController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private ServiceItemService $serviceItemService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/services/{serviceId}/items",
     *     tags={"Itens de Serviço"},
     *     summary="Listar itens de um serviço",
     *     description="Lista todos os itens de um serviço específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         description="ID do serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Itens do serviço listados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Itens do serviço listados com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="service_id", type="integer", example=1),
     *                     @OA\Property(property="product_id", type="integer", example=5),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="unit_price", type="number", format="float", example=89.90),
     *                     @OA\Property(property="discount", type="number", format="float", example=10.0),
     *                     @OA\Property(property="total_price", type="number", format="float", example=161.82),
     *                     @OA\Property(property="notes", type="string", example="Óleo sintético premium"),
     *                     @OA\Property(
     *                         property="product",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="name", type="string", example="Óleo Shell Helix Ultra"),
     *                         @OA\Property(property="category", type="object")
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Serviço não encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou não fornecido"
     *     )
     * )
     */
    public function index(int $serviceId): JsonResponse
    {
        try {
            $items = $this->serviceItemService->getServiceItems($serviceId);

            return $this->successResponse(
                ServiceItemResource::collection($items),
                'Itens do serviço listados com sucesso'
            );
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Erro ao listar itens do serviço', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/services/{serviceId}/items",
     *     tags={"Itens de Serviço"},
     *     summary="Adicionar item ao serviço",
     *     description="Adiciona um novo item a um serviço específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         description="ID do serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","quantity","unit_price"},
     *             @OA\Property(property="product_id", type="integer", example=5, description="ID do produto"),
     *             @OA\Property(property="quantity", type="integer", example=2, description="Quantidade do produto"),
     *             @OA\Property(property="unit_price", type="number", format="float", example=89.90, description="Preço unitário"),
     *             @OA\Property(property="discount", type="number", format="float", example=10.0, description="Desconto em porcentagem"),
     *             @OA\Property(property="notes", type="string", example="Óleo sintético premium", description="Observações sobre o item")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item adicionado ao serviço com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Item adicionado ao serviço com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="service_id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=5),
     *                 @OA\Property(property="quantity", type="integer", example=2),
     *                 @OA\Property(property="unit_price", type="number", format="float", example=89.90),
     *                 @OA\Property(property="discount", type="number", format="float", example=10.0),
     *                 @OA\Property(property="total_price", type="number", format="float", example=161.82),
     *                 @OA\Property(property="notes", type="string", example="Óleo sintético premium")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Serviço não encontrado"
     *     )
     * )
     */
    public function store(StoreServiceItemRequest $request, int $serviceId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $item = $this->serviceItemService->createServiceItem($serviceId, $validated);

            return $this->successResponse(
                new ServiceItemResource($item->load(['product', 'product.category'])),
                'Item adicionado ao serviço com sucesso',
                201
            );
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Erro ao adicionar item ao serviço', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/{serviceId}/items/{itemId}",
     *     tags={"Itens de Serviço"},
     *     summary="Obter item específico do serviço",
     *     description="Retorna os dados de um item específico de um serviço",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         description="ID do serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="itemId",
     *         in="path",
     *         required=true,
     *         description="ID do item",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Item encontrado"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="service_id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=5),
     *                 @OA\Property(property="quantity", type="integer", example=2),
     *                 @OA\Property(property="unit_price", type="number", format="float", example=89.90),
     *                 @OA\Property(property="discount", type="number", format="float", example=10.0),
     *                 @OA\Property(property="total_price", type="number", format="float", example=161.82),
     *                 @OA\Property(property="notes", type="string", example="Óleo sintético premium")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item não encontrado"
     *     )
     * )
     */
    public function show(int $serviceId, string $itemId): JsonResponse
    {
        $itemId = (int) $itemId;

        $item = ServiceItem::where('service_id', $serviceId)
                          ->where('id', $itemId)
                          ->with(['product', 'product.category'])
                          ->first();

        if (!$item) {
            return $this->errorResponse('Item não encontrado', 404);
        }

        return $this->successResponse(
            new ServiceItemResource($item),
            'Item encontrado'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/services/{serviceId}/items/{itemId}",
     *     tags={"Itens de Serviço"},
     *     summary="Atualizar item do serviço",
     *     description="Atualiza os dados de um item específico de um serviço",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         description="ID do serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="itemId",
     *         in="path",
     *         required=true,
     *         description="ID do item",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="product_id", type="integer", example=5, description="ID do produto"),
     *             @OA\Property(property="quantity", type="integer", example=3, description="Quantidade do produto"),
     *             @OA\Property(property="unit_price", type="number", format="float", example=95.90, description="Preço unitário"),
     *             @OA\Property(property="discount", type="number", format="float", example=15.0, description="Desconto em porcentagem"),
     *             @OA\Property(property="notes", type="string", example="Óleo sintético premium atualizado", description="Observações sobre o item")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Item atualizado com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="service_id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=5),
     *                 @OA\Property(property="quantity", type="integer", example=3),
     *                 @OA\Property(property="unit_price", type="number", format="float", example=95.90),
     *                 @OA\Property(property="discount", type="number", format="float", example=15.0),
     *                 @OA\Property(property="total_price", type="number", format="float", example=244.54),
     *                 @OA\Property(property="notes", type="string", example="Óleo sintético premium atualizado")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item não encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function update(UpdateServiceItemRequest $request, int $serviceId, string $itemId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $itemId = (int) $itemId;
            $item = $this->serviceItemService->updateServiceItem($serviceId, $itemId, $validated);

            return $this->successResponse(
                new ServiceItemResource($item->load(['product', 'product.category'])),
                'Item atualizado com sucesso'
            );
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Erro ao atualizar item do serviço', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/services/{serviceId}/items/{itemId}",
     *     tags={"Itens de Serviço"},
     *     summary="Remover item do serviço",
     *     description="Remove um item específico de um serviço",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         description="ID do serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="itemId",
     *         in="path",
     *         required=true,
     *         description="ID do item",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removido do serviço",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Item removido do serviço")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item não encontrado"
     *     )
     * )
     */
    public function destroy(int $serviceId, string $itemId): JsonResponse
    {
        try {
            $itemId = (int) $itemId;
            $this->serviceItemService->deleteServiceItem($serviceId, $itemId);

            return $this->successResponse(null, 'Item removido do serviço');
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Erro ao remover item do serviço', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/services/{serviceId}/items/bulk",
     *     tags={"Itens de Serviço"},
     *     summary="Adicionar múltiplos itens ao serviço",
     *     description="Adiciona múltiplos itens a um serviço de uma só vez",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         description="ID do serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=5),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="unit_price", type="number", format="float", example=89.90),
     *                     @OA\Property(property="discount", type="number", format="float", example=10.0),
     *                     @OA\Property(property="notes", type="string", example="Óleo sintético premium")
     *                 ),
     *                 example={
     *                     {
     *                         "product_id": 5,
     *                         "quantity": 2,
     *                         "unit_price": 89.90,
     *                         "discount": 10.0,
     *                         "notes": "Óleo sintético premium"
     *                     },
     *                     {
     *                         "product_id": 8,
     *                         "quantity": 1,
     *                         "unit_price": 45.00,
     *                         "discount": 5.0,
     *                         "notes": "Filtro de óleo"
     *                     }
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Itens adicionados ao serviço em lote",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Itens adicionados ao serviço em lote"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="service_id", type="integer", example=1),
     *                     @OA\Property(property="product_id", type="integer", example=5),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="unit_price", type="number", format="float", example=89.90),
     *                     @OA\Property(property="discount", type="number", format="float", example=10.0),
     *                     @OA\Property(property="total_price", type="number", format="float", example=161.82),
     *                     @OA\Property(property="notes", type="string", example="Óleo sintético premium")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Serviço não encontrado"
     *     )
     * )
     */
    public function bulkStore(BulkStoreServiceItemsRequest $request, int $serviceId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $items = $this->serviceItemService->bulkCreateServiceItems($serviceId, $validated['items']);

            return $this->successResponse(
                ServiceItemResource::collection($items),
                'Itens adicionados ao serviço em lote',
                201
            );
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Erro ao adicionar itens ao serviço', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/services/{serviceId}/items/bulk",
     *     tags={"Itens de Serviço"},
     *     summary="Atualizar itens do serviço (Compatibilidade)",
     *     description="**ENDPOINT DE COMPATIBILIDADE**: Redireciona para a nova implementação unificada. Use PUT /api/v1/services/{id} para novas implementações.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         description="ID do serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 description="Lista de itens do serviço (será convertida para estrutura unificada)",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=5, description="ID do produto"),
     *                     @OA\Property(property="quantity", type="integer", example=2, description="Quantidade"),
     *                     @OA\Property(property="unit_price", type="number", format="float", example=89.90, description="Preço unitário"),
     *                     @OA\Property(property="discount", type="number", format="float", example=10.0, description="Desconto em porcentagem"),
     *                     @OA\Property(property="notes", type="string", example="Óleo sintético premium", description="Observações do item")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Itens do serviço atualizados com sucesso (via implementação unificada)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Serviço atualizado com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="service_number", type="string", example="SER001"),
     *                 @OA\Property(property="description", type="string", example="Troca de óleo e filtro"),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=140.00),
     *                 @OA\Property(property="items", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Serviço não encontrado"
     *     )
     * )
     */
    public function bulkUpdate(BulkUpdateServiceItemsRequest $request, int $serviceId): JsonResponse
    {
        // Converter estrutura antiga para nova implementação unificada
        $unifiedData = [
            'service' => [], // Dados vazios, apenas atualizar itens
            'items' => [
                'operation' => 'replace',
                'data' => $request->validated()['items']
            ]
        ];

        // Usar a nova implementação unificada
        $service = app(\App\Domain\Service\Actions\UpdateServiceWithItemsAction::class)
            ->execute($serviceId, $unifiedData);

        if (!$service) {
            return $this->errorResponse('Serviço não encontrado', 404);
        }

        return $this->successResponse(
            new ServiceResource($service),
            'Serviço atualizado com sucesso'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/{serviceId}/items/total/calculate",
     *     tags={"Itens de Serviço"},
     *     summary="Calcular total dos itens do serviço",
     *     description="Calcula o total de todos os itens de um serviço específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         description="ID do serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Total dos itens calculado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Total dos itens calculado"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="service_id", type="integer", example=1),
     *                 @OA\Property(property="items_total", type="number", format="float", example=284.50),
     *                 @OA\Property(property="formatted_total", type="string", example="R$ 284,50")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Serviço não encontrado"
     *     )
     * )
     */
    public function getServiceTotal(int $serviceId): JsonResponse
    {
        $total = ServiceItem::where('service_id', $serviceId)
                           ->sum('total_price');

        return $this->successResponse([
            'service_id' => $serviceId,
            'items_total' => $total,
            'formatted_total' => 'R$ ' . number_format($total, 2, ',', '.')
        ], 'Total dos itens calculado');
    }
}
