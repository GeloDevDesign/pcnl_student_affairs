<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedBack extends Model
{

    protected $fillable = [
        'user_id',
        'event_id',
        'ratings',
        'comment',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
