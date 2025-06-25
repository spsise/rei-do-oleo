<?php

namespace App\Domain\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Database\Factories\PaymentMethodFactory;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return PaymentMethodFactory::new();
    }

    /**
     * Get the services with this payment method.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Scope to get only active payment methods.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get all active payment methods with cache.
     */
    public static function getActiveCached()
    {
        return Cache::remember('payment_methods', 86400, function () {
            return self::active()->ordered()->get();
        });
    }

    /**
     * Get payment method by name.
     */
    public static function findByName(string $name)
    {
        $methods = self::getActiveCached();
        return $methods->firstWhere('name', $name);
    }

    /**
     * Get services count.
     */
    public function getServicesCountAttribute(): int
    {
        return $this->services()->count();
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when payment method changes
        static::saved(function () {
            Cache::forget('payment_methods');
        });

        static::deleted(function () {
            Cache::forget('payment_methods');
        });
    }
}
