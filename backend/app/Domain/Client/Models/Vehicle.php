<?php

namespace App\Domain\Client\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Domain\Service\Models\Service;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'client_id',
        'license_plate',
        'brand',
        'model',
        'year',
        'color',
        'fuel_type',
        'mileage',
        'last_service',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'year' => 'integer',
        'mileage' => 'integer',
        'last_service' => 'date',
    ];

    /**
     * Get the client that owns the vehicle.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the services for the vehicle.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the latest service for the vehicle.
     */
    public function lastService()
    {
        return $this->hasOne(Service::class)->latest();
    }

    /**
     * Scope to search by license plate.
     */
    public function scopeSearchByPlate($query, $plate)
    {
        return $query->where('license_plate', 'like', "%{$plate}%");
    }

    /**
     * Scope to search by brand.
     */
    public function scopeSearchByBrand($query, $brand)
    {
        return $query->where('brand', 'like', "%{$brand}%");
    }

    /**
     * Get formatted license plate.
     */
    public function getFormattedLicensePlateAttribute(): string
    {
        $plate = $this->license_plate;

        // Format ABC-1234 or ABC1D23
        if (strlen($plate) == 7) {
            return substr($plate, 0, 3) . '-' . substr($plate, 3);
        }

        return $plate;
    }

    /**
     * Get vehicle full description.
     */
    public function getFullDescriptionAttribute(): string
    {
        $parts = array_filter([
            $this->brand,
            $this->model,
            $this->year,
            $this->color
        ]);

        return implode(' ', $parts);
    }

    /**
     * Get services count.
     */
    public function getServicesCountAttribute(): int
    {
        return $this->services()->count();
    }

    /**
     * Update mileage and last service date.
     */
    public function updateServiceInfo(int $mileage, $serviceDate = null): void
    {
        $this->update([
            'mileage' => $mileage,
            'last_service' => $serviceDate ?? now()->toDateString(),
        ]);
    }

    /**
     * Validate Brazilian license plate format.
     */
    public static function validateLicensePlate(string $plate): bool
    {
        // Remove spaces and convert to uppercase
        $plate = strtoupper(str_replace(' ', '', $plate));

        // Check old format (ABC-1234) or new format (ABC1D23)
        return preg_match('/^[A-Z]{3}-?\d{4}$/', $plate) ||
               preg_match('/^[A-Z]{3}\d[A-Z]\d{2}$/', $plate);
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($vehicle) {
            // Clear client cache when vehicle is updated
            Cache::forget("client_plate_{$vehicle->license_plate}");
        });

        static::deleted(function ($vehicle) {
            Cache::forget("client_plate_{$vehicle->license_plate}");
        });
    }
}
