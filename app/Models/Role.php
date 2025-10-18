<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected  $fillable = [
        'election_id',
        'name',
        'description'
    ];


    // App/Models/Role.php

    public function candidatesForElection()
    {
        return $this->hasMany(Candidate::class, 'role_id');
    }


    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'role_id');
    }
}
