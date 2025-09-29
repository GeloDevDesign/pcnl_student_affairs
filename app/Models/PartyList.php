<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartyList extends Model
{
    protected  $fillable = [
        'name',
        'slogan'
    ];

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
