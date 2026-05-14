<?php
namespace App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ComplaintController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) ($request->per_page ?? 20), 100);

        $complaints = Complaint::with(['category', 'user:id,name', 'assignedEngineer:id,name'])
            ->when($request->status,   fn($q) => $q->where('status',   $request->status))
            ->when($request->severity, fn($q) => $q->where('severity', $request->severity))
            ->latest()
            ->paginate($perPage);

        return ComplaintResource::collection($complaints);
    }

    public function show(Complaint $complaint): ComplaintResource
    {
        $complaint->load([
            'category',
            'user:id,name,email',
            'assignedEngineer:id,name,email',
            'ratedBy:id,name',
            'feedback',
            'media',
            'statusHistories.changedBy:id,name,role',
        ]);
        return new ComplaintResource($complaint);
    }

    public function assign(Request $request, Complaint $complaint): JsonResponse
    {
        $data = $request->validate([
            'engineer_id' => ['required', 'exists:users,id'],
        ]);
        $complaint->update(['assigned_to' => $data['engineer_id']]);
        return response()->json(['message' => 'Complaint assigned successfully.']);
    }

    public function updateStatus(Request $request, Complaint $complaint): JsonResponse
    {
        $data = $request->validate([
            'status'  => ['required', 'in:' . implode(',', Complaint::STATUSES)],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        // Remarks are MANDATORY when admin rejects engineer work (sends back to in_progress)
        $isWorkRejection = $complaint->status === Complaint::STATUS_AWAITING_VERIFICATION
                        && $data['status']    === Complaint::STATUS_IN_PROGRESS;
        if ($isWorkRejection && empty($data['remarks'])) {
            return response()->json([
                'message' => 'A rejection reason is required when sending work back to the engineer.',
                'errors'  => ['remarks' => ['Rejection reason is required.']],
            ], 422);
        }

        if (!$complaint->canTransitionTo($data['status'])) {
            return response()->json([
                'message' => "Invalid transition: [{$complaint->status}] → [{$data['status']}].",
            ], 422);
        }

        $complaint->updateStatus($data['status'], auth()->user(), $data['remarks'] ?? null);

        return response()->json([
            'message'   => 'Status updated.',
            'complaint' => new ComplaintResource($complaint->fresh()),
        ]);
    }

    /**
     * POST /api/v1/admin/complaints/{complaint}/rate
     * Admin rates the quality of engineer's work (1–5 stars).
     * Can only be done on verified complaints. Allows updating the rating.
     */
    public function rateEngineer(Request $request, Complaint $complaint): JsonResponse
    {
        abort_unless($complaint->status === Complaint::STATUS_VERIFIED, 422, 'Only verified complaints can be rated.');

        // 🔒 Lock: if citizen has already submitted feedback, ratings are sealed
        if ($complaint->engineer_rating && $complaint->feedback()->exists()) {
            return response()->json([
                'message' => 'Ratings are sealed — both the admin and citizen have already rated this complaint.',
                'locked'  => true,
            ], 423);
        }

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $complaint->rateEngineer($data['rating'], $data['comment'] ?? null, auth()->user());

        $labels = [1=>'Very Poor',2=>'Poor',3=>'Average',4=>'Good',5=>'Excellent'];

        return response()->json([
            'message' => "Rated {$data['rating']}/5 — {$labels[$data['rating']]}.",
            'rating'  => [
                'score'   => $data['rating'],
                'label'   => $labels[$data['rating']],
                'comment' => $data['comment'] ?? null,
                'rated_by'=> auth()->user()->name,
                'rated_at'=> now()->toIso8601String(),
            ],
        ]);
    }
}
