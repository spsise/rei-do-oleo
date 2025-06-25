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

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'active', 'per_page']);
        $categories = $this->categoryRepository->searchByFilters($filters);

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Categorias listadas com sucesso'
        );
    }

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

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->categoryRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Categoria não encontrada', 404);
        }

        return $this->successResponse(null, 'Categoria excluída com sucesso');
    }

    public function getActive(): JsonResponse
    {
        $categories = $this->categoryRepository->getAllActive();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Categorias ativas listadas'
        );
    }
}
