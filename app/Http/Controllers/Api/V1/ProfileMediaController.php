<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileMediaController extends Controller
{
    /**
     * POST /api/v1/profile/photo
     * Upload or replace the authenticated user's profile photo to S3.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'file', 'image', 'mimes:jpeg,png,webp,gif', 'max:5120'],
        ]);

        $user = $request->user();

        // Delete old photo from S3 (if stored there)
        $this->deleteOldS3($user->profile_photo);

        $file = $request->file('photo');
        $ext  = $file->getClientOriginalExtension();
        $path = "users/{$user->id}/profile/photo_" . Str::random(10) . ".{$ext}";

        // Upload WITHOUT ACL — bucket has "Object Ownership enforced", ACLs are disabled
        Storage::disk('s3')->put($path, file_get_contents($file));
        $url = $this->buildPublicUrl($path);

        $user->update(['profile_photo' => $url]);

        return response()->json([
            'message'           => 'Profile photo updated.',
            'profile_photo_url' => $url,
        ]);
    }

    /**
     * POST /api/v1/profile/banner
     * Upload or replace the authenticated user's profile banner to S3.
     */
    public function uploadBanner(Request $request): JsonResponse
    {
        $request->validate([
            'banner' => ['required', 'file', 'image', 'mimes:jpeg,png,webp', 'max:8192'],
        ]);

        $user = $request->user();

        // Delete old banner from S3
        $this->deleteOldS3($user->profile_banner_url);

        $file = $request->file('banner');
        $ext  = $file->getClientOriginalExtension();
        $path = "users/{$user->id}/profile/banner_" . Str::random(10) . ".{$ext}";

        // Upload WITHOUT ACL
        Storage::disk('s3')->put($path, file_get_contents($file));
        $url = $this->buildPublicUrl($path);

        $user->update(['profile_banner_url' => $url]);

        return response()->json([
            'message'            => 'Profile banner updated.',
            'profile_banner_url' => $url,
        ]);
    }

    /**
     * DELETE /api/v1/profile/banner
     * Remove the banner and revert to the gradient default.
     */
    public function deleteBanner(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->deleteOldS3($user->profile_banner_url);
        $user->update(['profile_banner_url' => null]);

        return response()->json(['message' => 'Banner removed.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Build the public URL for an S3 object without using ACLs.
     * Works with buckets that have public access via a bucket policy.
     */
    private function buildPublicUrl(string $path): string
    {
        $bucket = config('filesystems.disks.s3.bucket');
        $region = config('filesystems.disks.s3.region');
        $url    = config('filesystems.disks.s3.url');

        // If a custom URL is set (e.g. CloudFront CDN), use it
        if ($url) {
            return rtrim($url, '/') . '/' . $path;
        }

        return "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
    }

    private function deleteOldS3(?string $url): void
    {
        if (!$url || !str_contains($url, 'amazonaws.com')) {
            return;
        }

        // Extract the S3 object key from the full URL (virtual-hosted style)
        // URL format: https://bucket.s3.region.amazonaws.com/KEY
        $parsed = parse_url($url);
        $key    = ltrim($parsed['path'] ?? '', '/');
        if ($key) {
            Storage::disk('s3')->delete($key);
        }
    }
}
