<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected  $fillable = [
        'election_id',
        'role_id',
        'voter_id',
        'candidate_id',
    ];
}
