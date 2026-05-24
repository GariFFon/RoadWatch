<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable;

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------

    const ROLE_CITIZEN = 'citizen';

    const ROLE_ENGINEER = 'engineer';

    const ROLE_ADMIN = 'admin';

    const GENDER_MALE = 'male';

    const GENDER_FEMALE = 'female';

    const GENDER_OTHER = 'other';

    const GENDER_PREFER_NOT = 'prefer_not_to_say';

    const AUTH_EMAIL = 'email';

    const AUTH_GOOGLE = 'google';

    // -------------------------------------------------------------------------
    // Mass Assignable Fields
    // -------------------------------------------------------------------------

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'gender',
        'profile_photo',
        'profile_banner_url',
        'address',
        'city',
        'state',
        'latitude',
        'longitude',
        'notification_preferences',
        'is_active',
        'is_verified',
        'last_login_at',
        // Google OAuth
        'google_id',
        'auth_provider',
        'google_avatar',
        'password_set',
    ];

    // -------------------------------------------------------------------------
    // Hidden Fields (never returned in JSON/arrays)
    // -------------------------------------------------------------------------

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'password_set' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'notification_preferences' => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Default Values
    // -------------------------------------------------------------------------

    protected $attributes = [
        'role' => self::ROLE_CITIZEN,
        'is_active' => true,
        'is_verified' => false,
        'auth_provider' => self::AUTH_EMAIL,
        'password_set' => true,   // false for new Google OAuth users
        'notification_preferences' => '{"email":true,"sms":false,"push":true}',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Issues reported by this citizen.
     */
    public function reportedIssues(): HasMany
    {
        return $this->hasMany(RoadIssue::class, 'user_id');
    }

    /**
     * Issues assigned to this engineer.
     */
    public function assignedIssues(): HasMany
    {
        return $this->hasMany(RoadIssue::class, 'assigned_to');
    }

    /**
     * Issues this user has upvoted.
     */
    public function upvotes(): HasMany
    {
        return $this->hasMany(IssueUpvote::class);
    }

    /**
     * Status log entries changed by this user.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(IssueStatusLog::class, 'changed_by');
    }

    /**
     * Media files uploaded by this user.
     */
    public function uploadedMedia(): HasMany
    {
        return $this->hasMany(IssueMedia::class, 'uploaded_by');
    }

    // -------------------------------------------------------------------------
    // Role Helper Methods
    // -------------------------------------------------------------------------

    public function isCitizen(): bool
    {
        return $this->role === self::ROLE_CITIZEN;
    }

    public function isEngineer(): bool
    {
        return $this->role === self::ROLE_ENGINEER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isAuthority(): bool
    {
        return in_array($this->role, [self::ROLE_ENGINEER, self::ROLE_ADMIN]);
    }

    // -------------------------------------------------------------------------
    // Google OAuth Helper Methods
    // -------------------------------------------------------------------------

    /**
     * Whether this user signed in via Google OAuth.
     */
    public function isGoogleUser(): bool
    {
        return $this->auth_provider === self::AUTH_GOOGLE;
    }

    /**
     * Whether this Google user still needs to set a password.
     * Redirect them to the set-password screen if true.
     */
    public function needsPasswordSetup(): bool
    {
        return $this->isGoogleUser() && ! $this->password_set;
    }

    /**
     * Mark that the user has completed password setup.
     */
    public function markPasswordAsSet(): void
    {
        $this->forceFill(['password_set' => true])->save();
    }

    // -------------------------------------------------------------------------
    // Notification Preference Helpers
    // -------------------------------------------------------------------------

    public function wantsEmailNotification(): bool
    {
        return $this->notification_preferences['email'] ?? true;
    }

    public function wantsSmsNotification(): bool
    {
        return $this->notification_preferences['sms'] ?? false;
    }

    public function wantsPushNotification(): bool
    {
        return $this->notification_preferences['push'] ?? true;
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Get full profile photo URL — supports S3 URLs, local storage, Google avatar.
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if ($this->profile_photo) {
            // If already a full URL (S3), return as-is
            if (str_starts_with($this->profile_photo, 'http')) {
                return $this->profile_photo;
            }

            return asset('storage/'.$this->profile_photo);
        }

        // Use Google avatar for OAuth users
        if ($this->google_avatar) {
            return $this->google_avatar;
        }

        return null; // frontend shows initials
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeCitizens($query)
    {
        return $query->where('role', self::ROLE_CITIZEN);
    }

    public function scopeEngineers($query)
    {
        return $query->where('role', self::ROLE_ENGINEER);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** Complaints submitted by this citizen */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'user_id');
    }

    /** Complaints assigned to this engineer/admin */
    public function assignedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_to');
    }

    /** Feedback given by this user */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class, 'user_id');
    }
}
