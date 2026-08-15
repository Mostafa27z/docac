<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'video_duration_seconds' => $this->video_duration_seconds,
            'sort_order' => $this->sort_order,
            'is_preview' => $this->is_preview,
            'video_url' => $this->when($this->is_preview || ($request->user() && $this->isUserEnrolled($request->user())), $this->video_url),
            'progress' => $this->when($request->user(), function() use ($request) {
                $progress = $this->progress()->where('student_id', $request->user()->id)->first();
                if ($progress) {
                    return [
                        'watched_seconds' => $progress->watched_seconds,
                        'percentage' => $progress->percentage,
                        'last_position_seconds' => $progress->last_position_seconds,
                        'completed_at' => $progress->completed_at,
                    ];
                }
                return null;
            }),
        ];
    }

    private function isUserEnrolled($user): bool
    {
        if (in_array($user->role, ['admin', 'instructor'])) {
            return true;
        }
        $courseId = $this->section->course_id;
        return $user->enrollments()->where('course_id', $courseId)->where('status', 'active')->exists();
    }
}
