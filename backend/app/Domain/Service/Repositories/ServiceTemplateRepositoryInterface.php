<?php

namespace App\Domain\Service\Repositories;

use App\Domain\Service\Models\ServiceTemplate;
use Illuminate\Database\Eloquent\Collection;

interface ServiceTemplateRepositoryInterface
{
    public function find(int $id): ?ServiceTemplate;

    public function getActive(?string $category = null): Collection;

    public function getByCategory(string $category): Collection;

    public function create(array $data): ServiceTemplate;

    public function update(int $id, array $data): ?ServiceTemplate;

    public function delete(int $id): bool;

    public function getAllPaginated(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator;
}
