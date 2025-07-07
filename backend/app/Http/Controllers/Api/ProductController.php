<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\Services\ProductService;
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
        private ProductRepositoryInterface $productRepository,
        private ProductService $productService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     tags={"Produtos"},
     *     summary="Listar produtos",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="active", in="query", required=false, @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="Lista de produtos obtida com sucesso")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'category_id', 'active', 'low_stock', 'per_page']);
        $products = $this->productRepository->searchByFilters($filters);

        return $this->successResponse(
            ProductResource::collection($products),
            'Produtos listados com sucesso'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products",
     *     tags={"Produtos"},
     *     summary="Criar novo produto",
     *     description="Cria um novo produto no sistema",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","category_id","price","stock_quantity"},
     *             @OA\Property(property="name", type="string", example="Óleo Motor 5W30"),
     *             @OA\Property(property="description", type="string", example="Óleo lubrificante sintético para motores"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="price", type="number", format="float", example=89.90),
     *             @OA\Property(property="stock_quantity", type="integer", example=50),
     *             @OA\Property(property="min_stock", type="integer", example=10),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Produto criado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productRepository->create($request->validated());

        return $this->successResponse(
            new ProductResource($product),
            'Produto criado com sucesso',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{id}",
     *     tags={"Produtos"},
     *     summary="Obter produto específico",
     *     description="Retorna os dados de um produto específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produto encontrado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produto não encontrado"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/v1/products/{id}",
     *     tags={"Produtos"},
     *     summary="Atualizar produto",
     *     description="Atualiza os dados de um produto existente",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Óleo Motor 5W30 Premium"),
     *             @OA\Property(property="description", type="string", example="Óleo lubrificante sintético premium"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="price", type="number", format="float", example=99.90),
     *             @OA\Property(property="stock_quantity", type="integer", example=60),
     *             @OA\Property(property="min_stock", type="integer", example=15),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produto atualizado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produto não encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/v1/products/{id}",
     *     tags={"Produtos"},
     *     summary="Excluir produto",
     *     description="Remove um produto do sistema",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produto excluído com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produto não encontrado"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->productRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Produto não encontrado', 404);
        }

        return $this->successResponse(null, 'Produto excluído com sucesso');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/active/list",
     *     tags={"Produtos"},
     *     summary="Listar produtos ativos",
     *     description="Retorna apenas os produtos que estão ativos no sistema",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Produtos ativos listados com sucesso"
     *     )
     * )
     */
    public function getActive(): JsonResponse
    {
        $products = $this->productRepository->getAllActive();

        return $this->successResponse(
            ProductResource::collection($products),
            'Produtos ativos listados'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/category/{categoryId}",
     *     tags={"Produtos"},
     *     summary="Listar produtos por categoria",
     *     description="Retorna todos os produtos de uma categoria específica",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produtos da categoria listados com sucesso"
     *     )
     * )
     */
    public function getByCategory(int $categoryId): JsonResponse
    {
        $products = $this->productRepository->getByCategory($categoryId);

        return $this->successResponse(
            ProductResource::collection($products),
            'Produtos da categoria listados'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products/search/name",
     *     tags={"Produtos"},
     *     summary="Buscar produtos por nome",
     *     description="Busca produtos que contenham o termo no nome",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="óleo", description="Termo de busca")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produtos encontrados"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function searchByName(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:200']);

        $products = $this->productRepository->searchByName($request->name);

        return $this->successResponse(
            ProductResource::collection($products),
            'Produtos encontrados'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/stock/low",
     *     tags={"Produtos"},
     *     summary="Listar produtos com estoque baixo",
     *     description="Retorna produtos com quantidade em estoque abaixo do mínimo",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Produtos com estoque baixo listados"
     *     )
     * )
     */
    public function getLowStock(): JsonResponse
    {
        $products = $this->productRepository->getLowStock();

        return $this->successResponse(
            ProductResource::collection($products),
            'Produtos com estoque baixo'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/products/{id}/stock",
     *     tags={"Produtos"},
     *     summary="Atualizar estoque do produto",
     *     description="Atualiza a quantidade em estoque de um produto",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity","type"},
     *             @OA\Property(property="quantity", type="integer", example=10, description="Quantidade a ser adicionada/subtraída/definida"),
     *             @OA\Property(property="type", type="string", enum={"add","subtract","set"}, example="add", description="Tipo de operação: add (adicionar), subtract (subtrair), set (definir)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estoque atualizado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Produto não encontrado ou estoque insuficiente"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/products/with-sales-data",
     *     tags={"Produtos"},
     *     summary="Listar produtos com dados de vendas",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="service_center_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Produtos com dados de vendas obtidos com sucesso")
     * )
     */
    public function withSalesData(Request $request): JsonResponse
    {
        $serviceCenterId = $request->query('service_center_id');
        $limit = $request->query('limit', 10);

        $products = $this->productService->getProductsWithSalesData($serviceCenterId, $limit);

        return $this->successResponse(
            $products,
            'Produtos com dados de vendas obtidos com sucesso'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/performance-metrics",
     *     tags={"Produtos"},
     *     summary="Obter métricas de performance dos produtos",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="service_center_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Métricas de performance obtidas com sucesso")
     * )
     */
    public function performanceMetrics(Request $request): JsonResponse
    {
        $serviceCenterId = $request->query('service_center_id');

        $metrics = $this->productService->getProductPerformanceMetrics($serviceCenterId);

        return $this->successResponse(
            $metrics,
            'Métricas de performance obtidas com sucesso'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/chart-data",
     *     tags={"Produtos"},
     *     summary="Obter dados para gráficos de produtos",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="service_center_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="period", in="query", required=false, @OA\Schema(type="string", enum={"7d", "30d", "90d", "1y"})),
     *     @OA\Response(response=200, description="Dados para gráficos obtidos com sucesso")
     * )
     */
    public function chartData(Request $request): JsonResponse
    {
        $serviceCenterId = $request->query('service_center_id');
        $period = $request->query('period', '30d');

        $chartData = $this->productService->getProductsChartData($serviceCenterId, $period);

        return $this->successResponse(
            $chartData,
            'Dados para gráficos obtidos com sucesso'
        );
    }
}
