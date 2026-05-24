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
     * Citizen rates a verified complaint. Can update their rating.
     */
    public function store(Request $request, Complaint $complaint): JsonResponse
    {
        if ($complaint->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // Allow feedback on verified complaints (the new terminal state)
        if (! in_array($complaint->status, ['verified', 'resolved'])) {
            return response()->json([
                'message' => 'Feedback can only be submitted after the complaint is verified/resolved.',
            ], 422);
        }

        // 🔒 Lock: once both admin has rated AND citizen has already rated, seal all ratings
        $citizenAlreadyRated = Feedback::where('complaint_id', $complaint->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($citizenAlreadyRated && $complaint->engineer_rating) {
            return response()->json([
                'message' => 'Ratings are sealed — both parties have already rated this complaint.',
                'locked' => true,
            ], 423);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        // Upsert — allow citizen to update their own rating
        $feedback = Feedback::updateOrCreate(
            ['complaint_id' => $complaint->id, 'user_id' => auth()->id()],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null]
        );

        $labels = [1 => 'Very Poor', 2 => 'Poor', 3 => 'Average', 4 => 'Good', 5 => 'Excellent'];
        $emojis = [1 => '😡', 2 => '😞', 3 => '😐', 4 => '😊', 5 => '😍'];

        return response()->json([
            'message' => 'Feedback submitted — '.$labels[$data['rating']].'!',
            'feedback' => [
                'rating' => $feedback->rating,
                'label' => $labels[$feedback->rating],
                'emoji' => $emojis[$feedback->rating],
                'stars' => str_repeat('★', $feedback->rating).str_repeat('☆', 5 - $feedback->rating),
                'comment' => $feedback->comment,
            ],
        ], 200);
    }
}
