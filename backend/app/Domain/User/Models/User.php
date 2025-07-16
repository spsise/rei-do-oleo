<?php

namespace App\Domain\User\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Domain\Service\Models\ServiceCenter;
use App\Domain\Service\Models\Service;
use Database\Factories\UserFactory;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The guard used by Spatie Permission.
     */
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'whatsapp',
        'document',
        'birth_date',
        'hire_date',
        'salary',
        'commission_rate',
        'specialties',
        'password',
        'active',
        'last_login_at',
        'service_center_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'specialties' => 'array',
        'active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /**
     * Get the service center that the user belongs to.
     */
    public function serviceCenter()
    {
        return $this->belongsTo(ServiceCenter::class);
    }

    /**
     * Get the services created by this user.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get users by service center.
     */
    public function scopeByServiceCenter($query, $serviceCenterId)
    {
        return $query->where('service_center_id', $serviceCenterId);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is manager.
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Check if user is technician.
     */
    public function isTechnician(): bool
    {
        return $this->hasRole('technician');
    }

    /**
     * Scope to get users by role.
     */
    public function scopeByRole($query, $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    /**
     * Get services handled as technician.
     */
    public function technicalServices()
    {
        return $this->hasMany(Service::class, 'technician_id');
    }

    /**
     * Get services attended (created by this user).
     */
    public function attendedServices()
    {
        return $this->hasMany(Service::class, 'attendant_id');
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Get the highest role based on predefined hierarchy.
     */
    public function getHighestRole(): string
    {
        // Ensure roles are loaded
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        if ($this->roles->isEmpty()) {
            return 'guest';
        }

        // Define role hierarchy (highest to lowest)
        $roleHierarchy = [
            'admin' => 4,
            'manager' => 3,
            'attendant' => 2,
            'technician' => 1
        ];

        // Get user's highest role based on hierarchy
        $highestRole = $this->roles
            ->map(function ($role) use ($roleHierarchy) {
                return [
                    'name' => $role->name,
                    'priority' => $roleHierarchy[$role->name] ?? 0
                ];
            })
            ->sortByDesc('priority')
            ->first();

        return $highestRole['name'] ?? $this->roles->first()->name;
    }

    /**
     * Check if user has higher or equal role than specified.
     */
    public function hasRoleLevel(string $minimumRole): bool
    {
        $roleHierarchy = [
            'admin' => 4,
            'manager' => 3,
            'attendant' => 2,
            'technician' => 1
        ];

        $userLevel = $roleHierarchy[$this->getHighestRole()] ?? 0;
        $minimumLevel = $roleHierarchy[$minimumRole] ?? 0;

        return $userLevel >= $minimumLevel;
    }

}
