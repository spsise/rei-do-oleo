<?php

namespace App\Domain\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Product\Models\Product;

class ServiceItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'service_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'total_price',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the service that owns the service item.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the product of the service item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to filter by service.
     */
    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Scope to filter by product.
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Get formatted unit price.
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->unit_price, 2, ',', '.');
    }

    /**
     * Get formatted total price.
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->total_price, 2, ',', '.');
    }

    /**
     * Calculate total price from quantity and unit price.
     */
    public function calculateTotalPrice(): void
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discountAmount = $subtotal * ($this->discount / 100);
        $this->total_price = $subtotal - $discountAmount;
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Calculate total price automatically
        static::creating(function ($serviceItem) {
            $serviceItem->calculateTotalPrice();
        });

        static::updating(function ($serviceItem) {
            if ($serviceItem->isDirty(['quantity', 'unit_price'])) {
                $serviceItem->calculateTotalPrice();
            }
        });

        // Update service totals when service item changes
        static::saved(function ($serviceItem) {
            $serviceItem->service->calculateTotals();
        });

        static::deleted(function ($serviceItem) {
            $serviceItem->service->calculateTotals();
        });
    }
}
