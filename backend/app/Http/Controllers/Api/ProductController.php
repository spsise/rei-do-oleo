<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Http\Resources\ProductResource;
use App\Http\Requests\Api\Product\StoreProductRequest;
use App\Http\Requests\Api\Product\UpdateProductRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'category_id', 'active', 'low_stock', 'per_page']);
        $products = $this->productRepository->searchByFilters($filters);

        return $this->successResponse(
            ProductResource::collection($products),
            'Produtos listados com sucesso'
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productRepository->create($request->validated());

        return $this->successResponse(
            new ProductResource($product),
            'Produto criado com sucesso',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return $this->errorResponse('Produto não encontrado', 404);
        }

        return $this->successResponse(
            new ProductResource($product),
            'Produto encontrado'
        );
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productRepository->update($id, $request->validated());

        if (!$product) {
            return $this->errorResponse('Produto não encontrado', 404);
        }

        return $this->successResponse(
            new ProductResource($product),
            'Produto atualizado com sucesso'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->productRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Produto não encontrado', 404);
        }

        return $this->successResponse(null, 'Produto excluído com sucesso');
    }

    public function getActive(): JsonResponse
    {
        $products = $this->productRepository->getAllActive();

        return $this->successResponse(
            ProductResource::collection($products),
            'Produtos ativos listados'
        );
    }

    public function getByCategory(int $categoryId): JsonResponse
    {
        $products = $this->productRepository->getByCategory($categoryId);

        return $this->successResponse(
            ProductResource::collection($products),
            'Produtos da categoria listados'
        );
    }

    public function searchByName(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:200']);

        $products = $this->productRepository->searchByName($request->name);

        return $this->successResponse(
            ProductResource::collection($products),
            'Produtos encontrados'
        );
    }

    public function getLowStock(): JsonResponse
    {
        $products = $this->productRepository->getLowStock();

        return $this->successResponse(
            ProductResource::collection($products),
            'Produtos com estoque baixo'
        );
    }

    public function updateStock(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|in:add,subtract,set'
        ]);

        $result = $this->productRepository->updateStock(
            $id,
            $request->quantity,
            $request->type
        );

        if (!$result) {
            return $this->errorResponse('Produto não encontrado ou estoque insuficiente', 400);
        }

        return $this->successResponse(null, 'Estoque atualizado com sucesso');
    }
}
