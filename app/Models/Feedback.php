<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    /** Laravel pluralizes 'Feedback' → 'feedback', but the table is 'feedbacks'. */
    protected $table = 'feedbacks';

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------

    const MIN_RATING = 1;
    const MAX_RATING = 5;

    const RATING_LABELS = [
        1 => 'Very Poor',
        2 => 'Poor',
        3 => 'Average',
        4 => 'Good',
        5 => 'Excellent',
    ];

    const RATING_EMOJIS = [
        1 => '😡',
        2 => '😞',
        3 => '😐',
        4 => '😊',
        5 => '😍',
    ];

    // -------------------------------------------------------------------------
    // Mass Assignable Fields
    // -------------------------------------------------------------------------

    protected $fillable = [
        'complaint_id',
        'user_id',
        'rating',        // 1–5 star rating
        'comment',       // optional written feedback
        'is_anonymous',  // citizen can hide identity on feedback too
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'rating'       => 'integer',
            'is_anonymous' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Default Values
    // -------------------------------------------------------------------------

    protected $attributes = [
        'is_anonymous' => false,
    ];

    // -------------------------------------------------------------------------
    // Model Events
    // -------------------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (Feedback $feedback) {
            // Feedback can only be submitted on VERIFIED (or legacy resolved) complaints
            $complaint = Complaint::find($feedback->complaint_id);
            if (! $complaint || !in_array($complaint->status, ['verified', 'resolved'])) {
                throw new \LogicException(
                    'Feedback can only be submitted after a complaint is verified.'
                );
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The complaint this feedback belongs to.
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * The citizen who submitted this feedback.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Human-readable rating label e.g. "Excellent".
     */
    public function getRatingLabelAttribute(): string
    {
        return self::RATING_LABELS[$this->rating] ?? 'Unknown';
    }

    /**
     * Emoji for the rating e.g. "😍".
     */
    public function getRatingEmojiAttribute(): string
    {
        return self::RATING_EMOJIS[$this->rating] ?? '⭐';
    }

    /**
     * Star string e.g. "★★★★☆" for rating 4.
     */
    public function getStarsAttribute(): string
    {
        $filled = str_repeat('★', $this->rating);
        $empty  = str_repeat('☆', self::MAX_RATING - $this->rating);
        return $filled . $empty;
    }

    /**
     * Display name (hide if anonymous).
     */
    public function getReviewerNameAttribute(): string
    {
        return $this->is_anonymous ? 'Anonymous Citizen' : ($this->user->name ?? 'Unknown');
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopePositive($query)
    {
        // Rating 4 or 5
        return $query->where('rating', '>=', 4);
    }

    public function scopeNegative($query)
    {
        // Rating 1 or 2
        return $query->where('rating', '<=', 2);
    }

    public function scopeWithComments($query)
    {
        return $query->whereNotNull('comment')->where('comment', '!=', '');
    }

    // -------------------------------------------------------------------------
    // Static Helpers
    // -------------------------------------------------------------------------

    /**
     * Calculate average rating for a given complaint.
     */
    public static function averageForComplaint(int $complaintId): float
    {
        return round(
            static::where('complaint_id', $complaintId)->avg('rating') ?? 0,
            1
        );
    }

    /**
     * Rating distribution for analytics e.g. [1=>2, 2=>1, 3=>5, 4=>10, 5=>20].
     */
    public static function distributionForComplaint(int $complaintId): array
    {
        $rows = static::where('complaint_id', $complaintId)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        // Fill missing ratings with 0
        return array_replace([1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0], $rows);
    }
}
