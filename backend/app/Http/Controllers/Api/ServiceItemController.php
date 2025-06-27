<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Service\Models\ServiceItem;
use App\Http\Resources\ServiceItemResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceItemController extends Controller
{
    use ApiResponseTrait;

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
        $items = ServiceItem::where('service_id', $serviceId)
                           ->with(['product', 'product.category'])
                           ->get();

        return $this->successResponse(
            ServiceItemResource::collection($items),
            'Itens do serviço listados com sucesso'
        );
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
    public function store(Request $request, int $serviceId): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string'
        ]);

        $validated['service_id'] = $serviceId;

        // Calculate total
        $subtotal = $validated['quantity'] * $validated['unit_price'];
        $discountAmount = $subtotal * (($validated['discount'] ?? 0) / 100);
        $validated['total_price'] = $subtotal - $discountAmount;

        $item = ServiceItem::create($validated);

        return $this->successResponse(
            new ServiceItemResource($item->load(['product', 'product.category'])),
            'Item adicionado ao serviço com sucesso',
            201
        );
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
    public function show(int $serviceId, int $itemId): JsonResponse
    {
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
    public function update(Request $request, int $serviceId, int $itemId): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'quantity' => 'sometimes|integer|min:1',
            'unit_price' => 'sometimes|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string'
        ]);

        $item = ServiceItem::where('service_id', $serviceId)
                          ->where('id', $itemId)
                          ->first();

        if (!$item) {
            return $this->errorResponse('Item não encontrado', 404);
        }

        // Recalculate total if relevant fields changed
        if (isset($validated['quantity']) || isset($validated['unit_price']) || isset($validated['discount'])) {
            $quantity = $validated['quantity'] ?? $item->quantity;
            $unitPrice = $validated['unit_price'] ?? $item->unit_price;
            $discount = $validated['discount'] ?? $item->discount ?? 0;

            $subtotal = $quantity * $unitPrice;
            $discountAmount = $subtotal * ($discount / 100);
            $validated['total_price'] = $subtotal - $discountAmount;
        }

        $item->update($validated);

        return $this->successResponse(
            new ServiceItemResource($item->load(['product', 'product.category'])),
            'Item atualizado com sucesso'
        );
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
    public function destroy(int $serviceId, int $itemId): JsonResponse
    {
        $item = ServiceItem::where('service_id', $serviceId)
                          ->where('id', $itemId)
                          ->first();

        if (!$item) {
            return $this->errorResponse('Item não encontrado', 404);
        }

        $item->delete();

        return $this->successResponse(null, 'Item removido do serviço');
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
    public function bulkStore(Request $request, int $serviceId): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string'
        ]);

        $createdItems = [];

        foreach ($validated['items'] as $itemData) {
            $itemData['service_id'] = $serviceId;

            // Calculate total
            $subtotal = $itemData['quantity'] * $itemData['unit_price'];
            $discountAmount = $subtotal * (($itemData['discount'] ?? 0) / 100);
            $itemData['total_price'] = $subtotal - $discountAmount;

            $createdItems[] = ServiceItem::create($itemData);
        }

        // Load relationships
        $items = ServiceItem::whereIn('id', collect($createdItems)->pluck('id'))
                           ->with(['product', 'product.category'])
                           ->get();

        return $this->successResponse(
            ServiceItemResource::collection($items),
            'Itens adicionados ao serviço em lote',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/{serviceId}/items/total",
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
