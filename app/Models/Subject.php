<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'instructor_id'];

    public function u()
    {
        return $this->hasMany(Form::class);
    }
}
