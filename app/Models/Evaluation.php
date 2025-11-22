<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'evaluation_cycle_id',
        'student_id',
        'instructor_id',
        'comments_teacher',
        'comments_subject',
    ];

    public function answers() {
        return $this->hasMany(EvaluationAnswer::class);
    }
}