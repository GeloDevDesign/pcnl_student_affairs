<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Event extends Model
{

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'date'
    ];

    protected $appends = ['is_feedback'];

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


    protected function isFeedback(): Attribute
    {
        return Attribute::get(function () {
            return $this->feedbacks()->where('user_id', auth()->id())->exists();
        });
    }
}
