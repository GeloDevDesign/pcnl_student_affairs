<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Election extends Model
{

    const SCHEDULED = 0;
    const OPEN    = 1;
    const CLOSED = 2;

    protected  $fillable = [
        'user_id',
        'name',
        'start_date',
        'end_date',
        'status '
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
