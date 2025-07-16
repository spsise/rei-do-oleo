<?php

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Models\Product;
use App\Domain\Service\Models\ServiceItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
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

    public function getTopSellingProducts(int $limit = 10, ?int $serviceCenterId = null): Collection
    {
        $query = Product::select([
                'products.*',
                DB::raw('COALESCE(SUM(service_items.quantity), 0) as total_quantity_sold'),
                DB::raw('COALESCE(SUM(service_items.total_price), 0) as total_revenue'),
                DB::raw('COUNT(DISTINCT service_items.service_id) as sales_count')
            ])
            ->leftJoin('service_items', 'products.id', '=', 'service_items.product_id')
            ->leftJoin('services', 'service_items.service_id', '=', 'services.id')
            ->where('products.active', true)
            ->groupBy('products.id', 'products.category_id', 'products.name', 'products.slug', 'products.description', 'products.sku', 'products.price', 'products.stock_quantity', 'products.min_stock', 'products.unit', 'products.active', 'products.created_at', 'products.updated_at')
            ->orderBy('total_quantity_sold', 'desc')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit);

        if ($serviceCenterId) {
            $query->where('services.service_center_id', $serviceCenterId);
        }

        return $query->with('category')->get();
    }

    public function getProductSalesStats(string $period = '30d', ?int $serviceCenterId = null): array
    {
        $dateFilter = match($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
            default => now()->subDays(30)
        };

        $query = Product::select([
                'products.id',
                'products.name',
                DB::raw('COALESCE(SUM(service_items.quantity), 0) as total_quantity_sold'),
                DB::raw('COALESCE(SUM(service_items.total_price), 0) as total_revenue'),
                DB::raw('COUNT(DISTINCT service_items.service_id) as sales_count')
            ])
            ->leftJoin('service_items', 'products.id', '=', 'service_items.product_id')
            ->leftJoin('services', 'service_items.service_id', '=', 'services.id')
            ->where('products.active', true)
            ->where('service_items.created_at', '>=', $dateFilter)
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity_sold', 'desc')
            ->orderBy('total_revenue', 'desc');

        if ($serviceCenterId) {
            $query->where('services.service_center_id', $serviceCenterId);
        }

        return $query->get()->toArray();
    }

    public function getProductsWithSalesData(int $limit = 10, ?int $serviceCenterId = null): Collection
    {
        $query = Product::select([
                'products.*',
                DB::raw('COALESCE(SUM(service_items.quantity), 0) as total_quantity_sold'),
                DB::raw('COALESCE(SUM(service_items.total_price), 0) as total_revenue'),
                DB::raw('COUNT(DISTINCT service_items.service_id) as sales_count')
            ])
            ->leftJoin('service_items', 'products.id', '=', 'service_items.product_id')
            ->leftJoin('services', 'service_items.service_id', '=', 'services.id')
            ->where('products.active', true)
            ->groupBy('products.id', 'products.category_id', 'products.name', 'products.slug', 'products.description', 'products.sku', 'products.price', 'products.stock_quantity', 'products.min_stock', 'products.unit', 'products.active', 'products.created_at', 'products.updated_at')
            ->orderBy('total_quantity_sold', 'desc')
            ->limit($limit);

        if ($serviceCenterId) {
            $query->where('services.service_center_id', $serviceCenterId);
        }

        return $query->with('category')->get();
    }
}
