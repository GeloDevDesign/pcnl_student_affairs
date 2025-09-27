<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Event extends Model
{

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(FeedBack::class);
    }

    public function userFeedback()
    {
        return $this->hasOne(FeedBack::class);
    }
}
