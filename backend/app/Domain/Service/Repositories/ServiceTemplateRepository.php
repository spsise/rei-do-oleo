<?php

namespace App\Domain\Service\Repositories;

use App\Domain\Service\Models\ServiceTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ServiceTemplateRepository implements ServiceTemplateRepositoryInterface
{
    public function find(int $id): ?ServiceTemplate
    {
        return ServiceTemplate::find($id);
    }

    public function getActive(?string $category = null): Collection
    {
        return ServiceTemplate::getActiveCached($category);
    }

    public function getByCategory(string $category): Collection
    {
        return ServiceTemplate::getByCategory($category);
    }

    public function create(array $data): ServiceTemplate
    {
        $template = ServiceTemplate::create($data);

        // Clear cache
        $this->clearCache();

        return $template;
    }

    public function update(int $id, array $data): ?ServiceTemplate
    {
        $template = ServiceTemplate::find($id);

        if (!$template) {
            return null;
        }

        $template->update($data);

        // Clear cache
        $this->clearCache();

        return $template;
    }

    public function delete(int $id): bool
    {
        $template = ServiceTemplate::find($id);

        if (!$template) {
            return false;
        }

        $deleted = $template->delete();

        if ($deleted) {
            // Clear cache
            $this->clearCache();
        }

        return $deleted;
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return ServiceTemplate::ordered()->paginate($perPage);
    }

    /**
     * Clear all template caches
     */
    private function clearCache(): void
    {
        Cache::forget('service_templates');
        Cache::forget('service_templates_maintenance');
        Cache::forget('service_templates_repair');
        Cache::forget('service_templates_inspection');
        Cache::forget('service_templates_emergency');
        Cache::forget('service_templates_preventive');
    }
}
