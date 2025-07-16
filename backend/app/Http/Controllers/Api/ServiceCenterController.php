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

    /**
     * @OA\Get(
     *     path="/api/v1/service-centers",
     *     tags={"Centros de Serviço"},
     *     summary="Listar centros de serviço",
     *     description="Lista todos os centros de serviço com opções de filtro",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Termo de busca por nome ou código",
     *         required=false,
     *         @OA\Schema(type="string", example="Centro")
     *     ),
     *     @OA\Parameter(
     *         name="state",
     *         in="query",
     *         description="Filtrar por estado (UF)",
     *         required=false,
     *         @OA\Schema(type="string", example="SP")
     *     ),
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Filtrar por cidade",
     *         required=false,
     *         @OA\Schema(type="string", example="São Paulo")
     *     ),
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Filtrar por status ativo",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Número de itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Centros de serviço listados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Centros de serviço listados com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="CS001"),
     *                     @OA\Property(property="name", type="string", example="Centro de Serviço Principal"),
     *                     @OA\Property(property="slug", type="string", example="centro-de-servico-principal"),
     *                     @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
     *                     @OA\Property(property="city", type="string", example="São Paulo"),
     *                     @OA\Property(property="state", type="string", example="SP"),
     *                     @OA\Property(property="phone", type="string", example="(11) 3333-4444"),
     *                     @OA\Property(property="active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou não fornecido"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'state', 'city', 'active', 'per_page']);
        $serviceCenters = $this->serviceCenterRepository->searchByFilters($filters);

        return $this->successResponse(
            ServiceCenterResource::collection($serviceCenters),
            'Centros de serviço listados com sucesso'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service-centers",
     *     tags={"Centros de Serviço"},
     *     summary="Criar novo centro de serviço",
     *     description="Cria um novo centro de serviço no sistema",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code","name"},
     *             @OA\Property(property="code", type="string", example="CS002", description="Código único do centro"),
     *             @OA\Property(property="name", type="string", example="Centro de Serviço Zona Sul", description="Nome do centro"),
     *             @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90", description="CNPJ da empresa"),
     *             @OA\Property(property="city", type="string", example="São Paulo", description="Cidade"),
     *             @OA\Property(property="state", type="string", example="SP", description="Estado (UF)"),
     *             @OA\Property(property="phone", type="string", example="(11) 3333-4444", description="Telefone"),
     *             @OA\Property(property="email", type="string", format="email", example="centro@reidooleo.com", description="Email"),
     *             @OA\Property(property="active", type="boolean", example=true, description="Status ativo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Centro de serviço criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Centro de serviço criado com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="code", type="string", example="CS002"),
     *                 @OA\Property(property="name", type="string", example="Centro de Serviço Zona Sul"),
     *                 @OA\Property(property="slug", type="string", example="centro-de-servico-zona-sul"),
     *                 @OA\Property(property="active", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/service-centers/{id}",
     *     tags={"Centros de Serviço"},
     *     summary="Obter centro de serviço específico",
     *     description="Retorna os dados de um centro de serviço específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do centro de serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Centro de serviço encontrado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Centro de serviço não encontrado"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/v1/service-centers/{id}",
     *     tags={"Centros de Serviço"},
     *     summary="Atualizar centro de serviço",
     *     description="Atualiza os dados de um centro de serviço existente",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do centro de serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Centro de Serviço Principal Atualizado"),
     *             @OA\Property(property="city", type="string", example="São Paulo"),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Centro de serviço atualizado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Centro de serviço não encontrado"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/v1/service-centers/{id}",
     *     tags={"Centros de Serviço"},
     *     summary="Excluir centro de serviço",
     *     description="Remove um centro de serviço do sistema",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do centro de serviço",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Centro de serviço excluído com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Centro de serviço não encontrado"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->serviceCenterRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Centro de serviço não encontrado', 404);
        }

        return $this->successResponse(null, 'Centro de serviço excluído com sucesso');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/service-centers/active",
     *     tags={"Centros de Serviço"},
     *     summary="Listar centros de serviço ativos",
     *     description="Retorna apenas os centros de serviço que estão ativos no sistema",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Centros de serviço ativos listados com sucesso"
     *     )
     * )
     */
    public function getActive(): JsonResponse
    {
        $serviceCenters = $this->serviceCenterRepository->getAllActive();

        return $this->successResponse(
            ServiceCenterResource::collection($serviceCenters),
            'Centros de serviço ativos listados'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service-centers/search/code",
     *     tags={"Centros de Serviço"},
     *     summary="Buscar centro de serviço por código",
     *     description="Busca um centro de serviço específico pelo código",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="CS001", description="Código do centro de serviço")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Centro de serviço encontrado por código"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Centro de serviço não encontrado"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/service-centers/region",
     *     tags={"Centros de Serviço"},
     *     summary="Buscar centros por região",
     *     description="Busca centros de serviço por estado e opcionalmente por cidade",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="state",
     *         in="query",
     *         required=true,
     *         description="Estado (UF)",
     *         @OA\Schema(type="string", example="SP")
     *     ),
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         required=false,
     *         description="Cidade",
     *         @OA\Schema(type="string", example="São Paulo")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Centros de serviço da região listados com sucesso"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/v1/service-centers/nearby",
     *     tags={"Centros de Serviço"},
     *     summary="Buscar centros próximos",
     *     description="Busca centros de serviço próximos a uma localização específica",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"latitude","longitude"},
     *             @OA\Property(property="latitude", type="number", format="float", example=-23.5505, description="Latitude da localização"),
     *             @OA\Property(property="longitude", type="number", format="float", example=-46.6333, description="Longitude da localização"),
     *             @OA\Property(property="radius", type="number", format="float", example=10, description="Raio de busca em km (padrão: 10km)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Centros de serviço próximos encontrados com sucesso"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/service-centers/main-branch",
     *     tags={"Centros de Serviço"},
     *     summary="Obter filial principal",
     *     description="Retorna os dados da filial principal/matriz da empresa",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Filial principal encontrada com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Filial principal não encontrada"
     *     )
     * )
     */
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
