<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Service\Repositories\ServiceRepositoryInterface;
use App\Domain\Service\Services\ServiceService;
use App\Http\Resources\ServiceResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private ServiceService $serviceService
    ) {}

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

        $service = $this->serviceService->create($validated);

        return $this->successResponse(
            new ServiceResource($service),
            'Serviço criado com sucesso',
            201
        );
    }

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

        $service = $this->serviceRepository->update($id, $validated);

        if (!$service) {
            return $this->errorResponse('Serviço não encontrado', 404);
        }

        return $this->successResponse(
            new ServiceResource($service),
            'Serviço atualizado com sucesso'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->serviceRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Serviço não encontrado', 404);
        }

        return $this->successResponse(null, 'Serviço excluído com sucesso');
    }

    public function getByServiceCenter(int $serviceCenterId): JsonResponse
    {
        $services = $this->serviceRepository->getByServiceCenter($serviceCenterId);

        return $this->successResponse(
            ServiceResource::collection($services),
            'Serviços do centro de serviço listados'
        );
    }

    public function getByClient(int $clientId): JsonResponse
    {
        $services = $this->serviceRepository->getByClient($clientId);

        return $this->successResponse(
            ServiceResource::collection($services),
            'Serviços do cliente listados'
        );
    }

    public function getByVehicle(int $vehicleId): JsonResponse
    {
        $services = $this->serviceRepository->getByVehicle($vehicleId);

        return $this->successResponse(
            ServiceResource::collection($services),
            'Histórico de serviços do veículo'
        );
    }

    public function getByTechnician(int $technicianId): JsonResponse
    {
        $services = $this->serviceRepository->getByTechnician($technicianId);

        return $this->successResponse(
            ServiceResource::collection($services),
            'Serviços do técnico listados'
        );
    }

    public function searchByServiceNumber(Request $request): JsonResponse
    {
        $request->validate([
            'service_number' => 'required|string|max:20'
        ]);

        $service = $this->serviceRepository->findByServiceNumber($request->service_number);

        if (!$service) {
            return $this->errorResponse('Serviço não encontrado', 404);
        }

        return $this->successResponse(
            new ServiceResource($service),
            'Serviço encontrado por número'
        );
    }

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

    public function getDashboardStats(Request $request): JsonResponse
    {
        $serviceCenterId = $request->get('service_center_id');
        $stats = $this->serviceRepository->getDashboardStats($serviceCenterId);

        return $this->successResponse($stats, 'Estatísticas do dashboard');
    }
}
