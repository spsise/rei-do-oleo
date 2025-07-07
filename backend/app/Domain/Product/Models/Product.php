<?php

namespace App\Domain\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Domain\Service\Models\ServiceItem;
use Database\Factories\ProductFactory;

class Product extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ProductFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'sku',
        'price',
        'stock_quantity',
        'min_stock',
        'unit',
        'active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Get the category that the product belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the service items for the product.
     */
    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class);
    }

    /**
     * Scope to get only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to search by name or description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to get low stock products.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock')
                    ->whereNotNull('min_stock');
    }

    /**
     * Get all active products with cache.
     */
    public static function getActiveCached()
    {
        return Cache::remember('active_products', 21600, function () {
            return self::with('category')
                       ->active()
                       ->orderBy('name')
                       ->get();
        });
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Check if product is low stock.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->min_stock && $this->stock_quantity <= $this->min_stock;
    }

    /**
     * Check if product is out of stock.
     */
    public function getIsOutOfStockAttribute(): bool
    {
        return $this->stock_quantity === 0;
    }

    /**
     * Get stock status.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->is_out_of_stock) {
            return 'out_of_stock';
        }

        if ($this->is_low_stock) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Update stock quantity.
     */
    public function updateStock(int $quantity, string $operation = 'subtract'): void
    {
        $newQuantity = $operation === 'add'
            ? $this->stock_quantity + $quantity
            : $this->stock_quantity - $quantity;

        $this->update(['stock_quantity' => max(0, $newQuantity)]);
    }

    /**
     * Clear product cache
     */
    public function clearCache(): void
    {
        Cache::forget('active_products');
    }

    /**
     * Set SKU attribute - always convert to uppercase
     */
    public function setSkuAttribute($value)
    {
        $this->attributes['sku'] = strtoupper(trim($value));
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        // Clear cache when product changes
        static::saved(function () {
            Cache::forget('active_products');
        });

        static::deleted(function () {
            Cache::forget('active_products');
        });
    }
}
