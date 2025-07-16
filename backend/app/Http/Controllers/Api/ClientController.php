<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Services\ClientService;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ClientCollection;
use App\Http\Requests\Api\Client\StoreClientRequest;
use App\Http\Requests\Api\Client\UpdateClientRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private ClientService $clientService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/clients",
     *     tags={"Clientes"},
     *     summary="Listar clientes",
     *     description="Lista todos os clientes com opções de filtro",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", example="João")),
     *     @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string", example="pessoa_fisica")),
     *     @OA\Parameter(name="active", in="query", required=false, @OA\Schema(type="boolean", example=true)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Response(response=200, description="Lista de clientes obtida com sucesso"),
     *     @OA\Response(response=401, description="Token inválido ou não fornecido")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'type', 'active', 'per_page']);
        $clients = $this->clientRepository->searchByFilters($filters);

        return $this->successResponse(
            new ClientCollection($clients),
            'Clientes listados com sucesso'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/clients",
     *     tags={"Clientes"},
     *     summary="Criar novo cliente",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"name","email","document"})),
     *     @OA\Response(response=201, description="Cliente criado com sucesso"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientRepository->create($request->validated());

        return $this->successResponse(
            new ClientResource($client),
            'Cliente criado com sucesso',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/clients/{id}",
     *     tags={"Clientes"},
     *     summary="Obter cliente específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Cliente encontrado"),
     *     @OA\Response(response=404, description="Cliente não encontrado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            return $this->errorResponse('Cliente não encontrado', 404);
        }

        return $this->successResponse(
            new ClientResource($client),
            'Cliente encontrado'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/clients/{id}",
     *     tags={"Clientes"},
     *     summary="Atualizar cliente",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=200, description="Cliente atualizado com sucesso"),
     *     @OA\Response(response=404, description="Cliente não encontrado")
     * )
     */
    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        $client = $this->clientRepository->update($id, $request->validated());

        if (!$client) {
            return $this->errorResponse('Cliente não encontrado', 404);
        }

        return $this->successResponse(
            new ClientResource($client),
            'Cliente atualizado com sucesso'
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/clients/{id}",
     *     tags={"Clientes"},
     *     summary="Excluir cliente",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Cliente excluído com sucesso"),
     *     @OA\Response(response=404, description="Cliente não encontrado")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->clientRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Cliente não encontrado', 404);
        }

        return $this->successResponse(null, 'Cliente excluído com sucesso');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/clients/search/document",
     *     tags={"Clientes"},
     *     summary="Buscar cliente por documento",
     *     description="Busca um cliente específico pelo CPF ou CNPJ",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"document"},
     *             @OA\Property(property="document", type="string", example="12345678901", description="CPF ou CNPJ do cliente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cliente encontrado por documento"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="João Silva"),
     *                 @OA\Property(property="email", type="string", example="joao@example.com"),
     *                 @OA\Property(property="document", type="string", example="12345678901"),
     *                 @OA\Property(property="phone", type="string", example="(11) 98765-4321")
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
    public function searchByDocument(Request $request): JsonResponse
    {
        $request->validate(['document' => 'required|string|max:18']);

        $client = $this->clientRepository->findByDocument($request->document);

        if (!$client) {
            return $this->errorResponse('Cliente não encontrado', 404);
        }

        return $this->successResponse(
            new ClientResource($client),
            'Cliente encontrado por documento'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/clients/search/phone",
     *     tags={"Clientes"},
     *     summary="Buscar cliente por telefone",
     *     description="Busca um cliente específico pelo número de telefone",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="(11) 98765-4321", description="Número de telefone do cliente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cliente encontrado por telefone"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="João Silva"),
     *                 @OA\Property(property="email", type="string", example="joao@example.com"),
     *                 @OA\Property(property="document", type="string", example="12345678901"),
     *                 @OA\Property(property="phone", type="string", example="(11) 98765-4321")
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
    public function searchByPhone(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string|max:20']);

        $client = $this->clientRepository->findByPhone($request->phone);

        if (!$client) {
            return $this->errorResponse('Cliente não encontrado', 404);
        }

        return $this->successResponse(
            new ClientResource($client),
            'Cliente encontrado por telefone'
        );
    }
}
