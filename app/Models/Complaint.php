<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Complaint extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------

    const SEVERITY_LOW       = 'low';
    const SEVERITY_MEDIUM    = 'medium';
    const SEVERITY_HIGH      = 'high';
    const SEVERITY_EMERGENCY = 'emergency';

    const SEVERITY_LEVELS = [
        self::SEVERITY_LOW,
        self::SEVERITY_MEDIUM,
        self::SEVERITY_HIGH,
        self::SEVERITY_EMERGENCY,
    ];

    const STATUS_PENDING      = 'pending';
    const STATUS_UNDER_REVIEW  = 'under_review';
    const STATUS_IN_PROGRESS   = 'in_progress';
    const STATUS_RESOLVED      = 'resolved';
    const STATUS_REJECTED      = 'rejected';   // optional, from any stage

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_IN_PROGRESS,
        self::STATUS_RESOLVED,
        self::STATUS_REJECTED,
    ];

    /**
     * Valid status transitions.
     * Pending → Under Review → In Progress → Resolved
     * Rejected can be set from any non-resolved stage.
     */
    const VALID_TRANSITIONS = [
        self::STATUS_PENDING      => [self::STATUS_UNDER_REVIEW, self::STATUS_REJECTED],
        self::STATUS_UNDER_REVIEW => [self::STATUS_IN_PROGRESS,  self::STATUS_REJECTED],
        self::STATUS_IN_PROGRESS  => [self::STATUS_RESOLVED,      self::STATUS_REJECTED],
        self::STATUS_RESOLVED     => [],   // terminal state
        self::STATUS_REJECTED     => [],   // terminal state
    ];

    // -------------------------------------------------------------------------
    // Mass Assignable Fields
    // -------------------------------------------------------------------------

    protected $fillable = [
        'complaint_number',
        'user_id',
        'category_id',
        'assigned_to',
        'title',
        'description',
        'latitude',
        'longitude',
        'location',
        'severity',
        'status',
        'votes_count',
        'views_count',
        'is_duplicate',
        'duplicate_of',
        'rejection_reason',
        'estimated_completion',
        'resolved_at',
        'is_anonymous',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'latitude'             => 'decimal:7',
            'longitude'            => 'decimal:7',
            'votes_count'          => 'integer',
            'views_count'          => 'integer',
            'is_duplicate'         => 'boolean',
            'is_anonymous'         => 'boolean',
            'resolved_at'          => 'datetime',
            'estimated_completion' => 'date',
        ];
    }

    // -------------------------------------------------------------------------
    // Default Values
    // -------------------------------------------------------------------------

    protected $attributes = [
        'status'       => self::STATUS_PENDING,
        'severity'     => self::SEVERITY_MEDIUM,
        'votes_count'  => 0,
        'views_count'  => 0,
        'is_duplicate' => false,
        'is_anonymous' => false,
    ];

    // -------------------------------------------------------------------------
    // Model Events — auto-generate complaint number on create
    // -------------------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (Complaint $complaint) {
            if (empty($complaint->complaint_number)) {
                $complaint->complaint_number = static::generateComplaintNumber();
            }
        });
    }

    public static function generateComplaintNumber(): string
    {
        $year   = now()->format('Y');
        $latest = static::whereYear('created_at', $year)->count() + 1;
        return 'RW-' . $year . '-' . str_pad($latest, 5, '0', STR_PAD_LEFT);
        // e.g. RW-2025-00042
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The citizen who filed this complaint.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The engineer assigned to resolve this complaint.
     */
    public function assignedEngineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * The issue category (pothole, flooding, etc.)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The original complaint this is a duplicate of.
     */
    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(Complaint::class, 'duplicate_of');
    }

    /**
     * All media (images/videos) attached to this complaint.
     * Stored on cloud (S3 / Cloudinary).
     */
    public function media(): HasMany
    {
        return $this->hasMany(ComplaintMedia::class);
    }

    /**
     * Only images attached to this complaint.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ComplaintMedia::class)->where('file_type', 'image');
    }

    /**
     * Only videos attached to this complaint.
     */
    public function videos(): HasMany
    {
        return $this->hasMany(ComplaintMedia::class)->where('file_type', 'video');
    }

    /**
     * "Before" media — uploaded by citizen when reporting.
     */
    public function beforeMedia(): HasMany
    {
        return $this->hasMany(ComplaintMedia::class)->where('stage', 'before');
    }

    /**
     * "After" media — uploaded by engineer after fixing.
     */
    public function afterMedia(): HasMany
    {
        return $this->hasMany(ComplaintMedia::class)->where('stage', 'after');
    }

    /**
     * Status change audit trail (StatusHistory).
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class)->oldest();
    }

    /**
     * Comments / public discussion on this complaint.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Upvotes from citizens.
     */
    public function upvotes(): HasMany
    {
        return $this->hasMany(IssueUpvote::class);
    }

    // -------------------------------------------------------------------------
    // Status Helper Methods
    // -------------------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isEmergency(): bool
    {
        return $this->severity === self::SEVERITY_EMERGENCY;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [self::STATUS_RESOLVED, self::STATUS_REJECTED]);
    }

    /**
     * Check if a transition to $newStatus is allowed from the current status.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::VALID_TRANSITIONS[$this->status] ?? []);
    }

    /**
     * Update status with transition guard + automatic audit log.
     * Throws \LogicException if the transition is invalid.
     */
    public function updateStatus(string $newStatus, User $changedBy, ?string $remarks = null): void
    {
        if (! $this->canTransitionTo($newStatus)) {
            throw new \LogicException(
                "Invalid transition: [{$this->status}] → [{$newStatus}]"
            );
        }

        $old = $this->status;
        $this->update(['status' => $newStatus]);

        // Auto-timestamp resolution
        if ($newStatus === self::STATUS_RESOLVED) {
            $this->update(['resolved_at' => now()]);
        }

        // Write to status_histories audit log
        $this->statusHistories()->create([
            'changed_by' => $changedBy->id,
            'old_status' => $old,
            'new_status' => $newStatus,
            'remarks'    => $remarks,
        ]);
    }

    /**
     * Full status timeline — pass to view for complaint detail page.
     */
    public function timeline()
    {
        return StatusHistory::timelineFor($this->id);
    }

    /**
     * Total resolution time in human-readable form.
     */
    public function resolutionTime(): ?string
    {
        $seconds = StatusHistory::resolutionTimeFor($this->id);
        if (! $seconds) return null;

        if ($seconds >= 86400) return round($seconds / 86400) . ' day(s)';
        if ($seconds >= 3600)  return round($seconds / 3600)  . ' hour(s)';
        return round($seconds / 60) . ' minute(s)';
    }

    /**
     * Get the next valid statuses the complaint can move to.
     */
    public function nextStatuses(): array
    {
        return self::VALID_TRANSITIONS[$this->status] ?? [];
    }

    /**
     * Increment view count efficiently (no mass-assignment overhead).
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING      => 'Pending',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_IN_PROGRESS  => 'In Progress',
            self::STATUS_RESOLVED     => 'Resolved',
            self::STATUS_REJECTED     => 'Rejected',
            default                   => ucfirst($this->status),
        };
    }

    /**
     * Tailwind CSS badge color for current status.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING      => 'yellow',
            self::STATUS_UNDER_REVIEW => 'blue',
            self::STATUS_IN_PROGRESS  => 'indigo',
            self::STATUS_RESOLVED     => 'green',
            self::STATUS_REJECTED     => 'red',
            default                   => 'gray',
        };
    }

    /**
     * Severity badge color.
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_LOW       => 'green',
            self::SEVERITY_MEDIUM    => 'yellow',
            self::SEVERITY_HIGH      => 'orange',
            self::SEVERITY_EMERGENCY => 'red',
            default                  => 'gray',
        };
    }

    /**
     * Display name (hide if anonymous).
     */
    public function getReporterNameAttribute(): string
    {
        return $this->is_anonymous ? 'Anonymous Citizen' : ($this->user->name ?? 'Unknown');
    }

    /**
     * First image thumbnail URL for list views.
     */
    public function getThumbnailAttribute(): ?string
    {
        return $this->images()->where('stage', 'before')->value('cloud_url');
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeEmergency($query)
    {
        return $query->where('severity', self::SEVERITY_EMERGENCY);
    }

    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 5)
    {
        // Haversine formula to find complaints within radius
        return $query->selectRaw("
                *, ( 6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeAssignedTo($query, int $engineerId)
    {
        return $query->where('assigned_to', $engineerId);
    }
}
