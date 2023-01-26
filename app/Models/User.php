<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (User $user) {
            $user->password = Hash::make($user->password);
            // trim and title case name
            $user->name = Str::of($user->name)->title()->trim();
            $user->email = Str::of($user->email)->lower()->trim(); //trim and lowercase email

        });

        static::updating(function (user $user) {
            if ($user->isDirty(['password'])) {
                $user->password = hash::make($user->password);
            }
            $user->name = Str::of($user->name)->title()->trim();
            $user->email = Str::of($user->email)->lower()->trim(); //trim and lowercase email
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
