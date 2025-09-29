<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $fillable = [
        'user_id',
        'election_id',
        'role_id',
        'party_id'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function party_list()
    {
        return $this->belongsTo(PartyList::class);
    }
}
