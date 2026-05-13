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
                $this->statusHistories->sortByDesc('created_at')->map(fn($h) => [
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
                'comment' => $this->feedback->comment,
            ] : null),

            // Show owner name unless anonymous
            'submitted_by' => $this->when(
                !$this->is_anonymous && $this->relationLoaded('user'),
                fn() => $this->user?->name
            ),
        ];
    }
}
