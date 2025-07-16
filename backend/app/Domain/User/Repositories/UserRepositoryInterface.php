<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function getAllActive(): Collection;

    public function getByServiceCenter(int $serviceCenterId): Collection;

    public function getByRole(string $role): Collection;

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function searchByFilters(array $filters): LengthAwarePaginator;

    public function find(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(array $data): User;

    public function update(int $id, array $data): ?User;

    public function delete(int $id): bool;
}
