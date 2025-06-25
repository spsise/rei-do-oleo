<?php

namespace App\Domain\Client\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use App\Domain\Service\Models\Service;
use Database\Factories\ClientFactory;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ClientFactory::new();
    }

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * Scope to filter by state.
     */
    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Scope to filter by city.
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope to search by document (CPF or CNPJ).
     */
    public function scopeSearchByDocument($query, $document)
    {
        $cleanDocument = preg_replace('/\D/', '', $document);
        return $query->where('cpf', 'like', "%{$cleanDocument}%")
                    ->orWhere('cnpj', 'like', "%{$cleanDocument}%");
    }

    /**
     * Combined search scope.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('cpf', 'like', "%{$term}%")
              ->orWhere('cnpj', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone01', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to filter by document type.
     */
    public function scopeByDocumentType($query, $type)
    {
        if ($type === 'individual') {
            return $query->whereNotNull('cpf')->whereNull('cnpj');
        } elseif ($type === 'company') {
            return $query->whereNotNull('cnpj')->whereNull('cpf');
        }

        return $query;
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
        // Use loaded relationship if available to avoid additional query
        if ($this->relationLoaded('services')) {
            return $this->services->count();
        }

        return $this->services()->count();
    }

    /**
     * Get total amount spent.
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->services()
                    ->whereNotNull('total_amount')
                    ->sum('total_amount');
    }

    /**
     * Get last service date.
     */
    public function getLastServiceDateAttribute(): ?string
    {
        $lastService = $this->services()->latest('created_at')->first();
        return $lastService ? $lastService->created_at->format('Y-m-d') : null;
    }

    /**
     * Get next service reminder date.
     */
    public function getNextServiceReminderAttribute(): ?\Carbon\Carbon
    {
        $lastService = $this->services()->latest('created_at')->first();
        if (!$lastService) {
            return null;
        }

        return $lastService->created_at->addMonths(6);
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

    /**
     * Scope to search by region (advanced feature).
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
     * Scope to get VIP clients (advanced feature).
     */
    public function scopeVip($query, $minimumSpent = 5000)
    {
        return $query->whereHas('services', function ($q) use ($minimumSpent) {
            $q->havingRaw('SUM(total_amount) >= ?', [$minimumSpent]);
        });
    }
}
