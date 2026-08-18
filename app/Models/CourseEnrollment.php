<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    protected $fillable = [
        'course_id',
        'student_id',
        'status',
        'progress_percentage',
        'enrolled_at',
        'completed_at',
        'total_price',
        'paid_amount',
        'payment_status'
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percentage' => 'decimal:2',
        'total_price' => 'decimal:2',
        'paid_amount' => 'decimal:2'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function payments()
    {
        return $this->hasMany(CoursePayment::class, 'course_enrollment_id');
    }
}
