<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_cycle_id',
        'student_id',
        'instructor_id',
        'comments_teacher',
        'comments_subject',
    ];

    public function cycle()
    {
        return $this->belongsTo(EvaluationCycle::class, 'evaluation_cycle_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function answers()
    {
        return $this->hasMany(EvaluationAnswer::class);
    }
}