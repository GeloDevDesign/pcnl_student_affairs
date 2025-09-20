<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
