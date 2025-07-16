<?php

namespace App\Domain\Product\Services;

use App\Domain\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get dashboard statistics for products
     */
    public function getDashboardStats(?int $serviceCenterId = null): array
    {
        $cacheKey = "product_dashboard_stats_{$serviceCenterId}";
        
        return Cache::remember($cacheKey, 300, function () use ($serviceCenterId) {
            $totalProducts = $this->productRepository->getAllActive()->count();
            
            $lowStockProducts = $this->productRepository->getLowStockProducts();
            $lowStockCount = $lowStockProducts->count();
            
            $topProducts = $this->getTopProducts($serviceCenterId);
            
            return [
                'total_products' => $totalProducts,
                'low_stock_count' => $lowStockCount,
                'top_products' => $topProducts,
            ];
        });
    }

    /**
     * Get top selling products based on service items
     */
    private function getTopProducts(?int $serviceCenterId = null): array
    {
        $topSellingProducts = $this->productRepository->getTopSellingProducts(5, $serviceCenterId);
        
        return $topSellingProducts->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sales_count' => (int) $product->sales_count,
                'revenue' => (float) $product->total_revenue,
                'quantity_sold' => (int) $product->total_quantity_sold,
                'category' => $product->category?->name ?? 'Sem categoria',
            ];
        })->toArray();
    }

    /**
     * Get product chart data for dashboard
     */
    public function getProductsChartData(?int $serviceCenterId = null, string $period = '30d'): array
    {
        $cacheKey = "product_chart_data_{$serviceCenterId}_{$period}";
        
        return Cache::remember($cacheKey, 600, function () use ($serviceCenterId, $period) {
            $salesStats = $this->productRepository->getProductSalesStats($period, $serviceCenterId);
            
            return collect($salesStats)->take(10)->map(function ($product) {
                return [
                    'name' => $product['name'],
                    'sales' => (int) $product['sales_count'],
                    'revenue' => (float) $product['total_revenue'],
                    'quantity_sold' => (int) $product['total_quantity_sold'],
                ];
            })->toArray();
        });
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(?int $serviceCenterId = null): array
    {
        return $this->productRepository->getLowStockProducts()->toArray();
    }

    /**
     * Get products with sales data for detailed analysis
     */
    public function getProductsWithSalesData(?int $serviceCenterId = null, int $limit = 10): array
    {
        $products = $this->productRepository->getProductsWithSalesData($limit, $serviceCenterId);
        
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
                'stock_quantity' => (int) $product->stock_quantity,
                'min_stock' => (int) $product->min_stock,
                'category' => $product->category?->name ?? 'Sem categoria',
                'total_quantity_sold' => (int) $product->total_quantity_sold,
                'total_revenue' => (float) $product->total_revenue,
                'sales_count' => (int) $product->sales_count,
                'stock_status' => $this->getStockStatus($product),
                'is_low_stock' => $product->stock_quantity <= $product->min_stock,
            ];
        })->toArray();
    }

    /**
     * Get product performance metrics
     */
    public function getProductPerformanceMetrics(?int $serviceCenterId = null): array
    {
        $cacheKey = "product_performance_metrics_{$serviceCenterId}";
        
        return Cache::remember($cacheKey, 600, function () use ($serviceCenterId) {
            $products = $this->productRepository->getProductsWithSalesData(50, $serviceCenterId);
            
            $totalRevenue = $products->sum('total_revenue');
            $totalQuantitySold = $products->sum('total_quantity_sold');
            $totalSalesCount = $products->sum('sales_count');
            
            $lowStockProducts = $products->filter(function ($product) {
                return $product->stock_quantity <= $product->min_stock;
            });
            
            $outOfStockProducts = $products->filter(function ($product) {
                return $product->stock_quantity === 0;
            });
            
            return [
                'total_revenue' => (float) $totalRevenue,
                'total_quantity_sold' => (int) $totalQuantitySold,
                'total_sales_count' => (int) $totalSalesCount,
                'low_stock_count' => $lowStockProducts->count(),
                'out_of_stock_count' => $outOfStockProducts->count(),
                'average_revenue_per_product' => $products->count() > 0 ? (float) ($totalRevenue / $products->count()) : 0,
                'average_quantity_per_sale' => $totalSalesCount > 0 ? (float) ($totalQuantitySold / $totalSalesCount) : 0,
            ];
        });
    }

    /**
     * Get stock status for a product
     */
    private function getStockStatus($product): string
    {
        if ($product->stock_quantity === 0) {
            return 'out_of_stock';
        }

        if ($product->min_stock && $product->stock_quantity <= $product->min_stock) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Clear product cache
     */
    public function clearCache(?int $serviceCenterId = null): void
    {
        Cache::forget("product_dashboard_stats_{$serviceCenterId}");
        Cache::forget("product_chart_data_{$serviceCenterId}_7d");
        Cache::forget("product_chart_data_{$serviceCenterId}_30d");
        Cache::forget("product_chart_data_{$serviceCenterId}_90d");
        Cache::forget("product_chart_data_{$serviceCenterId}_1y");
        Cache::forget("product_performance_metrics_{$serviceCenterId}");
    }
} 