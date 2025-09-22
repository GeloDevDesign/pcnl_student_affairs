<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    const NOT_FOUND = 0;
    const RESOLVE    = 1;
    const ARCHIVE = 3;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'image_url',
        'status',
        'uploaded_at'
    ];

    // Automatically cast uploaded_at to Carbon instance
    protected $casts = [
        'uploaded_at' => 'datetime',
    ];


    protected $appends = ['formatted_uploaded_at', 'status_text'];

    public function getFormattedUploadedAtAttribute()
    {
        return $this->uploaded_at
            ? $this->uploaded_at->timezone('Asia/Manila')->format('Y-m-d h:i A')
            : null;
    }


    public function getStatusTextAttribute()
    {
        return match ($this->status) {
            self::NOT_FOUND => 'not found',
            self::RESOLVE     => 'resolve',
            self::ARCHIVE   => 'archive',
            default         => 'unknown',
        };
    }







    protected static function booted()
    {
        static::creating(function ($item) {
            // Set uploaded_at to current datetime if not provided
            if (!$item->uploaded_at) {
                $item->uploaded_at = now();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
