<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Service\Services\AttendantServiceService;
use App\Http\Requests\Api\Service\CreateQuickServiceRequest;
use App\Http\Requests\Api\Service\CreateCompleteServiceRequest;
use App\Http\Requests\Api\Service\ValidateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServiceTemplateResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendantServiceController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AttendantServiceService $attendantServiceService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/attendant/services/quick",
     *     tags={"Atendente - Serviços"},
     *     summary="Criar serviço rápido",
     *     description="Criação simplificada de serviço para atendente com campos essenciais",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id","vehicle_id","description"},
     *             @OA\Property(property="client_id", type="integer", example=1, description="ID do cliente"),
     *             @OA\Property(property="vehicle_id", type="integer", example=1, description="ID do veículo"),
     *             @OA\Property(property="description", type="string", example="Troca de óleo e filtro", description="Descrição do serviço"),
     *             @OA\Property(property="estimated_duration", type="integer", example=60, description="Duração estimada em minutos"),
     *             @OA\Property(property="priority", type="string", enum={"low","medium","high"}, example="medium", description="Prioridade do serviço"),
     *             @OA\Property(property="notes", type="string", example="Observações adicionais", description="Observações opcionais"),
     *             @OA\Property(property="template_id", type="integer", example=1, description="ID do template (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Serviço criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Serviço criado com sucesso"),
     *             @OA\Property(
                 property="data",
                 type="object",
                 @OA\Property(property="id", type="integer", example=1),
                 @OA\Property(property="service_number", type="string", example="SER001"),
                 @OA\Property(property="description", type="string", example="Troca de óleo e filtro"),
                 @OA\Property(property="status", type="object"),
                 @OA\Property(property="client", type="object"),
                 @OA\Property(property="vehicle", type="object"),
                 @OA\Property(property="created_at", type="string", format="date-time")
             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function createQuickService(CreateQuickServiceRequest $request): JsonResponse
    {
        $service = $this->attendantServiceService->createQuickService($request->validated());

        return $this->successResponse(
            new ServiceResource($service),
            'Serviço criado com sucesso',
            201
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/attendant/services/complete",
     *     tags={"Atendente - Serviços"},
     *     summary="Criar serviço completo",
     *     description="Criação completa de serviço com todos os campos disponíveis",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id","vehicle_id","description"},
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="vehicle_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Troca de óleo e filtro"),
     *             @OA\Property(property="estimated_duration", type="integer", example=60),
     *             @OA\Property(property="priority", type="string", enum={"low","medium","high"}, example="medium"),
     *             @OA\Property(property="scheduled_at", type="string", format="date-time", example="2024-01-15T10:00:00"),
     *             @OA\Property(property="technician_id", type="integer", example=2),
     *             @OA\Property(property="notes", type="string", example="Observações"),
     *             @OA\Property(property="observations", type="string", example="Observações detalhadas"),
     *             @OA\Property(
                 property="service_items",
                 type="array",
                 @OA\Items(
                     @OA\Property(property="product_id", type="integer", example=1),
                     @OA\Property(property="quantity", type="integer", example=2),
                     @OA\Property(property="unit_price", type="number", format="float", example=25.00),
                     @OA\Property(property="discount", type="number", format="float", example=5.0),
                     @OA\Property(property="notes", type="string", example="Observações do item")
                 )
             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Serviço criado com sucesso"
     *     )
     * )
     */
    public function createCompleteService(CreateCompleteServiceRequest $request): JsonResponse
    {
        $service = $this->attendantServiceService->createCompleteService($request->validated());

        return $this->successResponse(
            new ServiceResource($service),
            'Serviço criado com sucesso',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/attendant/services/templates",
     *     tags={"Atendente - Serviços"},
     *     summary="Listar templates de serviços",
     *     description="Retorna templates de serviços comuns para criação rápida",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filtrar por categoria",
     *         required=false,
     *         @OA\Schema(type="string", example="maintenance")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Templates listados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Templates listados com sucesso"),
     *             @OA\Property(
                 property="data",
                 type="array",
                 @OA\Items(
                     @OA\Property(property="id", type="integer", example=1),
                     @OA\Property(property="name", type="string", example="Troca de Óleo"),
                     @OA\Property(property="description", type="string", example="Troca de óleo e filtro"),
                     @OA\Property(property="category", type="string", example="maintenance"),
                     @OA\Property(property="estimated_duration", type="integer", example=60),
                     @OA\Property(property="items", type="array", @OA\Items(type="object"))
                 )
             )
     *         )
     *     )
     * )
     */
    public function getTemplates(Request $request): JsonResponse
    {
        $category = $request->get('category');
        $templates = $this->attendantServiceService->getTemplates($category);

        return $this->successResponse(
            ServiceTemplateResource::collection($templates),
            'Templates listados com sucesso'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/attendant/services/validate",
     *     tags={"Atendente - Serviços"},
     *     summary="Validar dados do serviço",
     *     description="Validação em tempo real dos dados do serviço antes da criação",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="vehicle_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Troca de óleo"),
     *             @OA\Property(property="scheduled_at", type="string", format="date-time"),
     *             @OA\Property(property="technician_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dados validados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Dados válidos"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="is_valid", type="boolean", example=true),
     *                 @OA\Property(property="warnings", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="suggestions", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     )
     * )
     */
    public function validateService(ValidateServiceRequest $request): JsonResponse
    {
        $validation = $this->attendantServiceService->validateService($request->validated());

        return $this->successResponse(
            $validation,
            'Dados validados com sucesso'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/attendant/services/suggestions",
     *     tags={"Atendente - Serviços"},
     *     summary="Obter sugestões de serviço",
     *     description="Retorna sugestões baseadas no histórico do cliente/veículo",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="client_id",
     *         in="query",
     *         description="ID do cliente",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_id",
     *         in="query",
     *         description="ID do veículo",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sugestões obtidas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Sugestões obtidas"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="recent_services", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="recommended_services", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="maintenance_due", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     )
     * )
     */
    public function getSuggestions(Request $request): JsonResponse
    {
        $clientId = $request->get('client_id');
        $vehicleId = $request->get('vehicle_id');

        $suggestions = $this->attendantServiceService->getSuggestions($clientId, $vehicleId);

        return $this->successResponse(
            $suggestions,
            'Sugestões obtidas com sucesso'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/attendant/services/quick-stats",
     *     tags={"Atendente - Serviços"},
     *     summary="Estatísticas rápidas do atendente",
     *     description="Estatísticas relevantes para o atendente",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estatísticas obtidas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Estatísticas obtidas"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="services_created_today", type="integer", example=15),
     *                 @OA\Property(property="pending_services", type="integer", example=8),
     *                 @OA\Property(property="completed_today", type="integer", example=12),
     *                 @OA\Property(property="average_creation_time", type="number", format="float", example=2.5)
     *             )
     *         )
     *     )
     * )
     */
    public function getQuickStats(): JsonResponse
    {
        $stats = $this->attendantServiceService->getQuickStats();

        return $this->successResponse(
            $stats,
            'Estatísticas obtidas com sucesso'
        );
    }
}
