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

    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail) {
            return asset('logo.jfif');
        }

        if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
            return $this->thumbnail;
        }

        $cleanPath = ltrim($this->thumbnail, '/');
        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        $storageZone = config('services.bunny.storage_zone', 'docac-storage');
        $pathSegments = explode('/', $cleanPath);
        $encodedSegments = array_map('rawurlencode', $pathSegments);
        
        return "https://{$storageZone}.b-cdn.net/" . implode('/', $encodedSegments);
    }

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
