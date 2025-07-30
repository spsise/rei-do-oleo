<?php

namespace App\Domain\Service\Services;

use App\Domain\Service\Repositories\ServiceItemRepositoryInterface;
use App\Domain\Service\Models\ServiceItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceItemsOperationService
{
    public function __construct(
        private ServiceItemRepositoryInterface $serviceItemRepository
    ) {}

    /**
     * Execute items operation based on operation type
     *
     * @param int $serviceId
     * @param array $itemsData
     * @return Collection
     * @throws \InvalidArgumentException
     */
    public function executeOperation(int $serviceId, array $itemsData): Collection
    {
        $operation = $itemsData['operation'] ?? 'update';
        $items = $itemsData['data'] ?? [];
        $removeUnsent = $itemsData['remove_unsent'] ?? false;

        Log::info('Executing service items operation', [
            'service_id' => $serviceId,
            'operation' => $operation,
            'items_count' => count($items),
            'remove_unsent' => $removeUnsent
        ]);

        return match ($operation) {
            'replace' => $this->replaceItems($serviceId, $items),
            'update' => $this->updateItems($serviceId, $items, $removeUnsent),
            'merge' => $this->mergeItems($serviceId, $items),
            default => throw new \InvalidArgumentException("Invalid operation: {$operation}")
        };
    }

    /**
     * Replace all items with new ones
     *
     * @param int $serviceId
     * @param array $items
     * @return Collection
     */
    private function replaceItems(int $serviceId, array $items): Collection
    {
        return DB::transaction(function () use ($serviceId, $items) {
            // Delete all existing items
            $this->serviceItemRepository->deleteByService($serviceId);

            // Create new items if provided
            if (!empty($items)) {
                return $this->serviceItemRepository->bulkCreate($serviceId, $items);
            }

            return new Collection();
        });
    }

    /**
     * Update existing items and add new ones
     *
     * @param int $serviceId
     * @param array $items
     * @param bool $removeUnsent
     * @return Collection
     * @throws \InvalidArgumentException
     */
    private function updateItems(int $serviceId, array $items, bool $removeUnsent = false): Collection
    {
        $existingItemIds = [];
        $updatedItems = [];
        $newItems = [];

        // Get all existing items for this service to check for duplicates
        $allExistingItems = $this->serviceItemRepository->getServiceItemsWithRelations($serviceId);
        $existingItemsByProductId = $allExistingItems->keyBy('product_id');

        foreach ($items as $itemData) {
            if (isset($itemData['id'])) {
                $existingItemIds[] = $itemData['id'];
                // Update existing item by ID
                $item = $this->serviceItemRepository->findById($serviceId, $itemData['id']);

                if (!$item) {
                    throw new \InvalidArgumentException("Service item with ID {$itemData['id']} not found");
                }

                $updatedItems[] = $this->serviceItemRepository->update($item, $itemData);
            } else {
                // Check if item with same product_id already exists
                $productId = $itemData['product_id'];
                if ($existingItemsByProductId->has($productId)) {
                    // Update existing item by product_id
                    $existingItem = $existingItemsByProductId->get($productId);
                    $existingItemIds[] = $existingItem->id;
                    $updatedItems[] = $this->serviceItemRepository->update($existingItem, $itemData);
                } else {
                    // Add new item
                    $newItems[] = $itemData;
                }
            }
        }

        // Remove items not sent if flag is true
        if ($removeUnsent) {
            foreach ($allExistingItems as $existingItem) {
                if (!in_array($existingItem->id, $existingItemIds)) {
                    $this->serviceItemRepository->delete($existingItem);
                    Log::info('Removed unsent service item', [
                        'service_id' => $serviceId,
                        'item_id' => $existingItem->id
                    ]);
                }
            }
        }

        // Create new items if any
        if (!empty($newItems)) {
            $createdItems = $this->serviceItemRepository->bulkCreate($serviceId, $newItems);
            $updatedItems = array_merge($updatedItems, $createdItems->toArray());
        }

        return new Collection($updatedItems);
    }

    /**
     * Merge items (add new ones, keep existing ones)
     *
     * @param int $serviceId
     * @param array $items
     * @return Collection
     */
    private function mergeItems(int $serviceId, array $items): Collection
    {
        if (empty($items)) {
            return $this->serviceItemRepository->getServiceItemsWithRelations($serviceId);
        }

        // Create new items
        $newItems = $this->serviceItemRepository->bulkCreate($serviceId, $items);

        // Return all items (existing + new)
        return $this->serviceItemRepository->getServiceItemsWithRelations($serviceId);
    }

    /**
     * Validate items data structure
     *
     * @param array $itemsData
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validateItemsData(array $itemsData): bool
    {
        if (!isset($itemsData['operation'])) {
            throw new \InvalidArgumentException('Operation is required');
        }

        if (!in_array($itemsData['operation'], ['replace', 'update', 'merge'])) {
            throw new \InvalidArgumentException('Invalid operation type');
        }

        if (!isset($itemsData['data']) || !is_array($itemsData['data'])) {
            throw new \InvalidArgumentException('Items data must be an array');
        }

        return true;
    }

    /**
     * Calculate totals for items
     *
     * @param array $items
     * @return array
     */
    public function calculateItemsTotals(array $items): array
    {
        return array_map(function ($item) {
            $quantity = $item['quantity'] ?? 0;
            $unitPrice = $item['unit_price'] ?? 0;
            $discount = $item['discount'] ?? 0;

            $subtotal = $quantity * $unitPrice;
            $discountAmount = $subtotal * ($discount / 100);
            $item['total_price'] = $subtotal - $discountAmount;

            return $item;
        }, $items);
    }
}
