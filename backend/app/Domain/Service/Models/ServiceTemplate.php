<?php

namespace App\Domain\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class ServiceTemplate extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'category',
        'estimated_duration',
        'priority',
        'notes',
        'service_items',
        'active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'estimated_duration' => 'integer',
        'service_items' => 'array',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to get only active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get all active templates with cache.
     */
    public static function getActiveCached($category = null)
    {
        $cacheKey = "service_templates" . ($category ? "_$category" : "");

        return Cache::remember($cacheKey, 3600, function () use ($category) {
            $query = self::active()->ordered();

            if ($category) {
                $query->byCategory($category);
            }

            return $query->get();
        });
    }

    /**
     * Get template by name.
     */
    public static function findByName(string $name)
    {
        return self::where('name', $name)->first();
    }

    /**
     * Get templates by category.
     */
    public static function getByCategory(string $category)
    {
        return self::getActiveCached($category);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->estimated_duration) {
            return 'N/A';
        }

        $hours = intval($this->estimated_duration / 60);
        $minutes = $this->estimated_duration % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }

        return "{$minutes}min";
    }

    /**
     * Get priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            default => 'Média'
        };
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'maintenance' => 'Manutenção',
            'repair' => 'Reparo',
            'inspection' => 'Inspeção',
            'emergency' => 'Emergência',
            'preventive' => 'Preventiva',
            default => 'Geral'
        };
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when template changes
        static::saved(function () {
            Cache::forget('service_templates');
            Cache::forget('service_templates_maintenance');
            Cache::forget('service_templates_repair');
            Cache::forget('service_templates_inspection');
            Cache::forget('service_templates_emergency');
            Cache::forget('service_templates_preventive');
        });

        static::deleted(function () {
            Cache::forget('service_templates');
            Cache::forget('service_templates_maintenance');
            Cache::forget('service_templates_repair');
            Cache::forget('service_templates_inspection');
            Cache::forget('service_templates_emergency');
            Cache::forget('service_templates_preventive');
        });
    }
}
