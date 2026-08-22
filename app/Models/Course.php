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

        // 1. Check direct local file existence
        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        // 2. If path has 'storage/' prefix, check local file without 'storage/'
        if (str_starts_with($cleanPath, 'storage/')) {
            $withoutStorage = substr($cleanPath, 8);
            if (file_exists(public_path($withoutStorage))) {
                return asset($withoutStorage);
            }
        }

        // 3. For Bunny Storage CDN on live server, strip any leading 'storage/' to match exact Bunny Storage zone hierarchy
        $bunnyPath = str_starts_with($cleanPath, 'storage/') ? substr($cleanPath, 8) : $cleanPath;

        $cdnUrl = config('services.bunny.cdn_url');
        if (!empty($cdnUrl) && !str_contains($cdnUrl, 'vz-')) {
            $baseUrl = rtrim($cdnUrl, '/');
        } else {
            $storageZone = config('services.bunny.storage_zone', 'docac-storage');
            $baseUrl = "https://{$storageZone}.b-cdn.net";
        }

        $pathSegments = explode('/', $bunnyPath);
        $encodedSegments = array_map('rawurlencode', $pathSegments);
        
        return $baseUrl . '/' . implode('/', $encodedSegments);
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
