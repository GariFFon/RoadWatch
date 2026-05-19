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
use Illuminate\Support\Str;

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
            'images'       => ['required', 'array', 'min:1', 'max:5'],
            'images.*'     => ['file', 'mimes:jpg,jpeg,png,webp,heic', 'max:10240'],
            'videos'       => ['nullable', 'array', 'max:2'],
            'videos.*'     => ['file', 'mimes:mp4,mov,webm', 'max:51200'],
        ]);

        try {
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
                $ext  = $file->getClientOriginalExtension() ?: 'jpg';
                $path = "complaints/{$complaint->id}/images/" . Str::random(20) . ".{$ext}";
                // Upload without ACL — bucket has Object Ownership enforced (ACLs disabled).
                // Public read access is controlled by a Bucket Policy on the AWS console.
                Storage::disk($disk)->put($path, file_get_contents($file));
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
                    'cloud_url'     => $this->buildPublicUrl($disk, $path),
                    'sort_order'    => $index,
                ]);
            }

            foreach ($request->file('videos', []) as $index => $file) {
                $ext  = $file->getClientOriginalExtension() ?: 'mp4';
                $path = "complaints/{$complaint->id}/videos/" . Str::random(20) . ".{$ext}";
                Storage::disk($disk)->put($path, file_get_contents($file));
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
                    'cloud_url'     => $this->buildPublicUrl($disk, $path),
                    'sort_order'    => $index,
                ]);
            }

            return $complaint;
        });

        return response()->json([
            'message'   => 'Complaint submitted successfully.',
            'complaint' => new ComplaintResource($complaint->load('category')),
        ], 201);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Complaint store failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            // TEMP DEBUG — remove before stable release
            return response()->json([
                'message' => 'Server error: ' . $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ], 500);
        }
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

        $complaint->load(['category', 'media', 'statusHistories.changedBy', 'feedback', 'assignedEngineer']);
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

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Build a public URL for an uploaded file without relying on ACLs.
     *
     * - For S3: constructs the virtual-hosted URL directly so no ACL header
     *   is sent (bucket uses a public Bucket Policy instead).
     * - For local/public disk: delegates to Storage::url() which is fine.
     */
    private function buildPublicUrl(string $disk, string $path): string
    {
        if ($disk === 's3') {
            $customUrl = config('filesystems.disks.s3.url');
            if ($customUrl) {
                return rtrim($customUrl, '/') . '/' . $path;
            }
            $bucket = config('filesystems.disks.s3.bucket');
            $region = config('filesystems.disks.s3.region');
            return "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
        }

        return Storage::disk($disk)->url($path);
    }
}
