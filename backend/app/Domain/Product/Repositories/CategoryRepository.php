<?php

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAllActive(): Collection
    {
        return Category::getActiveCached();
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(Category::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('active'),
            ])
            ->allowedSorts(['sort_order', 'name', 'created_at'])
            ->withCount(['products', 'activeProducts'])
            ->paginate($perPage);
    }

    public function searchByFilters(array $filters): LengthAwarePaginator
    {
        $query = Category::withCount(['products', 'activeProducts']);

        if (isset($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->ordered()->paginate($filters['per_page'] ?? 15);
    }

    public function find(int $id): ?Category
    {
        return Category::with(['products'])->find($id);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(int $id, array $data): ?Category
    {
        $category = Category::find($id);
        if (!$category) return null;

        $category->update($data);
        return $category->fresh(['products']);
    }

    public function delete(int $id): bool
    {
        $category = Category::find($id);
        return $category ? $category->delete() : false;
    }
}
