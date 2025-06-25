<?php

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAllActive(): Collection
    {
        return Product::getActiveCached();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return Product::byCategory($categoryId)->active()->with('category')->get();
    }

    public function searchByName(string $name): Collection
    {
        return Product::search($name)->active()->limit(10)->get();
    }

    public function getLowStockProducts(): Collection
    {
        return Product::lowStock()->with('category')->get();
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(Product::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::partial('sku'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('active'),
            ])
            ->allowedSorts(['name', 'price', 'stock_quantity', 'created_at'])
            ->with(['category'])
            ->paginate($perPage);
    }

    public function searchByFilters(array $filters): LengthAwarePaginator
    {
        $query = Product::with(['category']);

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function find(int $id): ?Product
    {
        return Product::with(['category', 'serviceItems'])->find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): ?Product
    {
        $product = Product::find($id);
        if (!$product) return null;

        $product->update($data);
        return $product->fresh(['category']);
    }

    public function delete(int $id): bool
    {
        $product = Product::find($id);
        return $product ? $product->delete() : false;
    }

    public function getLowStock(): Collection
    {
        return Product::lowStock()->with('category')->get();
    }

    public function updateStock(int $id, int $quantity, string $type): bool
    {
        $product = Product::find($id);
        if (!$product) return false;

        switch ($type) {
            case 'add':
                $product->increment('stock_quantity', $quantity);
                break;
            case 'subtract':
                if ($product->stock_quantity < $quantity) return false;
                $product->decrement('stock_quantity', $quantity);
                break;
            case 'set':
                $product->update(['stock_quantity' => $quantity]);
                break;
            default:
                return false;
        }

        $product->clearCache();
        return true;
    }
}
