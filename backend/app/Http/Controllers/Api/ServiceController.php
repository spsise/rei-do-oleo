<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Service\Repositories\ServiceRepositoryInterface;
use App\Domain\Service\Services\ServiceService;
use App\Http\Resources\ServiceResource;
use App\Traits\ApiResponseTrait;
use App\Traits\ServiceDataMappingTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    use ApiResponseTrait, ServiceDataMappingTrait;

    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private ServiceService $serviceService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/services",
     *     tags={"Serviços"},
     *     summary="Listar serviços",
     *     description="Lista todos os serviços com opções de filtro",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="service_center_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="client_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="vehicle_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="technician_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Response(response=200, description="Lista de serviços obtida com sucesso")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'service_center_id', 'client_id', 'vehicle_id',
            'status', 'technician_id', 'date_from', 'date_to', 'per_page'
        ]);

        $services = $this->serviceRepository->searchByFilters($filters);

        return $this->successResponse(
            ServiceResource::collection($services),
            'Serviços listados com sucesso'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/services",
     *     tags={"Serviços"},
     *     summary="Criar novo serviço",
     *     description="Cria um novo serviço no sistema",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"service_center_id","client_id","vehicle_id","description","status_id"},
     *             @OA\Property(property="service_center_id", type="integer", example=1),
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="vehicle_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Troca de óleo e filtro"),
     *             @OA\Property(property="complaint", type="string", example="Motor fazendo ruído"),
     *             @OA\Property(property="technician_id", type="integer", example=2),
     *             @OA\Property(property="status_id", type="integer", example=1),
     *             @OA\Property(property="scheduled_date", type="string", format="date-time"),
     *             @OA\Property(property="labor_cost", type="number", format="float", example=150.00),
     *             @OA\Property(property="mileage", type="integer", example=50000)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Serviço criado com sucesso"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_center_id' => 'required|exists:service_centers,id',
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_number' => 'nullable|string|max:20|unique:services,service_number',
            'description' => 'required|string',
            'complaint' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'solution' => 'nullable|string',
            'scheduled_date' => 'nullable|date',
            'started_at' => 'nullable|date',
            'finished_at' => 'nullable|date',
            'technician_id' => 'nullable|exists:users,id',
            'attendant_id' => 'nullable|exists:users,id',
            'status_id' => 'required|exists:service_statuses,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'labor_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'mileage' => 'nullable|integer|min:0',
            'fuel_level' => ['nullable', Rule::in(['empty', '1/4', '1/2', '3/4', 'full'])],
            'observations' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'warranty_months' => 'nullable|integer|min:0',
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0'
        ]);

        // Map frontend field names to backend field names
        $validated = $this->mapServiceDataForUpdate($validated);

        // Add user_id to the validated data
        $validated['user_id'] = Auth::user()->id;

        $service = $this->serviceService->createService($validated);

        return $this->successResponse(
            new ServiceResource($service),
            'Serviço criado com sucesso',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/{id}",
     *     tags={"Serviços"},
     *     summary="Obter serviço específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Serviço encontrado"),
     *     @OA\Response(response=404, description="Serviço não encontrado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $service = $this->serviceRepository->find($id);

        if (!$service) {
            return $this->errorResponse('Serviço não encontrado', 404);
        }

        return $this->successResponse(
            new ServiceResource($service),
            'Serviço encontrado'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/services/{id}",
     *     tags={"Serviços"},
     *     summary="Atualizar serviço",
     *     description="Atualiza os dados de um serviço existente",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="description", type="string", example="Troca de óleo e filtro - atualizado"),
     *             @OA\Property(property="complaint", type="string", example="Motor fazendo ruído estranho"),
     *             @OA\Property(property="diagnosis", type="string", example="Óleo vencido e filtro entupido"),
     *             @OA\Property(property="solution", type="string", example="Substituição completa do óleo e filtro"),
     *             @OA\Property(property="technician_id", type="integer", example=2),
     *             @OA\Property(property="status_id", type="integer", example=2),
     *             @OA\Property(property="labor_cost", type="number", format="float", example=150.00),
     *             @OA\Property(property="discount", type="number", format="float", example=10.00),
     *             @OA\Property(property="priority", type="string", enum={"low","normal","high","urgent"}, example="normal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Serviço atualizado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Serviço não encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'service_center_id' => 'sometimes|exists:service_centers,id',
            'client_id' => 'sometimes|exists:clients,id',
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'service_number' => ['sometimes', 'string', 'max:20', Rule::unique('services')->ignore($id)],
            'description' => 'sometimes|string',
            'complaint' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'solution' => 'nullable|string',
            'scheduled_date' => 'nullable|date',
            'started_at' => 'nullable|date',
            'finished_at' => 'nullable|date',
            'technician_id' => 'nullable|exists:users,id',
            'attendant_id' => 'nullable|exists:users,id',
            'status_id' => 'sometimes|exists:service_statuses,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'labor_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'mileage' => 'nullable|integer|min:0',
            'fuel_level' => ['nullable', Rule::in(['empty', '1/4', '1/2', '3/4', 'full'])],
            'observations' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'warranty_months' => 'nullable|integer|min:0',
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])]
        ]);

        // Map frontend field names to backend field names
        $validated = $this->mapServiceDataForUpdate($validated);

        $service = $this->serviceRepository->update($id, $validated);

        if (!$service) {
            return $this->errorResponse('Serviço não encontrado', 404);
        }

        return $this->successResponse(
            new ServiceResource($service),
            'Serviço atualizado com sucesso'
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/services/{id}",
     *     tags={"Serviços"},
     *     summary="Excluir serviço",
     *     description="Remove um serviço do sistema",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Serviço excluído com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Serviço não encontrado"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->serviceRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Serviço não encontrado', 404);
        }

        return $this->successResponse(null, 'Serviço excluído com sucesso');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/service-center/{serviceCenterId}",
     *     tags={"Serviços"},
     *     summary="Listar serviços por centro de serviço",
     *     description="Retorna todos os serviços de um centro de serviço específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="serviceCenterId",
     *         in="path",
     *         required=true,
     *         description="ID do centro de serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Serviços do centro de serviço listados com sucesso"
     *     )
     * )
     */
    public function getByServiceCenter(int $serviceCenterId): JsonResponse
    {
        $services = $this->serviceRepository->getServicesByCenter($serviceCenterId);

        return $this->successResponse(
            ServiceResource::collection($services),
            'Serviços do centro de serviço listados'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/client/{clientId}",
     *     tags={"Serviços"},
     *     summary="Listar serviços de um cliente",
     *     description="Retorna todos os serviços de um cliente específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="clientId",
     *         in="path",
     *         required=true,
     *         description="ID do cliente",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Serviços do cliente listados com sucesso"
     *     )
     * )
     */
    public function getByClient(int $clientId): JsonResponse
    {
        $services = $this->serviceRepository->getServicesByClient($clientId);

        return $this->successResponse(
            ServiceResource::collection($services),
            'Serviços do cliente listados'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/vehicle/{vehicleId}",
     *     tags={"Serviços"},
     *     summary="Histórico de serviços por veículo",
     *     description="Retorna todo o histórico de serviços de um veículo específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="vehicleId",
     *         in="path",
     *         required=true,
     *         description="ID do veículo",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Histórico de serviços do veículo obtido com sucesso"
     *     )
     * )
     */
    public function getByVehicle(int $vehicleId): JsonResponse
    {
        $services = $this->serviceRepository->getByVehicle($vehicleId);

        return $this->successResponse(
            ServiceResource::collection($services),
            'Histórico de serviços do veículo'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/technician/{technicianId}",
     *     tags={"Serviços"},
     *     summary="Listar serviços de um técnico",
     *     description="Retorna todos os serviços realizados por um técnico específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="technicianId",
     *         in="path",
     *         required=true,
     *         description="ID do técnico",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Serviços do técnico listados com sucesso"
     *     )
     * )
     */
    public function getByTechnician(int $technicianId): JsonResponse
    {
        $services = $this->serviceRepository->getByTechnician($technicianId);

        return $this->successResponse(
            ServiceResource::collection($services),
            'Serviços do técnico listados'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/services/search/number",
     *     tags={"Serviços"},
     *     summary="Buscar serviço por número",
     *     description="Busca um serviço específico pelo seu número de identificação",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"service_number"},
     *             @OA\Property(property="service_number", type="string", example="OS-2023-001", description="Número do serviço")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Serviço encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Serviço encontrado"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="service_number", type="string", example="OS-2023-001"),
     *                 @OA\Property(property="client_name", type="string", example="João Silva"),
     *                 @OA\Property(property="vehicle", type="string", example="Toyota Corolla"),
     *                 @OA\Property(property="status", type="string", example="em_andamento")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Serviço não encontrado"
     *     )
     * )
     */
    public function searchByServiceNumber(Request $request): JsonResponse
    {
        $request->validate(['service_number' => 'required|string|max:50']);

        $service = $this->serviceRepository->findByServiceNumber($request->service_number);

        if (!$service) {
            return $this->errorResponse('Serviço não encontrado', 404);
        }

        return $this->successResponse(
            new ServiceResource($service),
            'Serviço encontrado'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/services/{id}/status",
     *     tags={"Serviços"},
     *     summary="Atualizar status do serviço",
     *     description="Atualiza o status de um serviço específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status_id"},
     *             @OA\Property(property="status_id", type="integer", example=2, description="ID do novo status"),
     *             @OA\Property(property="notes", type="string", example="Serviço em andamento", description="Observações sobre a mudança de status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status do serviço atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Status do serviço atualizado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Serviço não encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status_id' => 'required|exists:service_statuses,id',
            'notes' => 'nullable|string'
        ]);

        $updated = $this->serviceService->updateStatus($id, $request->status_id, $request->notes);

        if (!$updated) {
            return $this->errorResponse('Serviço não encontrado', 404);
        }

        return $this->successResponse(null, 'Status do serviço atualizado');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/dashboard/stats",
     *     tags={"Serviços"},
     *     summary="Estatísticas do dashboard de serviços",
     *     description="Retorna estatísticas gerais de serviços para um centro de serviço",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="service_center_id",
     *         in="query",
     *         required=false,
     *         description="ID do centro de serviço para filtrar estatísticas",
     *         @OA\Schema(type="integer")
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
     *                 @OA\Property(property="total_services", type="integer", example=150),
     *                 @OA\Property(property="services_in_progress", type="integer", example=25),
     *                 @OA\Property(property="services_completed", type="integer", example=100),
     *                 @OA\Property(property="services_cancelled", type="integer", example=25),
     *                 @OA\Property(property="total_revenue", type="number", format="float", example=15000.50),
     *                 @OA\Property(property="average_service_duration", type="number", format="float", example=2.5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao obter estatísticas"
     *     )
     * )
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        $serviceCenterId = $request->get('service_center_id');
        $stats = $this->serviceRepository->getDashboardStats($serviceCenterId);

        return $this->successResponse($stats, 'Estatísticas do dashboard');
    }
}
