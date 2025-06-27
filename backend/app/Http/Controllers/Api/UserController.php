<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     tags={"Usuários"},
     *     summary="Listar usuários",
     *     description="Lista todos os usuários do sistema com opções de filtro",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Termo de busca por nome ou email",
     *         required=false,
     *         @OA\Schema(type="string", example="joão")
     *     ),
     *     @OA\Parameter(
     *         name="service_center_id",
     *         in="query",
     *         description="Filtrar por centro de serviço",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filtrar por papel/função",
     *         required=false,
     *         @OA\Schema(type="string", example="technician")
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
     *         description="Usuários listados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Usuários listados com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="João Silva"),
     *                     @OA\Property(property="email", type="string", example="joao@reidooleo.com"),
     *                     @OA\Property(property="role", type="string", example="technician"),
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
        $filters = $request->only(['search', 'service_center_id', 'role', 'active', 'per_page']);
        $users = $this->userRepository->searchByFilters($filters);

        return $this->successResponse(
            UserResource::collection($users),
            'Usuários listados com sucesso'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     tags={"Usuários"},
     *     summary="Criar novo usuário",
     *     description="Cria um novo usuário no sistema",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "service_center_id", "role"},
     *             @OA\Property(property="name", type="string", example="Maria Santos"),
     *             @OA\Property(property="email", type="string", example="maria@reidooleo.com"),
     *             @OA\Property(property="password", type="string", example="senha123"),
     *             @OA\Property(property="password_confirmation", type="string", example="senha123"),
     *             @OA\Property(property="service_center_id", type="integer", example=1),
     *             @OA\Property(property="role", type="string", enum={"admin", "manager", "attendant", "technician"}, example="technician"),
     *             @OA\Property(property="phone", type="string", example="(11) 98765-4321"),
     *             @OA\Property(property="whatsapp", type="string", example="(11) 98765-4321"),
     *             @OA\Property(property="document", type="string", example="123.456.789-00"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="hire_date", type="string", format="date", example="2023-01-15"),
     *             @OA\Property(property="salary", type="number", format="float", example=3500.00),
     *             @OA\Property(property="commission_rate", type="number", format="float", example=5.5),
     *             @OA\Property(property="specialties", type="string", example="Troca de óleo, Mecânica geral"),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Usuário criado com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="Maria Santos"),
     *                 @OA\Property(property="email", type="string", example="maria@reidooleo.com"),
     *                 @OA\Property(property="role", type="string", example="technician"),
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'service_center_id' => 'required|exists:service_centers,id',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'document' => 'nullable|string|max:18',
            'birth_date' => 'nullable|date',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'specialties' => 'nullable|string',
            'active' => 'boolean',
            'role' => ['required', Rule::in(['admin', 'manager', 'attendant', 'technician'])]
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $role = $validated['role'];
        unset($validated['role']);

        $user = $this->userRepository->create($validated);
        $user->assignRole($role);

        return $this->successResponse(
            new UserResource($user),
            'Usuário criado com sucesso',
            201
        );
    }

    /**
     * Display the specified user
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->errorResponse('Usuário não encontrado', 404);
        }

        return $this->successResponse(
            new UserResource($user),
            'Usuário encontrado'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     tags={"Usuários"},
     *     summary="Atualizar usuário",
     *     description="Atualiza os dados de um usuário existente",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Maria Silva Santos"),
     *             @OA\Property(property="email", type="string", example="maria.silva@reidooleo.com"),
     *             @OA\Property(property="service_center_id", type="integer", example=2),
     *             @OA\Property(property="phone", type="string", example="(11) 99999-8888"),
     *             @OA\Property(property="whatsapp", type="string", example="(11) 99999-8888"),
     *             @OA\Property(property="document", type="string", example="987.654.321-00"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="hire_date", type="string", format="date", example="2023-02-15"),
     *             @OA\Property(property="salary", type="number", format="float", example=4000.00),
     *             @OA\Property(property="commission_rate", type="number", format="float", example=7.5),
     *             @OA\Property(property="specialties", type="string", example="Diagnóstico eletrônico, Troca de óleo"),
     *             @OA\Property(property="role", type="string", enum={"admin", "manager", "attendant", "technician"}, example="manager"),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Usuário atualizado com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Maria Silva Santos"),
     *                 @OA\Property(property="email", type="string", example="maria.silva@reidooleo.com"),
     *                 @OA\Property(property="role", type="string", example="manager"),
     *                 @OA\Property(property="active", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado"
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
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
            'service_center_id' => 'sometimes|exists:service_centers,id',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'document' => 'nullable|string|max:18',
            'birth_date' => 'nullable|date',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'specialties' => 'nullable|string',
            'active' => 'boolean',
            'role' => ['sometimes', Rule::in(['admin', 'manager', 'attendant', 'technician'])]
        ]);

        $user = $this->userRepository->update($id, $validated);

        if (!$user) {
            return $this->errorResponse('Usuário não encontrado', 404);
        }

        return $this->successResponse(
            new UserResource($user),
            'Usuário atualizado com sucesso'
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     tags={"Usuários"},
     *     summary="Excluir usuário",
     *     description="Remove um usuário do sistema",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário excluído com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->userRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Usuário não encontrado', 404);
        }

        return $this->successResponse(null, 'Usuário excluído com sucesso');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/active",
     *     tags={"Usuários"},
     *     summary="Listar usuários ativos",
     *     description="Retorna todos os usuários ativos no sistema",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Usuários ativos listados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Usuários ativos listados"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="João Silva"),
     *                     @OA\Property(property="email", type="string", example="joao@reidooleo.com"),
     *                     @OA\Property(property="role", type="string", example="technician")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getActive(): JsonResponse
    {
        $users = $this->userRepository->getAllActive();

        return $this->successResponse(
            UserResource::collection($users),
            'Usuários ativos listados'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/service-center/{serviceCenterId}",
     *     tags={"Usuários"},
     *     summary="Listar usuários por centro de serviço",
     *     description="Retorna todos os usuários de um centro de serviço específico",
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
     *         description="Usuários do centro de serviço listados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Usuários do centro de serviço listados"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="João Silva"),
     *                     @OA\Property(property="email", type="string", example="joao@reidooleo.com"),
     *                     @OA\Property(property="role", type="string", example="technician"),
     *                     @OA\Property(property="service_center_id", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getByServiceCenter(int $serviceCenterId): JsonResponse
    {
        $users = $this->userRepository->getByServiceCenter($serviceCenterId);

        return $this->successResponse(
            UserResource::collection($users),
            'Usuários do centro de serviço listados'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/role/{role}",
     *     tags={"Usuários"},
     *     summary="Listar usuários por função",
     *     description="Retorna todos os usuários com uma função específica",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *         description="Função do usuário",
     *         @OA\Schema(type="string", enum={"admin", "manager", "attendant", "technician"}, example="technician")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuários por função listados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Usuários por função listados"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="João Silva"),
     *                     @OA\Property(property="email", type="string", example="joao@reidooleo.com"),
     *                     @OA\Property(property="role", type="string", example="technician")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getByRole(string $role): JsonResponse
    {
        $users = $this->userRepository->getByRole($role);

        return $this->successResponse(
            UserResource::collection($users),
            'Usuários por função listados'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}/last-login",
     *     tags={"Usuários"},
     *     summary="Atualizar último login",
     *     description="Atualiza o timestamp do último login de um usuário",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Último login atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Último login atualizado"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="last_login", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado"
     *     )
     * )
     */
    public function updateLastLogin(int $id): JsonResponse
    {
        $updated = $this->userRepository->updateLastLogin($id);

        if (!$updated) {
            return $this->errorResponse('Usuário não encontrado', 404);
        }

        return $this->successResponse(null, 'Último login atualizado');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}/change-password",
     *     tags={"Usuários"},
     *     summary="Alterar senha do usuário",
     *     description="Permite que um usuário altere sua própria senha",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "password"},
     *             @OA\Property(property="current_password", type="string", example="senhaantiga123"),
     *             @OA\Property(property="password", type="string", example="novasenha456"),
     *             @OA\Property(property="password_confirmation", type="string", example="novasenha456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Senha alterada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Senha alterada com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Senha atual incorreta"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function changePassword(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $result = $this->userRepository->changePassword(
            $id,
            $request->current_password,
            $request->password
        );

        if (!$result) {
            return $this->errorResponse('Senha atual incorreta', 400);
        }

        return $this->successResponse(null, 'Senha alterada com sucesso');
    }
}
