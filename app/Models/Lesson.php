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
}
