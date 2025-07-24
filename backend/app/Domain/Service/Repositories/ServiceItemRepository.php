<?php

namespace App\Domain\Service\Repositories;

use App\Domain\Service\Models\ServiceItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ServiceItemRepository implements ServiceItemRepositoryInterface
{
    public function findByService(int $serviceId): Collection
    {
        return ServiceItem::where('service_id', $serviceId)->get();
    }

    public function findById(int $serviceId, int $itemId): ?ServiceItem
    {
        return ServiceItem::where('service_id', $serviceId)
                         ->where('id', $itemId)
                         ->first();
    }

    public function create(array $data): ServiceItem
    {
        try {
            $item = ServiceItem::create($data);
            return $item;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update(ServiceItem $item, array $data): ServiceItem
    {
        $item->update($data);
        return $item->fresh();
    }

    public function delete(ServiceItem $item): bool
    {
        return $item->delete();
    }

    public function deleteByService(int $serviceId): int
    {
        return ServiceItem::where('service_id', $serviceId)->delete();
    }

    public function bulkCreate(int $serviceId, array $items): Collection
    {
        $createdItems = [];

        foreach ($items as $index => $itemData) {
            $itemData['service_id'] = $serviceId;
            $createdItems[] = $this->create($itemData);
        }

        return new Collection($createdItems);
    }

    public function bulkUpdate(int $serviceId, array $items): Collection
    {
        return DB::transaction(function () use ($serviceId, $items) {
            // Delete existing items
            $deletedCount = $this->deleteByService($serviceId);

            // Create new items
            $newItems = $this->bulkCreate($serviceId, $items);

            return $newItems;
        });
    }

    public function getServiceItemsWithRelations(int $serviceId): Collection
    {
        return ServiceItem::where('service_id', $serviceId)
                         ->with(['product', 'product.category'])
                         ->get();
    }
}
