<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Announcement extends Model
{

    protected $fillable = [
        'user_id',
        'title',
        'date',
        'details',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function createdAt(): Attribute
    {
        return Attribute::get(function ($value) {
            // Format the original timestamp
            return Carbon::parse($value)->format('m/d/Y');
        });
    }
}
