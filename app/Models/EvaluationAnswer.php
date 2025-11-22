<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'question_index',
        'rating',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }
}