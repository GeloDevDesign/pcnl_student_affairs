<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const TYPE_ADMIN = 'admin';
    const TYPE_STUDENT = 'student';

    public static $types = [
        self::TYPE_ADMIN => 'admin',
        self::TYPE_STUDENT => 'student'
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(FeedBack::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }


    public function party_lists()
    {
        return $this->hasMany(PartyList::class);
    }

    public function instructors()
    {
        return $this->hasMany(Instructor::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }


    public function handBooks()
    {
        return $this->hasMany(HandBook::class);
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function isAdmin()
    {
        return $this->role == self::TYPE_ADMIN;
    }


    public function isStudent()
    {
        return $this->role == self::TYPE_STUDENT;
    }
}
