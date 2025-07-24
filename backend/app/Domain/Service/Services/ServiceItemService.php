<?php

namespace App\Domain\Service\Services;

use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceItem;
use App\Domain\Service\Repositories\ServiceItemRepositoryInterface;
use App\Domain\Service\Repositories\ServiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ServiceItemService
{
    public function __construct(
        private ServiceItemRepositoryInterface $serviceItemRepository,
        private ServiceRepositoryInterface $serviceRepository
    ) {}

    public function getServiceItems(int $serviceId): Collection
    {
        $this->validateServiceExists($serviceId);
        return $this->serviceItemRepository->getServiceItemsWithRelations($serviceId);
    }

    public function createServiceItem(int $serviceId, array $data): ServiceItem
    {
        $this->validateServiceExists($serviceId);

        $data['service_id'] = $serviceId;
        $data = $this->calculateItemTotals($data);

        return $this->serviceItemRepository->create($data);
    }

    public function updateServiceItem(int $serviceId, int $itemId, array $data): ServiceItem
    {
        $item = $this->serviceItemRepository->findById($serviceId, $itemId);

        if (!$item) {
            throw new \InvalidArgumentException('Service item not found');
        }

        $data = $this->calculateItemTotals($data, $item);

        return $this->serviceItemRepository->update($item, $data);
    }

    public function deleteServiceItem(int $serviceId, int $itemId): bool
    {
        $item = $this->serviceItemRepository->findById($serviceId, $itemId);

        if (!$item) {
            throw new \InvalidArgumentException('Service item not found');
        }

        return $this->serviceItemRepository->delete($item);
    }

    public function bulkCreateServiceItems(int $serviceId, array $items): Collection
    {
        $this->validateServiceExists($serviceId);

        $processedItems = $this->processBulkItems($items);

        return DB::transaction(function () use ($serviceId, $processedItems) {
            $createdItems = $this->serviceItemRepository->bulkCreate($serviceId, $processedItems);

            // Recalculate service totals
            $service = $this->serviceRepository->find($serviceId);
            $service->calculateTotals();

            return $this->serviceItemRepository->getServiceItemsWithRelations($serviceId);
        });
    }

    public function bulkUpdateServiceItems(int $serviceId, array $items): Collection
    {
        $this->validateServiceExists($serviceId);

        $processedItems = $this->processBulkItems($items);

        return DB::transaction(function () use ($serviceId, $processedItems) {
            $updatedItems = $this->serviceItemRepository->bulkUpdate($serviceId, $processedItems);

            // Recalculate service totals
            $service = $this->serviceRepository->find($serviceId);
            $service->calculateTotals();

            return $this->serviceItemRepository->getServiceItemsWithRelations($serviceId);
        });
    }

    private function validateServiceExists(int $serviceId): void
    {
        $service = $this->serviceRepository->find($serviceId);

        if (!$service) {
            throw new \InvalidArgumentException('Service not found');
        }
    }

    private function calculateItemTotals(array $data, ?ServiceItem $existingItem = null): array
    {
        $quantity = $data['quantity'] ?? $existingItem?->quantity ?? 0;
        $unitPrice = $data['unit_price'] ?? $existingItem?->unit_price ?? 0;
        $discount = $data['discount'] ?? $existingItem?->discount ?? 0;

        $subtotal = $quantity * $unitPrice;
        $discountAmount = $subtotal * ($discount / 100);
        $data['total_price'] = $subtotal - $discountAmount;

        return $data;
    }

    private function processBulkItems(array $items): array
    {
        $processedItems = [];

        foreach ($items as $item) {
            $processedItems[] = $this->calculateItemTotals($item);
        }

        return $processedItems;
    }
}
