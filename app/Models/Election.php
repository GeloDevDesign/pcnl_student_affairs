<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Election extends Model
{
    const SCHEDULED = 0;
    const ONGOING   = 1;
    const CLOSED    = 2;

    protected $fillable = [
        'user_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'is_set'
    ];

    protected $dates = ['start_date', 'end_date'];

    // Keep database format (Y-m-d) for input fields
    public function getStartDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getEndDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    // Human-readable format for display
    public function getStartDateFormattedAttribute()
    {
        return $this->start_date ? Carbon::parse($this->start_date)->format('M d, Y') : null;
        // Example: "Oct 20, 2025"
    }

    public function getEndDateFormattedAttribute()
    {
        return $this->end_date ? Carbon::parse($this->end_date)->format('M d, Y') : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
