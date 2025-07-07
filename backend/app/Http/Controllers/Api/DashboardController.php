<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Service\Services\ServiceService;
use App\Domain\Client\Services\ClientService;
use App\Domain\Product\Services\ProductService;
use App\Domain\Vehicle\Services\VehicleService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="API Endpoints para Dashboard e Estatísticas"
 * )
 */
class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private ServiceService $serviceService,
        private ClientService $clientService,
        private ProductService $productService,
        private VehicleService $vehicleService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/overview",
     *     operationId="getDashboardOverview",
     *     tags={"Dashboard"},
     *     summary="Obter visão geral do dashboard",
     *     description="Retorna estatísticas gerais do sistema incluindo clientes, veículos, serviços e produtos",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="service_center_id",
     *         in="query",
     *         description="ID do centro de serviço (opcional)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Período para as estatísticas (today, week, month)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"today", "week", "month"}, default="today")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estatísticas do dashboard obtidas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Estatísticas do dashboard"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_clients", type="integer", example=1250),
     *                 @OA\Property(property="total_vehicles", type="integer", example=1890),
     *                 @OA\Property(property="total_services", type="integer", example=3420),
     *                 @OA\Property(property="total_products", type="integer", example=156),
     *                 @OA\Property(property="total_revenue", type="number", format="float", example=125000.00),
     *                 @OA\Property(property="services_this_month", type="integer", example=156),
     *                 @OA\Property(property="revenue_this_month", type="number", format="float", example=18500.00),
     *                 @OA\Property(property="low_stock_products", type="integer", example=12),
     *                 @OA\Property(property="pending_services", type="integer", example=8),
     *                 @OA\Property(property="completed_services_today", type="integer", example=15),
     *                 @OA\Property(
     *                     property="recent_services",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="service_number", type="string", example="SRV-2024-001"),
     *                         @OA\Property(property="client_name", type="string", example="João Silva"),
     *                         @OA\Property(property="vehicle_plate", type="string", example="ABC-1234"),
     *                         @OA\Property(property="status", type="string", example="completed"),
     *                         @OA\Property(property="total", type="number", format="float", example=450.00),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="top_products",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Óleo Motor 5W30"),
     *                         @OA\Property(property="sales_count", type="integer", example=45),
     *                         @OA\Property(property="revenue", type="number", format="float", example=2250.00)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
    public function getOverview(Request $request): JsonResponse
    {
        try {
            $serviceCenterId = $request->query('service_center_id');
            $period = $request->query('period', 'today');

            $serviceStats = $this->serviceService->getDashboardMetrics($serviceCenterId, $period);

            $clientStats = $this->clientService->getDashboardStats($serviceCenterId);

            $productStats = $this->productService->getDashboardStats($serviceCenterId);

            $vehicleStats = $this->vehicleService->getDashboardStats($serviceCenterId);

            $overview = [
                'total_clients' => $clientStats['total_clients'] ?? 0,
                'total_vehicles' => $vehicleStats['total_vehicles'] ?? 0,
                'total_services' => $serviceStats['total_services'] ?? 0,
                'total_products' => $productStats['total_products'] ?? 0,
                'total_revenue' => $serviceStats['total_revenue'] ?? 0,
                'services_this_month' => $serviceStats['services_this_month'] ?? 0,
                'revenue_this_month' => $serviceStats['revenue_this_month'] ?? 0,
                'low_stock_products' => $productStats['low_stock_count'] ?? 0,
                'pending_services' => $serviceStats['pending_services'] ?? 0,
                'completed_services_today' => $serviceStats['completed_today'] ?? 0,
                'recent_services' => $serviceStats['recent_services'] ?? [],
                'top_products' => $productStats['top_products'] ?? [],
                'service_trends' => $serviceStats['trends'] ?? [],
                'revenue_trends' => $serviceStats['revenue_trends'] ?? []
            ];

            return $this->successResponse($overview, 'Estatísticas do dashboard obtidas com sucesso');

        } catch (\Exception $e) {
            return $this->errorResponse('Erro ao obter estatísticas do dashboard: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/charts",
     *     operationId="getDashboardCharts",
     *     tags={"Dashboard"},
     *     summary="Obter dados para gráficos do dashboard",
     *     description="Retorna dados formatados para gráficos de serviços, receita e produtos",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="service_center_id",
     *         in="query",
     *         description="ID do centro de serviço (opcional)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Período para os dados (7d, 30d, 90d)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"7d", "30d", "90d"}, default="30d")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dados dos gráficos obtidos com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Dados dos gráficos"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="services_chart",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="date", type="string", example="2024-01-15"),
     *                         @OA\Property(property="completed", type="integer", example=15),
     *                         @OA\Property(property="pending", type="integer", example=8),
     *                         @OA\Property(property="cancelled", type="integer", example=2)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="revenue_chart",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="date", type="string", example="2024-01-15"),
     *                         @OA\Property(property="revenue", type="number", format="float", example=1850.00)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="products_chart",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Óleo Motor 5W30"),
     *                         @OA\Property(property="sales", type="integer", example=45),
     *                         @OA\Property(property="revenue", type="number", format="float", example=2250.00)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getCharts(Request $request): JsonResponse
    {
        try {
            $serviceCenterId = $request->query('service_center_id');
            $period = $request->query('period', '30d');

            $servicesChart = $this->serviceService->getServicesChartData($serviceCenterId, $period);

            $revenueChart = $this->serviceService->getRevenueChartData($serviceCenterId, $period);

            $productsChart = $this->productService->getProductsChartData($serviceCenterId, $period);

            $chartsData = [
                'services_chart' => $servicesChart,
                'revenue_chart' => $revenueChart,
                'products_chart' => $productsChart
            ];

            return $this->successResponse($chartsData, 'Dados dos gráficos obtidos com sucesso');

        } catch (\Exception $e) {
            return $this->errorResponse('Erro ao obter dados dos gráficos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/alerts",
     *     operationId="getDashboardAlerts",
     *     tags={"Dashboard"},
     *     summary="Obter alertas do dashboard",
     *     description="Retorna alertas importantes como produtos com estoque baixo, serviços pendentes, etc.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="service_center_id",
     *         in="query",
     *         description="ID do centro de serviço (opcional)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alertas obtidos com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Alertas do dashboard"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="type", type="string", example="low_stock"),
     *                     @OA\Property(property="title", type="string", example="Produto com estoque baixo"),
     *                     @OA\Property(property="message", type="string", example="Óleo Motor 5W30 está com estoque baixo"),
     *                     @OA\Property(property="severity", type="string", example="warning"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getAlerts(Request $request): JsonResponse
    {
        try {
            $serviceCenterId = $request->query('service_center_id');

            $alerts = [];

            $lowStockProducts = $this->productService->getLowStockProducts($serviceCenterId);
            foreach ($lowStockProducts as $product) {
                $alerts[] = [
                    'type' => 'low_stock',
                    'title' => 'Produto com estoque baixo',
                    'message' => "{$product->name} está com apenas {$product->stock_quantity} unidades em estoque",
                    'severity' => 'warning',
                    'created_at' => now()->toISOString()
                ];
            }

            $pendingServices = $this->serviceService->getLongPendingServices($serviceCenterId);
            foreach ($pendingServices as $service) {
                $alerts[] = [
                    'type' => 'pending_service',
                    'title' => 'Serviço pendente há muito tempo',
                    'message' => "Serviço #{$service->service_number} está pendente há {$service->days_pending} dias",
                    'severity' => 'info',
                    'created_at' => now()->toISOString()
                ];
            }

            return $this->successResponse($alerts, 'Alertas do dashboard obtidos com sucesso');

        } catch (\Exception $e) {
            return $this->errorResponse('Erro ao obter alertas: ' . $e->getMessage(), 500);
        }
    }
} 