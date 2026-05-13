<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Default Categories (used in seeder)
    // -------------------------------------------------------------------------

    const DEFAULTS = [
        [
            'name'        => 'Pothole',
            'slug'        => 'pothole',
            'icon'        => '🕳️',
            'color'       => 'red',
            'description' => 'Holes or craters on the road surface caused by wear or damage.',
            'is_active'   => true,
        ],
        [
            'name'        => 'Waterlogging',
            'slug'        => 'waterlogging',
            'icon'        => '🌊',
            'color'       => 'blue',
            'description' => 'Accumulation of water on roads due to poor drainage.',
            'is_active'   => true,
        ],
        [
            'name'        => 'Broken Divider',
            'slug'        => 'broken-divider',
            'icon'        => '🚧',
            'color'       => 'orange',
            'description' => 'Damaged or missing road dividers or median barriers.',
            'is_active'   => true,
        ],
        [
            'name'        => 'Cracked Road',
            'slug'        => 'cracked-road',
            'icon'        => '⚠️',
            'color'       => 'yellow',
            'description' => 'Visible cracks or fissures on road surface.',
            'is_active'   => true,
        ],
        [
            'name'        => 'Drainage Issue',
            'slug'        => 'drainage-issue',
            'icon'        => '🚿',
            'color'       => 'teal',
            'description' => 'Blocked or broken drainage systems causing flooding.',
            'is_active'   => true,
        ],
        [
            'name'        => 'Street Light',
            'slug'        => 'street-light',
            'icon'        => '💡',
            'color'       => 'purple',
            'description' => 'Non-functional or damaged street lights.',
            'is_active'   => true,
        ],
        [
            'name'        => 'Road Collapse',
            'slug'        => 'road-collapse',
            'icon'        => '🏚️',
            'color'       => 'rose',
            'description' => 'Serious structural failure or sinkhole on road.',
            'is_active'   => true,
        ],
        [
            'name'        => 'Other',
            'slug'        => 'other',
            'icon'        => '📌',
            'color'       => 'gray',
            'description' => 'Any other road-related issue not listed above.',
            'is_active'   => true,
        ],
    ];

    // -------------------------------------------------------------------------
    // Mass Assignable Fields
    // -------------------------------------------------------------------------

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'description',
        'is_active',
        'sort_order',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Default Values
    // -------------------------------------------------------------------------

    protected $attributes = [
        'is_active'  => true,
        'sort_order' => 0,
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * All complaints filed under this category.
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * Only pending complaints in this category.
     */
    public function pendingComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class)
                    ->where('status', Complaint::STATUS_PENDING);
    }

    /**
     * Only resolved complaints in this category.
     */
    public function resolvedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class)
                    ->where('status', Complaint::STATUS_RESOLVED);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Total complaint count for this category.
     */
    public function getComplaintsCountAttribute(): int
    {
        return $this->complaints()->count();
    }

    /**
     * Resolution rate as a percentage string e.g. "72%".
     */
    public function getResolutionRateAttribute(): string
    {
        $total    = $this->complaints()->count();
        $resolved = $this->resolvedComplaints()->count();

        if ($total === 0) return '0%';

        return round(($resolved / $total) * 100) . '%';
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
