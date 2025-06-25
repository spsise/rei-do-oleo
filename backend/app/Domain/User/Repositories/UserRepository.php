<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class UserRepository implements UserRepositoryInterface
{
    public function getAllActive(): Collection
    {
        return User::active()->with(['serviceCenter', 'roles'])->get();
    }

    public function getByServiceCenter(int $serviceCenterId): Collection
    {
        return User::byServiceCenter($serviceCenterId)
                   ->active()
                   ->with(['roles'])
                   ->get();
    }

    public function getByRole(string $role): Collection
    {
        return User::role($role)->active()->with(['serviceCenter'])->get();
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
                AllowedFilter::exact('service_center_id'),
                AllowedFilter::exact('active'),
            ])
            ->allowedSorts(['name', 'email', 'created_at', 'last_login_at'])
            ->with(['serviceCenter', 'roles'])
            ->paginate($perPage);
    }

    public function searchByFilters(array $filters): LengthAwarePaginator
    {
        $query = User::with(['serviceCenter', 'roles']);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($filters['service_center_id'])) {
            $query->byServiceCenter($filters['service_center_id']);
        }

        if (isset($filters['role'])) {
            $query->role($filters['role']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function find(int $id): ?User
    {
        return User::with(['serviceCenter', 'roles', 'services'])->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->with(['serviceCenter', 'roles'])->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(int $id, array $data): ?User
    {
        $user = User::find($id);
        if (!$user) return null;

        $user->update($data);
        return $user->fresh(['serviceCenter', 'roles']);
    }

    public function delete(int $id): bool
    {
        $user = User::find($id);
        return $user ? $user->delete() : false;
    }
}
