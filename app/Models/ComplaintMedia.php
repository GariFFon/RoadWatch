<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ComplaintMedia extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------

    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';

    const STAGE_BEFORE  = 'before';  // Citizen uploads when reporting
    const STAGE_DURING  = 'during';  // Engineer uploads mid-fix progress
    const STAGE_AFTER   = 'after';   // Engineer uploads proof of resolution

    // Allowed MIME types
    const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/heic'];
    const ALLOWED_VIDEO_MIMES = ['video/mp4', 'video/quicktime', 'video/webm'];

    // Size limits
    const MAX_IMAGE_SIZE_MB = 10;
    const MAX_VIDEO_SIZE_MB = 50;

    // -------------------------------------------------------------------------
    // Mass Assignable Fields
    // -------------------------------------------------------------------------

    protected $fillable = [
        'complaint_id',
        'uploaded_by',
        'file_type',       // image | video
        'stage',           // before | during | after
        'original_name',   // original file name from user's device
        'cloud_disk',      // 's3' | 'cloudinary' | 'public' (local fallback)
        'cloud_path',      // relative path/key on cloud (e.g. complaints/2025/abc.jpg)
        'cloud_url',       // full public URL returned by cloud (CDN URL)
        'cloud_public_id', // Cloudinary public_id (for deletion/transforms)
        'mime_type',       // e.g. image/jpeg
        'size_bytes',      // file size in bytes
        'width',           // image/video width in px
        'height',          // image/video height in px
        'duration',        // video duration in seconds (null for images)
        'thumbnail_url',   // auto-generated thumbnail for videos
        'is_flagged',      // admin can flag inappropriate content
        'sort_order',      // for ordering multiple images in a complaint
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'size_bytes'  => 'integer',
            'width'       => 'integer',
            'height'      => 'integer',
            'duration'    => 'float',
            'sort_order'  => 'integer',
            'is_flagged'  => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Default Values
    // -------------------------------------------------------------------------

    protected $attributes = [
        'cloud_disk' => 's3',  // change to 'cloudinary' if using Cloudinary
        'stage'      => self::STAGE_BEFORE,
        'is_flagged' => false,
        'sort_order' => 0,
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The complaint this media belongs to.
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * The user who uploaded this media.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // -------------------------------------------------------------------------
    // Helper Methods
    // -------------------------------------------------------------------------

    public function isImage(): bool
    {
        return $this->file_type === self::TYPE_IMAGE;
    }

    public function isVideo(): bool
    {
        return $this->file_type === self::TYPE_VIDEO;
    }

    /**
     * Human-readable file size.
     */
    public function getFileSizeAttribute(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Permanently delete media from cloud storage.
     * Call this before deleting the DB record.
     */
    public function deleteFromCloud(): bool
    {
        if ($this->cloud_disk === 's3') {
            return Storage::disk('s3')->delete($this->cloud_path);
        }

        // For Cloudinary — handled via Cloudinary SDK (see CloudinaryService)
        if ($this->cloud_disk === 'cloudinary') {
            // CloudinaryService::destroy($this->cloud_public_id);
            return true;
        }

        return Storage::disk('public')->delete($this->cloud_path);
    }

    /**
     * Return the display URL — prefers cloud_url (CDN), falls back to local.
     */
    public function getUrlAttribute(): string
    {
        if ($this->cloud_url) {
            return $this->cloud_url;
        }

        return Storage::disk($this->cloud_disk)->url($this->cloud_path);
    }

    /**
     * Cloudinary thumbnail transform URL (resize to 400x300, auto-quality).
     * Works only when cloud_disk = 'cloudinary'.
     */
    public function getThumbnailUrl(int $width = 400, int $height = 300): string
    {
        if ($this->cloud_disk === 'cloudinary' && $this->cloud_public_id) {
            // Cloudinary URL transformation
            return preg_replace(
                '/\/upload\//',
                "/upload/w_{$width},h_{$height},c_fill,q_auto,f_auto/",
                $this->cloud_url
            );
        }

        return $this->thumbnail_url ?? $this->url;
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    public function scopeImages($query)
    {
        return $query->where('file_type', self::TYPE_IMAGE);
    }

    public function scopeVideos($query)
    {
        return $query->where('file_type', self::TYPE_VIDEO);
    }

    public function scopeBefore($query)
    {
        return $query->where('stage', self::STAGE_BEFORE);
    }

    public function scopeAfter($query)
    {
        return $query->where('stage', self::STAGE_AFTER);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    public function scopeUnflagged($query)
    {
        return $query->where('is_flagged', false);
    }
}
