<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveAttendance extends Model
{
    protected $table = 'live_attendances';

    protected $fillable = [
        'live_session_id',
        'student_id',
        'joined_at',
        'left_at',
        'duration_seconds'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime'
    ];

    public function liveSession()
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
