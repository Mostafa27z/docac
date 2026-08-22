<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image_url',
    ];

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
