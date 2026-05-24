<?php

namespace App\Http\Controllers\Api\V1\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * POST /api/v1/citizen/complaints/{complaint}/media
     * Add additional media to an existing complaint.
     */
    public function store(Request $request, Complaint $complaint): JsonResponse
    {
        if ($complaint->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'files' => ['required', 'array', 'max:5'],
            'files.*' => ['file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:51200'],
            'stage' => ['nullable', 'in:before,after'],
        ]);

        $stage = $request->input('stage', 'before');
        $added = [];
        $existing = $complaint->media()->count();
        $disk = config('filesystems.default');

        foreach ($request->file('files') as $index => $file) {
            $isVideo = in_array($file->getMimeType(), ['video/mp4', 'video/quicktime', 'video/webm']);
            $folder = $isVideo ? 'videos' : 'images';
            $ext = $file->getClientOriginalExtension() ?: ($isVideo ? 'mp4' : 'jpg');
            $path = "complaints/{$complaint->id}/{$folder}/".Str::random(20).".{$ext}";
            // Upload without ACL — bucket uses a public Bucket Policy instead.
            Storage::disk($disk)->put($path, file_get_contents($file));

            $media = ComplaintMedia::create([
                'complaint_id' => $complaint->id,
                'uploaded_by' => auth()->id(),
                'file_type' => $isVideo ? 'video' : 'image',
                'stage' => $stage,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'cloud_disk' => $disk,
                'cloud_path' => $path,
                'cloud_url' => $this->buildPublicUrl($disk, $path),
                'sort_order' => $existing + $index,
            ]);

            $added[] = [
                'id' => $media->id,
                'file_type' => $media->file_type,
                'cloud_url' => $media->cloud_url,
            ];
        }

        return response()->json(['message' => 'Media uploaded.', 'data' => $added], 201);
    }

    /**
     * DELETE /api/v1/citizen/media/{media}
     * Remove a media file (owner only).
     */
    public function destroy(ComplaintMedia $media): JsonResponse
    {
        if ($media->uploaded_by !== auth()->id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        Storage::disk($media->cloud_disk)->delete($media->cloud_path);
        $media->delete();

        return response()->json(['message' => 'Media deleted.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildPublicUrl(string $disk, string $path): string
    {
        if ($disk === 's3') {
            $customUrl = config('filesystems.disks.s3.url');
            if ($customUrl) {
                return rtrim($customUrl, '/').'/'.$path;
            }
            $bucket = config('filesystems.disks.s3.bucket');
            $region = config('filesystems.disks.s3.region');

            return "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
        }

        return Storage::disk($disk)->url($path);
    }
}
