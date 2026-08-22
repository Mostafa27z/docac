<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'instructor_id',
        'category_id',
        'subcategory_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'type',
        'status',
        'published_at',
        'price'
    ];

    protected $casts = [
        'published_at' => 'datetime'
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function sections()
    {
        return $this->hasMany(CourseSection::class)->orderBy('sort_order');
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, CourseSection::class, 'course_id', 'section_id');
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function files()
    {
        return $this->hasMany(CourseFile::class);
    }

    public function liveSessions()
    {
        return $this->hasMany(LiveSession::class);
    }
}
