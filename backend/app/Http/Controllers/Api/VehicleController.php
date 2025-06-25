<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Client\Repositories\VehicleRepositoryInterface;
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
        private VehicleRepositoryInterface $vehicleRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'client_id', 'brand', 'active', 'per_page']);
        $vehicles = $this->vehicleRepository->searchByFilters($filters);

        return $this->successResponse(
            VehicleResource::collection($vehicles),
            'Veículos listados com sucesso'
        );
    }

    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = $this->vehicleRepository->create($request->validated());

        return $this->successResponse(
            new VehicleResource($vehicle),
            'Veículo criado com sucesso',
            201
        );
    }

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

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->vehicleRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Veículo não encontrado', 404);
        }

        return $this->successResponse(null, 'Veículo excluído com sucesso');
    }

    public function searchByLicensePlate(Request $request): JsonResponse
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

    public function updateMileage(Request $request, int $id): JsonResponse
    {
        $request->validate(['mileage' => 'required|integer|min:0']);

        $result = $this->vehicleRepository->updateMileage($id, $request->mileage);

        if (!$result) {
            return $this->errorResponse('Veículo não encontrado ou quilometragem inválida', 400);
        }

        return $this->successResponse(null, 'Quilometragem atualizada com sucesso');
    }

    public function getByClient(int $clientId): JsonResponse
    {
        $vehicles = $this->vehicleRepository->getByClientId($clientId);

        return $this->successResponse(
            VehicleResource::collection($vehicles),
            'Veículos do cliente listados'
        );
    }
}
