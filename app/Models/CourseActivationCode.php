<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseActivationCode extends Model
{
    protected $fillable = [
        'course_id',
        'code',
        'is_used',
        'used_by_student_id',
        'used_at',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'used_by_student_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
