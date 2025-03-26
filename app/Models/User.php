<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory,
        Notifiable;

    protected $primaryKey = 'userID'; // Specify the primary key
    public $incrementing = false; // Disable auto-increment if the primary key is not numeric
    protected $keyType = 'string'; // Specify the type if it’s a string

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'userID',
        'userName',
        'email',
        'userPhone',
        'userGender',
        'password',
    ];

    public function contractor() {
        return $this->hasOne(Contractor::class, 'userID', 'userID');
    }

    public function homeowner() {
        return $this->hasOne(Homeowner::class, 'userID', 'userID');
    }

    public function worker() {
        return $this->hasOne(Worker::class, 'userID', 'userID');
    }

    public function getRoleAttribute() {
        if (Contractor::where('userID', $this->userID)->exists()) {
            return 'contractor';
        } elseif (Homeowner::where('userID', $this->userID)->exists()) {
            return 'homeowner';
        } elseif (Worker::where('userID', $this->userID)->exists()) {
            return 'worker';
        }
        return 'guest'; // fallback if user is not in any of the role tables
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
