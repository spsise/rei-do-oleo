<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Product\Repositories\CategoryRepositoryInterface;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\Api\Category\StoreCategoryRequest;
use App\Http\Requests\Api\Category\UpdateCategoryRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     tags={"Categorias"},
     *     summary="Listar categorias",
     *     description="Lista todas as categorias com opções de filtro",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Termo de busca por nome",
     *         required=false,
     *         @OA\Schema(type="string", example="óleo")
     *     ),
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Filtrar por status ativo",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Número de itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de categorias obtida com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Categorias listadas com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Óleos"),
     *                     @OA\Property(property="slug", type="string", example="oleos"),
     *                     @OA\Property(property="description", type="string", example="Categoria de óleos lubrificantes"),
     *                     @OA\Property(property="active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou não fornecido"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'active', 'per_page']);
        $categories = $this->categoryRepository->searchByFilters($filters);

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Categorias listadas com sucesso'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     tags={"Categorias"},
     *     summary="Criar nova categoria",
     *     description="Cria uma nova categoria de produtos",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Filtros de Óleo"),
     *             @OA\Property(property="description", type="string", example="Categoria para filtros de óleo automotivos"),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Categoria criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Categoria criada com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Filtros de Óleo"),
     *                 @OA\Property(property="slug", type="string", example="filtros-de-oleo"),
     *                 @OA\Property(property="description", type="string", example="Categoria para filtros de óleo automotivos"),
     *                 @OA\Property(property="active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou não fornecido"
     *     )
     * )
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Auto-generate slug
        $validated['slug'] = Str::slug($validated['name']);

        $category = $this->categoryRepository->create($validated);

        return $this->successResponse(
            new CategoryResource($category),
            'Categoria criada com sucesso',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{id}",
     *     tags={"Categorias"},
     *     summary="Obter categoria específica",
     *     description="Retorna os dados de uma categoria específica",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da categoria",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoria encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Categoria encontrada"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Óleos"),
     *                 @OA\Property(property="slug", type="string", example="oleos"),
     *                 @OA\Property(property="description", type="string", example="Categoria de óleos lubrificantes"),
     *                 @OA\Property(property="active", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoria não encontrada"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            return $this->errorResponse('Categoria não encontrada', 404);
        }

        return $this->successResponse(
            new CategoryResource($category),
            'Categoria encontrada'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/categories/{id}",
     *     tags={"Categorias"},
     *     summary="Atualizar categoria",
     *     description="Atualiza os dados de uma categoria existente",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da categoria",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Óleos Premium"),
     *             @OA\Property(property="description", type="string", example="Categoria de óleos lubrificantes premium"),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoria atualizada com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoria não encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        // Update slug if name changed
        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category = $this->categoryRepository->update($id, $validated);

        if (!$category) {
            return $this->errorResponse('Categoria não encontrada', 404);
        }

        return $this->successResponse(
            new CategoryResource($category),
            'Categoria atualizada com sucesso'
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/categories/{id}",
     *     tags={"Categorias"},
     *     summary="Excluir categoria",
     *     description="Remove uma categoria do sistema",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da categoria",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoria excluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Categoria excluída com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoria não encontrada"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->categoryRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Categoria não encontrada', 404);
        }

        return $this->successResponse(null, 'Categoria excluída com sucesso');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/active/list",
     *     tags={"Categorias"},
     *     summary="Listar categorias ativas",
     *     description="Retorna apenas as categorias que estão ativas no sistema",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Categorias ativas listadas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Categorias ativas listadas"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Óleos"),
     *                     @OA\Property(property="slug", type="string", example="oleos"),
     *                     @OA\Property(property="description", type="string", example="Categoria de óleos lubrificantes"),
     *                     @OA\Property(property="active", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getActive(): JsonResponse
    {
        $categories = $this->categoryRepository->getAllActive();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Categorias ativas listadas'
        );
    }
}
