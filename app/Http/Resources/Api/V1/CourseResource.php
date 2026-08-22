<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'type' => $this->type,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'price' => (float)$this->price,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null,
            'subcategory' => $this->subcategory ? [
                'id' => $this->subcategory->id,
                'name' => $this->subcategory->name,
                'slug' => $this->subcategory->slug,
            ] : null,
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'sections' => CourseSectionResource::collection($this->whenLoaded('sections')),
            'enrollment' => $this->when($request->user(), function() use ($request) {
                $enrollment = $this->enrollments()->where('student_id', $request->user()->id)->first();
                if ($enrollment) {
                    return [
                        'status' => $enrollment->status,
                        'progress_percentage' => $enrollment->progress_percentage,
                        'enrolled_at' => $enrollment->enrolled_at,
                        'completed_at' => $enrollment->completed_at,
                    ];
                }
                return null;
            }),
        ];
    }
}
