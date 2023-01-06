<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (User $user) {
            $user->password = Hash::make($user->password);
        });

        static::updating(function (user $user) {
            if ($user->isDirty(['password'])) {
                $user->password = hash::make($user->password);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // relation to roles table
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // relation to user_profiles table
    public function user_profile()
    {
        return $this->belongsTo(UserProfile::class, 'id', 'user_id');
    }

    // relation to countries table

    // relation to loans table
    public function loan()
    {
        return $this->hasMany(Loan::class, 'user_id');
    }
}
