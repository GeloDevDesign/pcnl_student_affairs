<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartyList extends Model
{
    protected  $fillable = [
        'election_id',
        'user_id',
        'name',
        'slogan'
    ];

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'party_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
