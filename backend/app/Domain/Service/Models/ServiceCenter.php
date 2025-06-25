<?php

namespace App\Domain\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Domain\User\Models\User;

class ServiceCenter extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'slug',
        'cnpj',
        'state_registration',
        'legal_name',
        'trade_name',
        'address_line',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'latitude',
        'longitude',
        'phone',
        'whatsapp',
        'email',
        'website',
        'facebook_url',
        'instagram_url',
        'google_maps_url',
        'manager_id',
        'technical_responsible',
        'opening_date',
        'operating_hours',
        'is_main_branch',
        'active',
        'observations',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'opening_date' => 'date',
        'is_main_branch' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Get the manager of the service center.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the users of the service center.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the services of the service center.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Scope to get only active service centers.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get by region.
     */
    public function scopeByRegion($query, $state, $city = null)
    {
        $query = $query->where('state', $state);

        if ($city) {
            $query->where('city', $city);
        }

        return $query;
    }

    /**
     * Scope to get main branch.
     */
    public function scopeMainBranch($query)
    {
        return $query->where('is_main_branch', true);
    }

    /**
     * Scope to find nearby centers.
     */
    public function scopeNearby($query, $latitude, $longitude, $radiusKm = 10)
    {
        return $query->whereRaw(
            "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) < ?",
            [$latitude, $longitude, $latitude, $radiusKm]
        );
    }

    /**
     * Get all active centers with cache.
     */
    public static function getActiveCached()
    {
        return Cache::remember('active_service_centers', 43200, function () {
            return self::active()->with('manager')->orderBy('name')->get();
        });
    }

    /**
     * Get centers by region with cache.
     */
    public static function getByRegionCached($state, $city = null)
    {
        $cacheKey = "service_centers_region_{$state}" . ($city ? "_{$city}" : '');

        return Cache::remember($cacheKey, 43200, function () use ($state, $city) {
            return self::active()
                      ->byRegion($state, $city)
                      ->with('manager')
                      ->orderBy('name')
                      ->get();
        });
    }

    /**
     * Find by code.
     */
    public static function findByCode(string $code)
    {
        return self::where('code', $code)->first();
    }

    /**
     * Get main branch.
     */
    public static function getMainBranch()
    {
        return self::mainBranch()->active()->first();
    }

    /**
     * Get full address string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line,
            $this->number,
            $this->complement,
            $this->neighborhood,
            $this->city,
            $this->state,
            $this->zip_code
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get formatted phone.
     */
    public function getFormattedPhoneAttribute(): string
    {
        return $this->phone ?? '';
    }

    /**
     * Get services count.
     */
    public function getServicesCountAttribute(): int
    {
        return $this->services()->count();
    }

    /**
     * Get active services count.
     */
    public function getActiveServicesCountAttribute(): int
    {
        return $this->services()->where('active', true)->count();
    }

    /**
     * Get distance from coordinates.
     */
    public function getDistanceFrom($latitude, $longitude): float
    {
        if (!$this->latitude || !$this->longitude) {
            return 0;
        }

        $earthRadius = 6371; // km

        $dLat = deg2rad($latitude - $this->latitude);
        $dLng = deg2rad($longitude - $this->longitude);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug
        static::creating(function ($serviceCenter) {
            if (empty($serviceCenter->slug)) {
                $serviceCenter->slug = Str::slug($serviceCenter->name);
            }
        });

        static::updating(function ($serviceCenter) {
            if ($serviceCenter->isDirty('name') && empty($serviceCenter->slug)) {
                $serviceCenter->slug = Str::slug($serviceCenter->name);
            }
        });

        // Clear cache when service center changes
        static::saved(function ($serviceCenter) {
            Cache::forget('active_service_centers');
            Cache::forget("service_centers_region_{$serviceCenter->state}");
            Cache::forget("service_centers_region_{$serviceCenter->state}_{$serviceCenter->city}");
        });

        static::deleted(function ($serviceCenter) {
            Cache::forget('active_service_centers');
            Cache::forget("service_centers_region_{$serviceCenter->state}");
            Cache::forget("service_centers_region_{$serviceCenter->state}_{$serviceCenter->city}");
        });
    }
}
