<?php

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function getAllActive(): Collection;

    public function getByCategory(int $categoryId): Collection;

    public function searchByName(string $name): Collection;

    public function getLowStockProducts(): Collection;

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function searchByFilters(array $filters): LengthAwarePaginator;

    public function find(int $id): ?Product;

    public function create(array $data): Product;

    public function update(int $id, array $data): ?Product;

    public function delete(int $id): bool;

    public function getLowStock(): Collection;

    public function updateStock(int $id, int $quantity, string $type): bool;

    /**
     * Get top selling products based on service items
     */
    public function getTopSellingProducts(int $limit = 10, ?int $serviceCenterId = null): Collection;

    /**
     * Get product sales statistics for charts
     */
    public function getProductSalesStats(string $period = '30d', ?int $serviceCenterId = null): array;

    /**
     * Get products with sales data for dashboard
     */
    public function getProductsWithSalesData(int $limit = 10, ?int $serviceCenterId = null): Collection;
}
