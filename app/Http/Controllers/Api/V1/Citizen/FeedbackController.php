<?php

namespace App\Http\Controllers\Api\V1\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * POST /api/v1/citizen/complaints/{complaint}/feedback
     */
    public function store(Request $request, Complaint $complaint): JsonResponse
    {
        if ($complaint->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($complaint->status !== 'resolved') {
            return response()->json(['message' => 'Feedback is only allowed on resolved complaints.'], 422);
        }

        if (Feedback::where('complaint_id', $complaint->id)->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'You have already submitted feedback for this complaint.'], 409);
        }

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $feedback = Feedback::create([
            'complaint_id' => $complaint->id,
            'user_id'      => auth()->id(),
            'rating'       => $data['rating'],
            'comment'      => $data['comment'] ?? null,
        ]);

        return response()->json([
            'message'  => 'Feedback submitted.',
            'feedback' => ['rating' => $feedback->rating, 'comment' => $feedback->comment],
        ], 201);
    }
}
