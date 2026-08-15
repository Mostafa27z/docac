<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';

    protected $fillable = [
        'student_id',
        'lesson_id',
        'watched_seconds',
        'duration_seconds',
        'percentage',
        'last_position_seconds',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'percentage' => 'decimal:2'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
