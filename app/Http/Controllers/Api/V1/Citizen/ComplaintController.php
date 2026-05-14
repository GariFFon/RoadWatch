<?php

namespace App\Http\Controllers\Api\V1\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    /**
     * GET /api/v1/citizen/complaints
     * List authenticated citizen's complaints with pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $complaints = auth()->user()
            ->complaints()
            ->with(['category', 'media' => fn($q) => $q->where('stage', 'before')->orderBy('sort_order')])
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return ComplaintResource::collection($complaints);
    }

    /**
     * POST /api/v1/citizen/complaints
     * Create a new complaint with optional media.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string', 'min:20', 'max:2000'],
            'category_id'  => ['required', 'exists:categories,id'],
            'severity'     => ['required', 'in:' . implode(',', Complaint::SEVERITY_LEVELS)],
            'latitude'     => ['required', 'numeric', 'between:-90,90'],
            'longitude'    => ['required', 'numeric', 'between:-180,180'],
            'location'     => ['required', 'string', 'max:500'],
            'is_anonymous' => ['boolean'],
            'images'       => ['nullable', 'array', 'max:5'],
            'images.*'     => ['file', 'mimes:jpg,jpeg,png,webp,heic', 'max:10240'],
            'videos'       => ['nullable', 'array', 'max:2'],
            'videos.*'     => ['file', 'mimes:mp4,mov,webm', 'max:51200'],
        ]);

        $complaint = DB::transaction(function () use ($validated, $request) {

            $disk = config('filesystems.default'); // 'public' locally, 's3' in production

            $complaint = Complaint::create([
                'user_id'      => auth()->id(),
                'category_id'  => $validated['category_id'],
                'title'        => $validated['title'],
                'description'  => $validated['description'],
                'latitude'     => $validated['latitude'],
                'longitude'    => $validated['longitude'],
                'location'     => $validated['location'],
                'severity'     => $validated['severity'],
                'is_anonymous' => $request->boolean('is_anonymous'),
                'status'       => Complaint::STATUS_PENDING,
            ]);

            foreach ($request->file('images', []) as $index => $file) {
                $path = $file->store("complaints/{$complaint->id}/images", $disk);
                ComplaintMedia::create([
                    'complaint_id'  => $complaint->id,
                    'uploaded_by'   => auth()->id(),
                    'file_type'     => 'image',
                    'stage'         => 'before',
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getMimeType(),
                    'size_bytes'    => $file->getSize(),
                    'cloud_disk'    => $disk,
                    'cloud_path'    => $path,
                    'cloud_url'     => Storage::disk($disk)->url($path),
                    'sort_order'    => $index,
                ]);
            }

            foreach ($request->file('videos', []) as $index => $file) {
                $path = $file->store("complaints/{$complaint->id}/videos", $disk);
                ComplaintMedia::create([
                    'complaint_id'  => $complaint->id,
                    'uploaded_by'   => auth()->id(),
                    'file_type'     => 'video',
                    'stage'         => 'before',
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getMimeType(),
                    'size_bytes'    => $file->getSize(),
                    'cloud_disk'    => $disk,
                    'cloud_path'    => $path,
                    'cloud_url'     => Storage::disk($disk)->url($path),
                    'sort_order'    => $index,
                ]);
            }

            return $complaint;
        });

        return response()->json([
            'message'   => 'Complaint submitted successfully.',
            'complaint' => new ComplaintResource($complaint->load('category')),
        ], 201);
    }

    /**
     * GET /api/v1/citizen/complaints/{complaint}
     * Show a single complaint (owner only).
     */
    public function show(Complaint $complaint): ComplaintResource|JsonResponse
    {
        if ($complaint->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $complaint->load(['category', 'media', 'statusHistories.changedBy', 'feedback']);
        $complaint->incrementViews();

        return new ComplaintResource($complaint);
    }

    /**
     * DELETE /api/v1/citizen/complaints/{complaint}
     * Citizens can only delete their own pending complaints.
     */
    public function destroy(Complaint $complaint): JsonResponse
    {
        if ($complaint->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($complaint->status !== Complaint::STATUS_PENDING) {
            return response()->json(['message' => 'Only pending complaints can be deleted.'], 422);
        }

        // Delete associated media files
        foreach ($complaint->media as $media) {
            Storage::disk($media->cloud_disk)->delete($media->cloud_path);
        }

        $complaint->delete();

        return response()->json(['message' => 'Complaint deleted.'], 200);
    }
}
