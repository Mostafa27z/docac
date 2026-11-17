<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoursePayment extends Model
{
    protected $fillable = [
        'course_enrollment_id',
        'amount',
        'payment_method',
        'transaction_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function enrollment()
    {
        return $this->belongsTo(CourseEnrollment::class, 'course_enrollment_id');
    }
}
