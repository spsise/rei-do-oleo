<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Service\Repositories\ServiceCenterRepositoryInterface;
use App\Http\Resources\ServiceCenterResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ServiceCenterController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private ServiceCenterRepositoryInterface $serviceCenterRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'state', 'city', 'active', 'per_page']);
        $serviceCenters = $this->serviceCenterRepository->searchByFilters($filters);

        return $this->successResponse(
            ServiceCenterResource::collection($serviceCenters),
            'Centros de serviço listados com sucesso'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:service_centers,code',
            'name' => 'required|string|max:150',
            'cnpj' => 'nullable|string|max:18|unique:service_centers,cnpj',
            'state_registration' => 'nullable|string|max:50',
            'legal_name' => 'nullable|string|max:200',
            'trade_name' => 'nullable|string|max:150',
            'address_line' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|size:2',
            'zip_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'google_maps_url' => 'nullable|url|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'technical_responsible' => 'nullable|string|max:255',
            'opening_date' => 'nullable|date',
            'operating_hours' => 'nullable|string',
            'is_main_branch' => 'boolean',
            'active' => 'boolean',
            'observations' => 'nullable|string'
        ]);

        // Auto-generate slug
        $validated['slug'] = Str::slug($validated['name']);

        $serviceCenter = $this->serviceCenterRepository->create($validated);

        return $this->successResponse(
            new ServiceCenterResource($serviceCenter),
            'Centro de serviço criado com sucesso',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $serviceCenter = $this->serviceCenterRepository->find($id);

        if (!$serviceCenter) {
            return $this->errorResponse('Centro de serviço não encontrado', 404);
        }

        return $this->successResponse(
            new ServiceCenterResource($serviceCenter),
            'Centro de serviço encontrado'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:20|unique:service_centers,code,' . $id,
            'name' => 'sometimes|string|max:150',
            'cnpj' => 'nullable|string|max:18|unique:service_centers,cnpj,' . $id,
            'state_registration' => 'nullable|string|max:50',
            'legal_name' => 'nullable|string|max:200',
            'trade_name' => 'nullable|string|max:150',
            'address_line' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|size:2',
            'zip_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'google_maps_url' => 'nullable|url|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'technical_responsible' => 'nullable|string|max:255',
            'opening_date' => 'nullable|date',
            'operating_hours' => 'nullable|string',
            'is_main_branch' => 'boolean',
            'active' => 'boolean',
            'observations' => 'nullable|string'
        ]);

        // Update slug if name changed
        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $serviceCenter = $this->serviceCenterRepository->update($id, $validated);

        if (!$serviceCenter) {
            return $this->errorResponse('Centro de serviço não encontrado', 404);
        }

        return $this->successResponse(
            new ServiceCenterResource($serviceCenter),
            'Centro de serviço atualizado com sucesso'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->serviceCenterRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Centro de serviço não encontrado', 404);
        }

        return $this->successResponse(null, 'Centro de serviço excluído com sucesso');
    }

    public function getActive(): JsonResponse
    {
        $serviceCenters = $this->serviceCenterRepository->getAllActive();

        return $this->successResponse(
            ServiceCenterResource::collection($serviceCenters),
            'Centros de serviço ativos listados'
        );
    }

    public function findByCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:20'
        ]);

        $serviceCenter = $this->serviceCenterRepository->findByCode($request->code);

        if (!$serviceCenter) {
            return $this->errorResponse('Centro de serviço não encontrado', 404);
        }

        return $this->successResponse(
            new ServiceCenterResource($serviceCenter),
            'Centro de serviço encontrado por código'
        );
    }

    public function getByRegion(Request $request): JsonResponse
    {
        $request->validate([
            'state' => 'required|string|size:2',
            'city' => 'nullable|string|max:100'
        ]);

        $serviceCenters = $this->serviceCenterRepository->getByRegion(
            $request->state,
            $request->city
        );

        return $this->successResponse(
            ServiceCenterResource::collection($serviceCenters),
            'Centros de serviço da região listados'
        );
    }

    public function findNearby(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:100'
        ]);

        $serviceCenters = $this->serviceCenterRepository->findNearby(
            $request->latitude,
            $request->longitude,
            $request->radius ?? 10
        );

        return $this->successResponse(
            ServiceCenterResource::collection($serviceCenters),
            'Centros de serviço próximos encontrados'
        );
    }

    public function getMainBranch(): JsonResponse
    {
        $serviceCenter = $this->serviceCenterRepository->getMainBranch();

        if (!$serviceCenter) {
            return $this->errorResponse('Filial principal não encontrada', 404);
        }

        return $this->successResponse(
            new ServiceCenterResource($serviceCenter),
            'Filial principal encontrada'
        );
    }
}
