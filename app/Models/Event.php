<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\FeedBack;

class Event extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'date',
    ];

    protected $appends = ['is_ended'];

    /**
     * Check if the event has ended.
     */
    public function getIsEndedAttribute(): bool
    {
        return $this->date
            ? now()->greaterThan($this->date)
            : false;
    }

    /**
     * The user who created the event.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All feedbacks for this event.
     */
    public function feedbacks()
    {
        return $this->hasMany(FeedBack::class);
    }

    /**
     * The feedback submitted by the currently authenticated user.
     */
    public function userFeedback()
    {
        return $this->hasOne(FeedBack::class)->where('user_id', auth()->id());
    }
}
