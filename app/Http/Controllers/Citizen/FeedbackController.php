<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request, Complaint $complaint): RedirectResponse
    {
        abort_unless($complaint->user_id === auth()->id(), 403);
        abort_unless($complaint->status === 'resolved', 403, 'Feedback can only be given on resolved complaints.');

        // Prevent duplicate feedback
        if ($complaint->feedback()->where('user_id', auth()->id())->exists()) {
            return back()->with('warning', 'You have already submitted feedback for this complaint.');
        }

        $request->validate([
            'rating'  => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        Feedback::create([
            'complaint_id' => $complaint->id,
            'user_id'      => auth()->id(),
            'rating'       => $request->rating,
            'comment'      => $request->comment,
        ]);

        return redirect()
            ->route('citizen.complaints.show', $complaint)
            ->with('success', '⭐ Thank you for your feedback!');
    }
}
