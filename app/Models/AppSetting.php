<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $table = 'app_settings';

    protected $fillable = [
        'enable',
    ];

    protected $casts = [
        'enable' => 'boolean',
    ];

    /**
     * Retrieve the singleton settings record, creating it if it does not exist.
     */
    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'enable' => false,
        ]);
    }
}

