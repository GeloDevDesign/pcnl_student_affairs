<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Election extends Model
{
    const SCHEDULED = 0;
    const ONGOING   = 1;
    const CLOSED    = 2;
    const ARCHIVE   = 3;

    protected $fillable = [
        'user_id',  
        'name',
        'start_date',
        'start_time', // Add this
        'end_date',
        'end_time',   // Add this
        'status',
        'is_set'
    ];

    // Keep database format (Y-m-d) for input fields
    public function getStartDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getEndDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    // Human-readable format for display (COMBINED DATE AND TIME)
    public function getStartDateFormattedAttribute()
    {
        if (!$this->start_date) return null;
        
        $date = Carbon::parse($this->start_date)->format('M d, Y');
        $time = $this->start_time ? Carbon::parse($this->start_time)->format('h:i A') : '';
        
        return trim("$date $time");
    }

    public function getEndDateFormattedAttribute()
    {
        if (!$this->end_date) return null;

        $date = Carbon::parse($this->end_date)->format('M d, Y');
        $time = $this->end_time ? Carbon::parse($this->end_time)->format('h:i A') : '';
        
        return trim("$date $time");
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