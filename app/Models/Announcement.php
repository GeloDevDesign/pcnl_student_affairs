<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'date',
        'details',
        'image_url',
        'publish_at'
    ];

    protected $appends = ['should_show'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function getShouldShowAttribute()
    {
        if (is_null($this->publish_at)) {
            return true;
        }
        
        return Carbon::today()->greaterThanOrEqualTo(Carbon::parse($this->publish_at));
    }



    protected function createdAt(): Attribute
    {
        return Attribute::get(function ($value) {
            // Format the original timestamp
            return Carbon::parse($value)->format('m/d/Y');
        });
    }

    protected function publishAt(): Attribute
    {
        return Attribute::get(function ($value) {
            // Format the original timestamp
            return Carbon::parse($value)->format('m/d/Y');
        });
    }
}
