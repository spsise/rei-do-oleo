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
