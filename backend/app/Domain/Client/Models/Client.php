<?php

namespace App\Domain\Client\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use App\Domain\Service\Models\Service;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'phone01',
        'phone02',
        'email',
        'cpf',
        'cnpj',
        'address',
        'city',
        'state',
        'zip_code',
        'notes',
        'active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the vehicles for the client.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get the services for the client.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the latest service for the client.
     */
    public function lastService()
    {
        return $this->hasOne(Service::class)->latest();
    }

    /**
     * Scope to get only active clients.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to search by name.
     */
    public function scopeSearchByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Scope to search by phone.
     */
    public function scopeSearchByPhone($query, $phone)
    {
        return $query->where('phone01', 'like', "%{$phone}%")
                    ->orWhere('phone02', 'like', "%{$phone}%");
    }

    /**
     * Find client by license plate with cache.
     */
    public static function findByLicensePlate(string $plate): ?self
    {
        return Cache::remember(
            "client_plate_{$plate}",
            3600,
            function () use ($plate) {
                return self::whereHas('vehicles', function ($query) use ($plate) {
                    $query->where('license_plate', $plate);
                })->with('vehicles')->first();
            }
        );
    }

    /**
     * Get full address string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
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
        return $this->phone01;
    }

    /**
     * Get total services count.
     */
    public function getTotalServicesAttribute(): int
    {
        return $this->services()->count();
    }

    /**
     * Get total amount spent.
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->services()
                    ->whereNotNull('final_amount')
                    ->sum('final_amount');
    }

    /**
     * Clear cache for this client.
     */
    public function clearCache(): void
    {
        $this->vehicles->each(function ($vehicle) {
            Cache::forget("client_plate_{$vehicle->license_plate}");
        });
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($client) {
            $client->clearCache();
        });

        static::deleted(function ($client) {
            $client->clearCache();
        });
    }
}
