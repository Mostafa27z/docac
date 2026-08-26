<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildSubcategory extends Model
{
    protected $fillable = [
        'subcategory_id',
        'name',
        'slug',
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
