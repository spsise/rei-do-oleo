<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Client\Repositories\VehicleRepositoryInterface;
use App\Domain\Vehicle\Services\VehicleService;
use App\Http\Resources\VehicleResource;
use App\Http\Requests\Api\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Api\Vehicle\UpdateVehicleRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VehicleController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private VehicleRepositoryInterface $vehicleRepository,
        private VehicleService $vehicleService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles",
     *     tags={"Veículos"},
     *     summary="Listar veículos",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="client_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lista de veículos obtida com sucesso")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'client_id', 'brand', 'active', 'per_page']);
        $vehicles = $this->vehicleRepository->searchByFilters($filters);

        return $this->successResponse(
            VehicleResource::collection($vehicles),
            'Veículos listados com sucesso'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vehicles",
     *     tags={"Veículos"},
     *     summary="Criar novo veículo",
     *     description="Cadastra um novo veículo para um cliente",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id","brand","model","year","license_plate"},
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="brand", type="string", example="Toyota"),
     *             @OA\Property(property="model", type="string", example="Corolla"),
     *             @OA\Property(property="year", type="integer", example=2020),
     *             @OA\Property(property="license_plate", type="string", example="ABC1234"),
     *             @OA\Property(property="color", type="string", example="Prata"),
     *             @OA\Property(property="mileage", type="integer", example=50000),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Veículo criado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = $this->vehicleRepository->create($request->validated());

        return $this->successResponse(
            new VehicleResource($vehicle),
            'Veículo criado com sucesso',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles/{id}",
     *     tags={"Veículos"},
     *     summary="Obter veículo específico",
     *     description="Retorna os dados de um veículo específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Veículo encontrado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Veículo não encontrado"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $vehicle = $this->vehicleRepository->find($id);

        if (!$vehicle) {
            return $this->errorResponse('Veículo não encontrado', 404);
        }

        return $this->successResponse(
            new VehicleResource($vehicle),
            'Veículo encontrado'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vehicles/{id}",
     *     tags={"Veículos"},
     *     summary="Atualizar veículo",
     *     description="Atualiza os dados de um veículo existente",
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
     *             @OA\Property(property="brand", type="string", example="Honda"),
     *             @OA\Property(property="model", type="string", example="Civic"),
     *             @OA\Property(property="year", type="integer", example=2021),
     *             @OA\Property(property="license_plate", type="string", example="XYZ5678"),
     *             @OA\Property(property="color", type="string", example="Azul"),
     *             @OA\Property(property="mileage", type="integer", example=45000),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Veículo atualizado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Veículo não encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function update(UpdateVehicleRequest $request, int $id): JsonResponse
    {
        $vehicle = $this->vehicleRepository->update($id, $request->validated());

        if (!$vehicle) {
            return $this->errorResponse('Veículo não encontrado', 404);
        }

        return $this->successResponse(
            new VehicleResource($vehicle),
            'Veículo atualizado com sucesso'
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vehicles/{id}",
     *     tags={"Veículos"},
     *     summary="Excluir veículo",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Veículo excluído com sucesso")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->vehicleRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Veículo não encontrado', 404);
        }

        return $this->successResponse(null, 'Veículo excluído com sucesso');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vehicles/search/license-plate",
     *     tags={"Veículos"},
     *     summary="Buscar veículo por placa",
     *     description="Busca um veículo específico pelo número da placa",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"license_plate"},
     *             @OA\Property(property="license_plate", type="string", example="ABC1234", description="Placa do veículo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Veículo encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Veículo encontrado por placa"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="brand", type="string", example="Toyota"),
     *                 @OA\Property(property="model", type="string", example="Corolla"),
     *                 @OA\Property(property="license_plate", type="string", example="ABC1234"),
     *                 @OA\Property(property="client_name", type="string", example="João Silva")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Veículo não encontrado"
     *     )
     * )
     */
    public function findByLicensePlate(Request $request): JsonResponse
    {
        $request->validate(['license_plate' => 'required|string|max:8']);

        $vehicle = $this->vehicleRepository->findByLicensePlate($request->license_plate);

        if (!$vehicle) {
            return $this->errorResponse('Veículo não encontrado', 404);
        }

        return $this->successResponse(
            new VehicleResource($vehicle),
            'Veículo encontrado por placa'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vehicles/{id}/mileage",
     *     tags={"Veículos"},
     *     summary="Atualizar quilometragem do veículo",
     *     description="Atualiza a quilometragem atual do veículo",
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
     *             required={"mileage"},
     *             @OA\Property(property="mileage", type="integer", example=55000, description="Nova quilometragem do veículo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quilometragem atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Quilometragem atualizada com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Veículo não encontrado ou quilometragem inválida"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function updateMileage(Request $request, int $id): JsonResponse
    {
        $request->validate(['mileage' => 'required|integer|min:0']);

        $result = $this->vehicleRepository->updateMileage($id, $request->mileage);

        if (!$result) {
            return $this->errorResponse('Veículo não encontrado ou quilometragem inválida', 400);
        }

        return $this->successResponse(null, 'Quilometragem atualizada com sucesso');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles/client/{clientId}",
     *     tags={"Veículos"},
     *     summary="Listar veículos de um cliente",
     *     description="Retorna todos os veículos de um cliente específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="clientId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Veículos do cliente listados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Veículos do cliente listados"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="brand", type="string", example="Toyota"),
     *                     @OA\Property(property="model", type="string", example="Corolla"),
     *                     @OA\Property(property="year", type="integer", example=2020),
     *                     @OA\Property(property="license_plate", type="string", example="ABC1234"),
     *                     @OA\Property(property="color", type="string", example="Prata"),
     *                     @OA\Property(property="mileage", type="integer", example=50000)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getByClient(int $clientId): JsonResponse
    {
        $vehicles = $this->vehicleRepository->getByClient($clientId);

        return $this->successResponse(
            VehicleResource::collection($vehicles),
            'Veículos do cliente listados'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles/dashboard/stats",
     *     tags={"Veículos"},
     *     summary="Obter estatísticas do dashboard de veículos",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="service_center_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Estatísticas obtidas com sucesso")
     * )
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        $serviceCenterId = $request->get('service_center_id');
        $stats = $this->vehicleService->getDashboardStats($serviceCenterId);

        return $this->successResponse($stats, 'Estatísticas obtidas com sucesso');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles/chart-data",
     *     tags={"Veículos"},
     *     summary="Obter dados para gráficos de veículos",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="service_center_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="period", in="query", required=false, @OA\Schema(type="string", enum={"7d", "30d", "90d", "1y"})),
     *     @OA\Response(response=200, description="Dados obtidos com sucesso")
     * )
     */
    public function getChartData(Request $request): JsonResponse
    {
        $serviceCenterId = $request->get('service_center_id');
        $period = $request->get('period', '30d');
        $chartData = $this->vehicleService->getVehiclesChartData($serviceCenterId, $period);

        return $this->successResponse($chartData, 'Dados do gráfico obtidos com sucesso');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles/recent",
     *     tags={"Veículos"},
     *     summary="Obter veículos recentes",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="service_center_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Veículos recentes obtidos com sucesso")
     * )
     */
    public function getRecentVehicles(Request $request): JsonResponse
    {
        $serviceCenterId = $request->get('service_center_id');
        $limit = $request->get('limit', 10);
        $recentVehicles = $this->vehicleService->getRecentVehicles($serviceCenterId, $limit);

        return $this->successResponse($recentVehicles, 'Veículos recentes obtidos com sucesso');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles/service-stats",
     *     tags={"Veículos"},
     *     summary="Obter veículos com estatísticas de serviços",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="service_center_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Veículos com estatísticas obtidos com sucesso")
     * )
     */
    public function getVehiclesWithServiceStats(Request $request): JsonResponse
    {
        $serviceCenterId = $request->get('service_center_id');
        $limit = $request->get('limit', 10);
        $vehicles = $this->vehicleService->getVehiclesWithServiceStats($serviceCenterId, $limit);

        return $this->successResponse($vehicles, 'Veículos com estatísticas obtidos com sucesso');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles/performance-metrics",
     *     tags={"Veículos"},
     *     summary="Obter métricas de performance de veículos",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="service_center_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Métricas obtidas com sucesso")
     * )
     */
    public function getPerformanceMetrics(Request $request): JsonResponse
    {
        $serviceCenterId = $request->get('service_center_id');
        $metrics = $this->vehicleService->getVehiclePerformanceMetrics($serviceCenterId);

        return $this->successResponse($metrics, 'Métricas de performance obtidas com sucesso');
    }
}
