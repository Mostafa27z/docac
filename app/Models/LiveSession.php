<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'meeting_provider',
        'meeting_url',
        'meeting_id',
        'status'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function attendances()
    {
        return $this->hasMany(LiveAttendance::class);
    }
}
