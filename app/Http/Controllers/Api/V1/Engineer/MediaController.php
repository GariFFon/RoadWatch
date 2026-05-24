<?php

namespace App\Http\Controllers\Api\V1\Engineer;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * POST /api/v1/engineer/complaints/{complaint}/media
     *
     * Engineer uploads "after" work evidence (photos/videos).
     * Only the assigned engineer for this complaint can upload.
     */
    public function store(Request $request, Complaint $complaint): JsonResponse
    {
        // Only the assigned engineer may upload evidence
        if ($complaint->assigned_to !== auth()->id()) {
            return response()->json(['message' => 'You are not assigned to this complaint.'], 403);
        }

        $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:51200'],
        ]);

        $added = [];
        $existing = $complaint->media()->count();
        $disk = config('filesystems.default');

        foreach ($request->file('files') as $index => $file) {
            $isVideo = in_array($file->getMimeType(), ['video/mp4', 'video/quicktime', 'video/webm']);
            $folder = $isVideo ? 'videos' : 'images';
            $path = $file->store("complaints/{$complaint->id}/{$folder}", $disk);

            $media = ComplaintMedia::create([
                'complaint_id' => $complaint->id,
                'uploaded_by' => auth()->id(),
                'file_type' => $isVideo ? 'video' : 'image',
                'stage' => 'after',
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'cloud_disk' => $disk,
                'cloud_path' => $path,
                'cloud_url' => Storage::disk($disk)->url($path),
                'sort_order' => $existing + $index,
            ]);

            $added[] = [
                'id' => $media->id,
                'file_type' => $media->file_type,
                'stage' => $media->stage,
                'cloud_url' => $media->cloud_url,
            ];
        }

        return response()->json([
            'message' => count($added).' file(s) uploaded as work evidence.',
            'data' => $added,
        ], 201);
    }

    /**
     * DELETE /api/v1/engineer/media/{media}
     *
     * Remove "after" evidence (only if uploaded by this engineer).
     */
    public function destroy(ComplaintMedia $media): JsonResponse
    {
        if ($media->uploaded_by !== auth()->id()) {
            return response()->json(['message' => 'You did not upload this file.'], 403);
        }
        if ($media->stage !== 'after') {
            return response()->json(['message' => 'Only after-work evidence can be deleted.'], 422);
        }

        Storage::disk($media->cloud_disk)->delete($media->cloud_path);
        $media->delete();

        return response()->json(['message' => 'File removed.']);
    }
}
