<?php
// app/Models/Backup.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $guarded = [];

    protected $casts = [
        'backed_up_at' => 'datetime',
        'size' => 'integer',
    ];

    public function getSizeAttribute()
    {
        $bytes = $this->attributes['size'];

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
