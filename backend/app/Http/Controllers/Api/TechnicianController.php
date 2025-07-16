<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Repositories\VehicleRepositoryInterface;
use App\Domain\Service\Repositories\ServiceRepositoryInterface;
use App\Domain\Service\Services\ServiceService;
use App\Http\Resources\TechnicianSearchResource;
use App\Http\Resources\ServiceResource;
use App\Http\Requests\Api\Service\StoreServiceRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TechnicianController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private VehicleRepositoryInterface $vehicleRepository,
        private ServiceRepositoryInterface $serviceRepository,
        private ServiceService $serviceService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/technician/search",
     *     tags={"Técnico"},
     *     summary="Buscar cliente por placa ou documento",
     *     description="Busca um cliente específico pela placa do veículo ou documento (CPF/CNPJ)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"search_type", "search_value"},
     *             @OA\Property(property="search_type", type="string", enum={"license_plate", "document"}, example="license_plate", description="Tipo de busca: placa ou documento"),
     *             @OA\Property(property="search_value", type="string", example="ABC1234", description="Valor para busca (placa ou documento)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cliente encontrado"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="client", type="object"),
     *                 @OA\Property(property="vehicles", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="recent_services", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cliente não encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'search_type' => 'required|in:license_plate,document',
            'search_value' => 'required|string|max:18'
        ]);

        $searchType = $request->search_type;
        $searchValue = $request->search_value;

        if ($searchType === 'license_plate') {
            // Buscar por placa
            $vehicle = $this->vehicleRepository->findByLicensePlate($searchValue);

            if (!$vehicle) {
                return $this->errorResponse('Veículo não encontrado', 404);
            }

            $client = $vehicle->client;
            $vehicles = $this->vehicleRepository->getByClient($client->id);
            $recentServices = $this->serviceRepository->getRecentByClient($client->id, 5);

            return $this->successResponse(
                new TechnicianSearchResource([
                    'client' => $client,
                    'vehicles' => $vehicles,
                    'recent_services' => $recentServices,
                    'found_by' => 'vehicle'
                ]),
                'Cliente encontrado por placa'
            );

        } else {
            // Buscar por documento
            $client = $this->clientRepository->findByDocument($searchValue);

            if (!$client) {
                return $this->errorResponse('Cliente não encontrado', 404);
            }

            $vehicles = $this->vehicleRepository->getByClient($client->id);
            $recentServices = $this->serviceRepository->getRecentByClient($client->id, 5);

            return $this->successResponse(
                new TechnicianSearchResource([
                    'client' => $client,
                    'vehicles' => $vehicles,
                    'recent_services' => $recentServices,
                    'found_by' => 'document'
                ]),
                'Cliente encontrado por documento'
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/technician/services",
     *     tags={"Técnico"},
     *     summary="Criar novo serviço para cliente",
     *     description="Cria um novo serviço para um cliente específico",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id", "vehicle_id", "description"},
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="vehicle_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Troca de óleo e filtro"),
     *             @OA\Property(property="estimated_duration", type="integer", example=60),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}, example="medium"),
     *             @OA\Property(property="notes", type="string", example="Observações adicionais")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Serviço criado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function createService(StoreServiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['technician_id'] = auth()->user()->id;
        $data['attendant_id'] = auth()->user()->id; // Técnico também é o atendente neste caso

        $service = $this->serviceService->create($data);

        return $this->successResponse(
            new ServiceResource($service),
            'Serviço criado com sucesso',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/technician/dashboard",
     *     tags={"Técnico"},
     *     summary="Dashboard do técnico",
     *     description="Retorna estatísticas e informações relevantes para o técnico",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard obtido com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Dashboard obtido com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="today_services", type="integer", example=5),
     *                 @OA\Property(property="pending_services", type="integer", example=3),
     *                 @OA\Property(property="completed_today", type="integer", example=2),
     *                 @OA\Property(property="recent_services", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function dashboard(): JsonResponse
    {
        $technicianId = auth()->user()->id;

        $stats = [
            'today_services' => $this->serviceRepository->getTodayServicesCount($technicianId),
            'pending_services' => $this->serviceRepository->getPendingServicesCount($technicianId),
            'completed_today' => $this->serviceRepository->getCompletedTodayCount($technicianId),
            'recent_services' => ServiceResource::collection(
                $this->serviceRepository->getRecentByTechnician($technicianId, 10)
            )
        ];

        return $this->successResponse($stats, 'Dashboard obtido com sucesso');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/technician/services/my",
     *     tags={"Técnico"},
     *     summary="Listar serviços do técnico",
     *     description="Retorna os serviços atribuídos ao técnico logado",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"pending", "in_progress", "completed", "cancelled"})),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Serviços listados com sucesso"
     *     )
     * )
     */
    public function myServices(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'per_page']);
        $filters['technician_id'] = auth()->user()->id;

        $services = $this->serviceRepository->searchByFilters($filters);

        return $this->successResponse(
            ServiceResource::collection($services),
            'Serviços listados com sucesso'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/technician/services/{id}/status",
     *     tags={"Técnico"},
     *     summary="Atualizar status do serviço",
     *     description="Atualiza o status de um serviço (iniciar, completar, etc.)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"in_progress", "completed", "cancelled"}, example="in_progress"),
     *             @OA\Property(property="notes", type="string", example="Observações sobre o serviço")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status atualizado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Serviço não encontrado"
     *     )
     * )
     */
    public function updateServiceStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:1000'
        ]);

        $service = $this->serviceRepository->find($id);

        if (!$service) {
            return $this->errorResponse('Serviço não encontrado', 404);
        }

        // Verificar se o técnico é responsável pelo serviço
        if ($service->technician_id !== auth()->user()->id) {
            return $this->errorResponse('Você não tem permissão para atualizar este serviço', 403);
        }

        $updatedService = $this->serviceService->updateStatus($id, $request->status, $request->notes);

        return $this->successResponse(
            new ServiceResource($updatedService),
            'Status do serviço atualizado com sucesso'
        );
    }
}
