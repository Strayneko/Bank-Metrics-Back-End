<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserProfile extends Model
{
    use HasFactory;

    protected $guarded = ['id'];


    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (UserProfile $user) {
            $user->address = Str::of($user->address)->trim(); //trim address
        });

        static::updating(function (UserProfile $user) {
            $user->address = Str::of($user->address)->trim(); //trim address
        });
    }
}
