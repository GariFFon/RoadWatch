<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusHistory extends Model
{
    use HasFactory;

    // No updated_at needed — logs are immutable once written
    const UPDATED_AT = null;

    // -------------------------------------------------------------------------
    // Mass Assignable Fields
    // -------------------------------------------------------------------------

    protected $fillable = [
        'complaint_id',
        'changed_by',
        'old_status',
        'new_status',
        'remarks',
        'time_in_previous_status', // seconds the complaint spent in old_status
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'time_in_previous_status' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Model Events — auto-calculate time spent in previous status
    // -------------------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (StatusHistory $log) {
            // Find the previous log entry to calculate how long the complaint
            // sat in the old status before this change was made
            $previousLog = static::where('complaint_id', $log->complaint_id)
                ->latest('created_at')
                ->first();

            $since = $previousLog
                ? $previousLog->created_at
                : optional(Complaint::find($log->complaint_id))->created_at;

            // abs() is critical: MySQL UNSIGNED INT rejects negatives (strict mode).
            // Carbon 3.x changed diffInSeconds() default to absolute=false, unlike
            // Carbon 2.x. Any clock/timezone drift between app & DB can flip the sign.
            $log->time_in_previous_status = $since
                ? abs((int) now()->diffInSeconds($since))
                : null;
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The complaint this log entry belongs to.
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * The admin/engineer who made the status change.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Human-readable label for old status.
     */
    public function getOldStatusLabelAttribute(): string
    {
        return $this->formatStatusLabel($this->old_status);
    }

    /**
     * Human-readable label for new status.
     */
    public function getNewStatusLabelAttribute(): string
    {
        return $this->formatStatusLabel($this->new_status);
    }

    /**
     * Icon arrow showing direction of status change.
     * e.g. "Pending → Under Review"
     */
    public function getTransitionLabelAttribute(): string
    {
        return $this->old_status_label.' → '.$this->new_status_label;
    }

    /**
     * How long the complaint spent in the previous status, human-readable.
     * e.g. "2 days", "3 hours", "45 minutes"
     */
    public function getTimeInPreviousStatusHumanAttribute(): string
    {
        $seconds = $this->time_in_previous_status;

        if (! $seconds) {
            return 'N/A';
        }

        if ($seconds >= 86400) {
            return round($seconds / 86400).' day(s)';
        }
        if ($seconds >= 3600) {
            return round($seconds / 3600).' hour(s)';
        }
        if ($seconds >= 60) {
            return round($seconds / 60).' minute(s)';
        }

        return $seconds.' second(s)';
    }

    /**
     * Badge color for the new status.
     */
    public function getNewStatusColorAttribute(): string
    {
        return match ($this->new_status) {
            Complaint::STATUS_PENDING => 'yellow',
            Complaint::STATUS_UNDER_REVIEW => 'blue',
            Complaint::STATUS_IN_PROGRESS => 'indigo',
            Complaint::STATUS_AWAITING_VERIFICATION => 'orange',
            Complaint::STATUS_VERIFIED => 'green',
            Complaint::STATUS_REJECTED => 'red',
            default => 'gray',
        };
    }

    // -------------------------------------------------------------------------
    // Static Helpers
    // -------------------------------------------------------------------------

    /**
     * Get full timeline for a complaint, ordered chronologically.
     * Use this to render the status timeline on the complaint detail page.
     *
     * @return Collection
     */
    public static function timelineFor(int $complaintId)
    {
        return static::where('complaint_id', $complaintId)
            ->with('changedBy:id,name,role')
            ->oldest('created_at')
            ->get();
    }

    /**
     * Total resolution time in seconds (pending → resolved).
     * Returns null if complaint is not yet resolved.
     */
    public static function resolutionTimeFor(int $complaintId): ?int
    {
        $first = static::where('complaint_id', $complaintId)
            ->oldest()
            ->value('created_at');

        $resolved = static::where('complaint_id', $complaintId)
            ->where('new_status', Complaint::STATUS_VERIFIED)
            ->value('created_at');

        if (! $first || ! $resolved) {
            return null;
        }

        return (int) Carbon::parse($first)
            ->diffInSeconds(Carbon::parse($resolved));
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    public function scopeForComplaint($query, int $complaintId)
    {
        return $query->where('complaint_id', $complaintId);
    }

    public function scopeByChanger($query, int $userId)
    {
        return $query->where('changed_by', $userId);
    }

    public function scopeTransitionedTo($query, string $status)
    {
        return $query->where('new_status', $status);
    }

    public function scopeRejections($query)
    {
        return $query->where('new_status', Complaint::STATUS_REJECTED);
    }

    public function scopeResolutions($query)
    {
        // Verified = the terminal "resolved" state in this system
        return $query->where('new_status', Complaint::STATUS_VERIFIED);
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    private function formatStatusLabel(string $status): string
    {
        return match ($status) {
            Complaint::STATUS_PENDING => 'Pending',
            Complaint::STATUS_UNDER_REVIEW => 'Under Review',
            Complaint::STATUS_IN_PROGRESS => 'In Progress',
            Complaint::STATUS_AWAITING_VERIFICATION => 'Awaiting Verification',
            Complaint::STATUS_VERIFIED => 'Verified',
            Complaint::STATUS_REJECTED => 'Rejected',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }
}
