<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Ensure this is imported

class Evaluation extends Model
{
    protected $fillable = [
        'evaluation_cycle_id',
        'student_id',
        'instructor_id',
        'comments_teacher',
        'comments_subject',
    ];

    // Relationship to the student (User)
    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers() {
        return $this->hasMany(EvaluationAnswer::class);
    }
}