<?php
namespace App\Http\Controllers\Api\V1\Engineer;
use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\StatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ComplaintController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $complaints = Complaint::with(['category', 'user:id,name'])
            ->where('assigned_to', auth()->id())
            ->latest()->paginate(15);
        return ComplaintResource::collection($complaints);
    }

    public function show(Complaint $complaint): ComplaintResource
    {
        abort_unless($complaint->assigned_to === auth()->id(), 403);
        $complaint->load(['category', 'media', 'statusHistories.changedBy', 'user:id,name']);
        return new ComplaintResource($complaint);
    }

    public function updateStatus(Request $request, Complaint $complaint): JsonResponse
    {
        abort_unless($complaint->assigned_to === auth()->id(), 403);
        $data = $request->validate([
            'status'  => ['required', 'in:' . implode(',', Complaint::STATUSES)],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $allowed = Complaint::VALID_TRANSITIONS[$complaint->status] ?? [];
        if (!in_array($data['status'], $allowed)) {
            return response()->json(['message' => "Cannot transition from {$complaint->status} to {$data['status']}."], 422);
        }

        $old = $complaint->status;
        $complaint->update([
            'status'      => $data['status'],
            'resolved_at' => $data['status'] === 'resolved' ? now() : $complaint->resolved_at,
        ]);

        StatusHistory::create([
            'complaint_id' => $complaint->id,
            'old_status'   => $old,
            'new_status'   => $data['status'],
            'changed_by'   => auth()->id(),
            'remarks'      => $data['remarks'] ?? null,
        ]);

        return response()->json(['message' => 'Status updated.', 'status' => $data['status']]);
    }
}
