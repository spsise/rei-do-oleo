<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Services\ClientService;
use App\Http\Resources\ClientResource;
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

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'type', 'active', 'per_page']);
        $clients = $this->clientRepository->searchByFilters($filters);

        return $this->successResponse(
            ClientResource::collection($clients),
            'Clientes listados com sucesso'
        );
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientRepository->create($request->validated());

        return $this->successResponse(
            new ClientResource($client),
            'Cliente criado com sucesso',
            201
        );
    }

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

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->clientRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Cliente não encontrado', 404);
        }

        return $this->successResponse(null, 'Cliente excluído com sucesso');
    }

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
