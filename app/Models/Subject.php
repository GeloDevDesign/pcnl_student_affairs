<?php

namespace App\Models;
use App\Models\Instructor;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name'];


    public function instructors()
    {
       return $this->belongsToMany(Instructor::class, 'instructor_subject');
    }


}








