<?php

namespace App\Domain\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\User\Models\User;
use Database\Factories\ServiceFactory;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ServiceFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'client_id',
        'vehicle_id',
        'user_id',
        'service_center_id',
        'service_number',
        'scheduled_at',
        'started_at',
        'completed_at',
        'service_status_id',
        'payment_method_id',
        'mileage_at_service',
        'total_amount',
        'discount_amount',
        'final_amount',
        'observations',
        'notes',
        'active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'mileage_at_service' => 'integer',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Get the client that owns the service.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the vehicle of the service.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user that created the service.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the service center of the service.
     */
    public function serviceCenter()
    {
        return $this->belongsTo(ServiceCenter::class);
    }

    /**
     * Get the service status.
     */
    public function serviceStatus()
    {
        return $this->belongsTo(ServiceStatus::class);
    }

    /**
     * Get the payment method.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the service items.
     */
    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class);
    }

    /**
     * Scope to get only active services.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $statusId)
    {
        return $query->where('service_status_id', $statusId);
    }

    /**
     * Scope to filter by service center.
     */
    public function scopeByServiceCenter($query, $serviceCenterId)
    {
        return $query->where('service_center_id', $serviceCenterId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('scheduled_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get completed services.
     */
    public function scopeCompleted($query)
    {
        return $query->whereHas('serviceStatus', function ($q) {
            $q->where('name', 'completed');
        });
    }

    /**
     * Scope to get services in progress.
     */
    public function scopeInProgress($query)
    {
        return $query->whereHas('serviceStatus', function ($q) {
            $q->where('name', 'in_progress');
        });
    }

    /**
     * Scope to get scheduled services.
     */
    public function scopeScheduled($query)
    {
        return $query->whereHas('serviceStatus', function ($q) {
            $q->where('name', 'scheduled');
        });
    }

    /**
     * Generate unique service number.
     */
    public static function generateServiceNumber(): string
    {
        $prefix = 'SRV';
        $date = now()->format('Ymd');
        $sequence = str_pad((self::whereDate('created_at', today())->count() + 1), 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$date}{$sequence}";
    }

    /**
     * Get services by date range.
     */
    public static function getServicesByDateRange($startDate, $endDate)
    {
        return self::byPeriod($startDate, $endDate)
                   ->with(['client', 'vehicle', 'serviceCenter', 'serviceStatus'])
                   ->orderBy('scheduled_at', 'desc')
                   ->get();
    }

    /**
     * Get services by client with cache.
     */
    public static function getServicesByClient($clientId)
    {
        return Cache::remember(
            "client_services_{$clientId}",
            1800,
            function () use ($clientId) {
                return self::where('client_id', $clientId)
                          ->with(['vehicle', 'serviceStatus', 'serviceCenter'])
                          ->orderBy('scheduled_at', 'desc')
                          ->get();
            }
        );
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationInMinutesAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->duration_in_minutes;

        if (!$minutes) {
            return 'N/A';
        }

        $hours = intval($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$remainingMinutes}min";
        }

        return "{$remainingMinutes}min";
    }

    /**
     * Get status name.
     */
    public function getStatusNameAttribute(): string
    {
        return $this->serviceStatus->name ?? 'unknown';
    }

    /**
     * Check if service is completed.
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status_name === 'completed';
    }

    /**
     * Check if service is in progress.
     */
    public function getIsInProgressAttribute(): bool
    {
        return $this->status_name === 'in_progress';
    }

    /**
     * Get total items count.
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->serviceItems()->count();
    }

    /**
     * Start service.
     */
    public function startService(): void
    {
        $inProgressStatus = ServiceStatus::findByName('in_progress');

        $this->update([
            'started_at' => now(),
            'service_status_id' => $inProgressStatus ? $inProgressStatus->id : 2, // Default to ID 2
        ]);
    }

    /**
     * Complete service.
     */
    public function completeService(): void
    {
        $completedStatus = ServiceStatus::findByName('completed');

        $this->update([
            'completed_at' => now(),
            'service_status_id' => $completedStatus ? $completedStatus->id : 3, // Default to ID 3
        ]);

        // Update vehicle info
        if ($this->mileage_at_service && $this->vehicle) {
            $this->vehicle->updateServiceInfo($this->mileage_at_service, $this->completed_at);
        }
    }

    /**
     * Calculate totals from service items.
     */
    public function calculateTotals(): void
    {
        $totalAmount = $this->serviceItems()->sum('total_price');
        $finalAmount = $totalAmount - ($this->discount_amount ?? 0);

        $this->update([
            'total_amount' => $totalAmount,
            'final_amount' => max(0, $finalAmount),
        ]);
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate service number on creation
        static::creating(function ($service) {
            if (empty($service->service_number)) {
                $service->service_number = self::generateServiceNumber();
            }

            // Set default status if not provided
            if (!$service->service_status_id) {
                $scheduledStatus = ServiceStatus::findByName('scheduled');
                $service->service_status_id = $scheduledStatus ? $scheduledStatus->id : 1; // Default to ID 1
            }
        });
    }
}
