<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'department',
    ];

    protected $appends = ['department_name']; 

    protected static $departments = [
        1 => 'BSA',
        2 => 'BSBA',
        3 => 'BSCRIM',
        4 => 'BSIT',
        5 => 'BSCE',
        6 => 'BEE',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    // Accessor: Get readable department name
    public function getDepartmentNameAttribute()
    {
        return self::$departments[$this->department] ?? 'Unknown';
    }

    // Optional: Mutator (so you can set by name or ID)
    public function setDepartmentAttribute($value)
    {
        if (is_numeric($value)) {
            $this->attributes['department'] = $value;
        } else {
            // find ID by name
            $id = array_search(strtoupper($value), self::$departments);
            $this->attributes['department'] = $id ?: null;
        }
    }
}
