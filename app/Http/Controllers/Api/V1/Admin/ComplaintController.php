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
            'media',                          // includes before + after
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
            'status'  => ['required', 'in:pending,under_review,in_progress,resolved,rejected'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

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
}
