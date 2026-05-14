<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'complaint_number' => $this->complaint_number,
            'title'            => $this->title,
            'description'      => $this->description,
            'status'           => $this->status,
            'severity'         => $this->severity,
            'location'         => $this->location,
            'latitude'         => (float) $this->latitude,
            'longitude'        => (float) $this->longitude,
            'is_anonymous'     => (bool) $this->is_anonymous,
            'votes_count'      => $this->votes_count,
            'views_count'      => $this->views_count,
            'resolved_at'      => $this->resolved_at?->toIso8601String(),
            'created_at'       => $this->created_at->toIso8601String(),
            'updated_at'       => $this->updated_at->toIso8601String(),

            // Valid status transitions — role-scoped:
            // awaiting_verification → {verified, in_progress, rejected} are ADMIN-only.
            // Engineers see an empty array when status = awaiting_verification (read-only, waiting for admin).
            'next_statuses' => (function () use ($request) {
                $allTransitions = \App\Models\Complaint::VALID_TRANSITIONS[$this->status] ?? [];
                $user = $request->user();
                // If an engineer is viewing and status is awaiting_verification → no engineer actions
                if ($user && $user->hasRole('engineer') && $this->status === \App\Models\Complaint::STATUS_AWAITING_VERIFICATION) {
                    return [];
                }
                return $allTransitions;
            })(),

            // Related
            'category'         => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'icon' => $this->category->icon,
            ]),

            'media' => $this->whenLoaded('media', fn() =>
                $this->media->map(fn($m) => [
                    'id'            => $m->id,
                    'file_type'     => $m->file_type,
                    'stage'         => $m->stage,
                    'cloud_url'     => $m->cloud_url,
                    'original_name' => $m->original_name,
                    'mime_type'     => $m->mime_type,
                    'size_bytes'    => $m->size_bytes,
                    'sort_order'    => $m->sort_order,
                ])
            ),

            'status_histories' => $this->whenLoaded('statusHistories', fn() =>
                $this->statusHistories
                    ->sortBy('created_at')       // oldest first → chronological order
                    ->values()                   // re-index so JSON encodes as [] not {}
                    ->map(fn($h) => [
                        'id'           => $h->id,
                        'old_status'   => $h->old_status,
                        'new_status'   => $h->new_status,
                        'remarks'      => $h->remarks,
                        'changed_by'   => $h->changedBy ? [
                            'id'   => $h->changedBy->id,
                            'name' => $h->is_anonymous ? 'Anonymous' : $h->changedBy->name,
                            'role' => $h->changedBy->role,
                        ] : null,
                        'created_at'   => $h->created_at->toIso8601String(),
                    ])
            ),

            'feedback' => $this->whenLoaded('feedback', fn() => $this->feedback ? [
                'rating'  => $this->feedback->rating,
                'label'   => [1=>'Very Poor',2=>'Poor',3=>'Average',4=>'Good',5=>'Excellent'][$this->feedback->rating] ?? '—',
                'emoji'   => [1=>'😡',2=>'😞',3=>'😐',4=>'😊',5=>'😍'][$this->feedback->rating] ?? '⭐',
                'stars'   => str_repeat('★', $this->feedback->rating) . str_repeat('☆', 5 - $this->feedback->rating),
                'comment' => $this->feedback->comment,
            ] : null),

            // Show owner name unless anonymous
            'submitted_by' => $this->when(
                !$this->is_anonymous && $this->relationLoaded('user'),
                fn() => $this->user?->name
            ),

            // Assigned engineer (null if not yet assigned)
            'assigned_engineer' => $this->whenLoaded('assignedEngineer', fn() =>
                $this->assignedEngineer ? [
                    'id'   => $this->assignedEngineer->id,
                    'name' => $this->assignedEngineer->name,
                    'role' => $this->assignedEngineer->role,
                ] : null
            ),

            // Admin rating of engineer's work quality (1–5)
            'engineer_rating' => $this->engineer_rating ? [
                'score'    => $this->engineer_rating,
                'label'    => [1=>'Very Poor',2=>'Poor',3=>'Average',4=>'Good',5=>'Excellent'][$this->engineer_rating] ?? '—',
                'emoji'    => [1=>'😡',2=>'😞',3=>'😐',4=>'😊',5=>'😍'][$this->engineer_rating] ?? '⭐',
                'stars'    => str_repeat('★', $this->engineer_rating) . str_repeat('☆', 5 - $this->engineer_rating),
                'comment'  => $this->engineer_rating_comment,
                'rated_by' => $this->rated_by ? [
                    'id'   => $this->rated_by,
                    'name' => $this->ratedBy?->name ?? 'Admin',
                ] : null,
                'rated_at' => $this->rated_at?->toIso8601String(),
            ] : null,

            // 🔒 True when BOTH admin engineer_rating AND citizen feedback exist → all ratings sealed
            'ratings_locked' => $this->engineer_rating !== null
                && $this->whenLoaded('feedback', fn() => $this->feedback !== null, false),
        ];
    }
}
