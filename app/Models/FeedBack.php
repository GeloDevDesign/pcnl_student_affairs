<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class FeedBack extends Model
{

    protected $fillable = [
        'user_id',
        'event_id',
        'ratings',
        'comments',
    ];

    protected $appends = [];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
