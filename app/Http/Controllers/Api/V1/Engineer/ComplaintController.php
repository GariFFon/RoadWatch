<?php

namespace App\Http\Controllers\Api\V1\Engineer;

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
        $query = Complaint::with(['category', 'user:id,name'])
            ->where('assigned_to', auth()->id());

        if ($request->boolean('completed')) {
            // Completed Tasks tab — only admin-verified complaints
            $query->where('status', Complaint::STATUS_VERIFIED);
        } else {
            // Active Assignments — exclude terminal states
            $query->whereNotIn('status', [Complaint::STATUS_VERIFIED, Complaint::STATUS_REJECTED]);

            if ($request->status) {
                $query->where('status', $request->status);
            }
        }

        $complaints = $query->latest()->paginate(15);

        return ComplaintResource::collection($complaints);
    }

    public function show(Complaint $complaint): ComplaintResource
    {
        abort_unless($complaint->assigned_to === auth()->id(), 403);
        $complaint->load(['category', 'media', 'statusHistories.changedBy', 'user:id,name', 'assignedEngineer:id,name', 'ratedBy:id,name', 'feedback']);

        return new ComplaintResource($complaint);
    }

    public function updateStatus(Request $request, Complaint $complaint): JsonResponse
    {
        abort_unless($complaint->assigned_to === auth()->id(), 403);

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', Complaint::STATUSES)],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        if ($complaint->status === $data['status']) {
            return response()->json([
                'message' => "The complaint is already in [{$data['status']}] status. Please refresh the page.",
            ], 422);
        }

        if (! $complaint->canTransitionTo($data['status'])) {
            return response()->json([
                'message' => "Cannot transition from [{$complaint->status}] to [{$data['status']}].",
            ], 422);
        }

        // Block engineer actions entirely when awaiting admin review
        if ($complaint->status === Complaint::STATUS_AWAITING_VERIFICATION) {
            return response()->json([
                'message' => 'This complaint is awaiting admin verification. No engineer actions are permitted until the admin responds.',
            ], 403);
        }

        // Engineers may only move to these statuses (never verified/rejected — admin-only)
        $engineerAllowed = [Complaint::STATUS_AWAITING_VERIFICATION, Complaint::STATUS_IN_PROGRESS, Complaint::STATUS_UNDER_REVIEW, Complaint::STATUS_REJECTED];
        if (! in_array($data['status'], $engineerAllowed)) {
            return response()->json(['message' => 'Engineers are not permitted to set this status.'], 403);
        }

        $complaint->updateStatus($data['status'], auth()->user(), $data['remarks'] ?? null);

        return response()->json(['message' => 'Status updated.', 'status' => $complaint->fresh()->status]);
    }
}
