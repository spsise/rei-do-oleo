<?php

namespace App\Domain\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ServiceStatus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'color',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the services with this status.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get all statuses with cache.
     */
    public static function getAllCached()
    {
        return Cache::remember('service_statuses', 86400, function () {
            return self::ordered()->get();
        });
    }

    /**
     * Get status by name with cache.
     */
    public static function findByName(string $name)
    {
        $statuses = self::getAllCached();
        return $statuses->firstWhere('name', $name);
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

        // Clear cache when status changes
        static::saved(function () {
            Cache::forget('service_statuses');
        });

        static::deleted(function () {
            Cache::forget('service_statuses');
        });
    }
}
