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
     * Display a listing of users
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
     * Store a newly created user
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
            new UserResource($user->load(['serviceCenter', 'roles'])),
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
     * Update the specified user
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'password' => 'nullable|string|min:8|confirmed',
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

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $role = $validated['role'] ?? null;
        unset($validated['role']);

        $user = $this->userRepository->update($id, $validated);

        if (!$user) {
            return $this->errorResponse('Usuário não encontrado', 404);
        }

        // Update role if provided
        if ($role) {
            $user->syncRoles([$role]);
        }

        return $this->successResponse(
            new UserResource($user->load(['serviceCenter', 'roles'])),
            'Usuário atualizado com sucesso'
        );
    }

    /**
     * Remove the specified user
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->userRepository->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Usuário não encontrado', 404);
        }

        return $this->successResponse(null, 'Usuário excluído com sucesso');
    }

    public function getActive(): JsonResponse
    {
        $users = $this->userRepository->getAllActive();

        return $this->successResponse(
            UserResource::collection($users),
            'Usuários ativos listados'
        );
    }

    public function getByServiceCenter(int $serviceCenterId): JsonResponse
    {
        $users = $this->userRepository->getByServiceCenter($serviceCenterId);

        return $this->successResponse(
            UserResource::collection($users),
            'Usuários do centro de serviço listados'
        );
    }

    public function getByRole(string $role): JsonResponse
    {
        $users = $this->userRepository->getByRole($role);

        return $this->successResponse(
            UserResource::collection($users),
            "Usuários com role '{$role}' listados"
        );
    }

    public function updateLastLogin(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->errorResponse('Usuário não encontrado', 404);
        }

        $user->update(['last_login_at' => now()]);

        return $this->successResponse(null, 'Último login atualizado');
    }

    public function changePassword(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->errorResponse('Usuário não encontrado', 404);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Senha atual incorreta', 400);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return $this->successResponse(null, 'Senha alterada com sucesso');
    }
}
