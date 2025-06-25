<?php

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function getAllActive(): Collection;

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function searchByFilters(array $filters): LengthAwarePaginator;

    public function find(int $id): ?Category;

    public function create(array $data): Category;

    public function update(int $id, array $data): ?Category;

    public function delete(int $id): bool;
}
