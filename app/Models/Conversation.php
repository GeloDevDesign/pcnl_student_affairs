<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected  $fillable = [
        'admin_id',
        'student_id'
    ];
}
