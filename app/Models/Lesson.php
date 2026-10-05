<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'section_id',
        'title',
        'description',
        'type',
        'video_url',
        'video_duration_seconds',
        'sort_order',
        'is_preview'
    ];

    protected $casts = [
        'is_preview' => 'boolean'
    ];

    public function section()
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function studentProgress($studentId)
    {
        return $this->hasOne(LessonProgress::class)->where('student_id', $studentId);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function files()
    {
        return $this->hasMany(CourseFile::class);
    }

    /**
     * Determine the video provider (youtube, bunny_stream, bunny_storage, or null).
     */
    public function getVideoProviderAttribute(): ?string
    {
        if ($this->type !== 'video' || empty($this->video_url)) {
            return null;
        }

        $bunnyCdn = app(\App\Services\BunnyCdnService::class);
        if ($bunnyCdn->isYouTubeUrl($this->video_url) || preg_match('/^[a-zA-Z0-9_-]{11}$/', $this->video_url)) {
            return 'youtube';
        }

        if ($bunnyCdn->isBunnyStreamGuid($this->video_url)) {
            return 'bunny_stream';
        }

        return 'bunny_storage';
    }

    /**
     * Check if the lesson video is a YouTube video.
     */
    public function getIsYoutubeAttribute(): bool
    {
        return $this->video_provider === 'youtube';
    }

    /**
     * Get YouTube embed URL if applicable.
     */
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (!$this->is_youtube) {
            return null;
        }

        return app(\App\Services\BunnyCdnService::class)->getYouTubeEmbedUrl($this->video_url);
    }
}
